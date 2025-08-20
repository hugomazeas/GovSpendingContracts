<?php

namespace Database\Seeders;

use App\Models\ProcurementContract;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Exception;

class ProcurementContractSeeder extends Seeder
{
    use WithoutModelEvents;

    private int $errorCount = 0;
    private array $validationErrors = [];
    private int $memoryPeakMB = 0;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable query logging and optimize for large datasets
        DB::disableQueryLog();
        ini_set('memory_limit', '2G');
        
        $this->command->info('🧹 Clearing existing data...');
        ProcurementContract::truncate();

        $csvFile = base_path('data/data.csv');

        if (! file_exists($csvFile)) {
            $this->command->error('❌ CSV file not found at: '.$csvFile);
            return;
        }

        $fileSize = round(filesize($csvFile) / 1024 / 1024, 2);
        $this->command->info("📁 CSV file size: {$fileSize}MB");
        $this->command->info('🚀 Starting optimized CSV import...');

        $handle = fopen($csvFile, 'r');
        if (!$handle) {
            $this->command->error('❌ Failed to open CSV file');
            return;
        }

        $headers = fgetcsv($handle);
        $this->command->info('📊 Found '.count($headers).' columns');

        // Use specific column indexes instead of header matching to handle duplicates
        $columnMap = $this->getColumnIndexMap();

        $batchSize = 500; // Reduced for memory optimization
        $batch = [];
        $count = 0;
        $lineNumber = 1; // Track line number for error reporting
        $lastProgressUpdate = 0;
        $progressInterval = 10000; // Show progress every 10k records

        try {
            while (($data = fgetcsv($handle)) !== false) {
                $lineNumber++;
                
                try {
                    $record = $this->processRecord($data, $columnMap, $lineNumber);
                    
                    if ($record !== null) {
                        $batch[] = $record;
                        $count++;
                    }
                } catch (Exception $e) {
                    $this->handleRecordError($e, $lineNumber, $data);
                    continue;
                }

                // Process batch when full
                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch, $count);
                    $batch = [];
                    
                    // Memory management
                    $this->trackMemoryUsage();
                    if (memory_get_usage(true) > 1.5 * 1024 * 1024 * 1024) { // 1.5GB threshold
                        gc_collect_cycles();
                    }
                }

                // Show progress less frequently
                if ($count - $lastProgressUpdate >= $progressInterval) {
                    $memoryMB = round(memory_get_usage(true) / 1024 / 1024, 1);
                    $this->command->info("📈 Processed {$count} records (Memory: {$memoryMB}MB, Errors: {$this->errorCount})");
                    $lastProgressUpdate = $count;
                }
            }

            // Process final batch
            if (!empty($batch)) {
                $this->processBatch($batch, $count);
            }

        } catch (Exception $e) {
            $this->command->error("💥 Critical error at line {$lineNumber}: " . $e->getMessage());
            $this->command->error("Stack trace: " . $e->getTraceAsString());
            throw $e;
        } finally {
            fclose($handle);
        }

        $this->displayResults($count);
    }

    private function processRecord(array $data, array $columnMap, int $lineNumber): ?array
    {
        // Validate row has enough columns
        if (count($data) < max($columnMap)) {
            throw new Exception("Insufficient columns: expected at least " . (max($columnMap) + 1) . ", got " . count($data));
        }

        $record = [];
        $hasValidData = false;

        foreach ($columnMap as $dbField => $csvIndex) {
            $value = $data[$csvIndex] ?? null;

            if ($value === '-' || $value === '' || $value === 'NA') {
                $value = null;
            }

            $cleanedValue = $this->cleanValue($value, $dbField);
            $record[$dbField] = $cleanedValue;
            
            // Check if we have some meaningful data
            if ($cleanedValue !== null && in_array($dbField, ['vendor_name', 'total_contract_value', 'organization'])) {
                $hasValidData = true;
            }
        }

        // Skip completely empty records
        if (!$hasValidData) {
            $this->validationErrors[] = "Line {$lineNumber}: No meaningful data found";
            return null;
        }

        // Validate required fields
        if (empty($record['vendor_name']) && empty($record['organization'])) {
            $this->validationErrors[] = "Line {$lineNumber}: Missing both vendor_name and organization";
            return null;
        }

        $record['created_at'] = now();
        $record['updated_at'] = now();

        return $record;
    }

    private function processBatch(array $batch, int $totalCount): void
    {
        try {
            DB::table('procurement_contracts')->insert($batch);
        } catch (Exception $e) {
            $this->command->error("💥 Batch insert failed at record {$totalCount}: " . $e->getMessage());
            
            // Try inserting records one by one to identify the problematic record
            $this->command->info("🔍 Attempting individual record insertion to identify problem...");
            
            foreach ($batch as $index => $record) {
                try {
                    DB::table('procurement_contracts')->insert([$record]);
                } catch (Exception $individualError) {
                    $recordIndex = $totalCount - count($batch) + $index + 1;
                    $this->command->error("❌ Failed record #{$recordIndex}: " . $individualError->getMessage());
                    $this->command->error("🔍 Problematic data: " . json_encode($record, JSON_UNESCAPED_UNICODE));
                    $this->errorCount++;
                    
                    if ($this->errorCount > 10) {
                        $this->command->error("⚠️ Too many errors, stopping individual analysis");
                        throw $e; // Re-throw original batch error
                    }
                }
            }
        }
    }

    private function handleRecordError(Exception $e, int $lineNumber, array $data): void
    {
        $this->errorCount++;
        $dataPreview = implode(',', array_slice($data, 0, 5)) . '...';
        $this->validationErrors[] = "Line {$lineNumber}: {$e->getMessage()} (Data: {$dataPreview})";
        
        // Show first few errors immediately to help debug
        if ($this->errorCount <= 5) {
            $this->command->warn("🔍 Validation error #{$this->errorCount} at line {$lineNumber}: {$e->getMessage()}");
        }
        
        // Stop if too many consecutive errors - but be more lenient
        if ($this->errorCount > 500) {
            throw new Exception("Too many validation errors ({$this->errorCount}), stopping import");
        }
    }

    private function trackMemoryUsage(): void
    {
        $currentMemoryMB = round(memory_get_usage(true) / 1024 / 1024, 1);
        if ($currentMemoryMB > $this->memoryPeakMB) {
            $this->memoryPeakMB = $currentMemoryMB;
        }
    }

    private function displayResults(int $count): void
    {
        $this->command->info("\n✅ Import completed!");
        $this->command->info("📊 Total records processed: " . number_format($count));
        $this->command->info("❌ Validation errors: {$this->errorCount}");
        $this->command->info("💾 Peak memory usage: {$this->memoryPeakMB}MB");
        
        if (!empty($this->validationErrors) && count($this->validationErrors) <= 20) {
            $this->command->warn("\n⚠️ Validation errors:");
            foreach (array_slice($this->validationErrors, 0, 10) as $error) {
                $this->command->warn("  • {$error}");
            }
            if (count($this->validationErrors) > 10) {
                $remaining = count($this->validationErrors) - 10;
                $this->command->warn("  ... and {$remaining} more errors");
            }
        }

        // Test a few values
        $sample = ProcurementContract::whereNotNull('total_contract_value')->first();
        if ($sample) {
            $this->command->info('💰 Sample contract value: $'.number_format($sample->total_contract_value, 2));
        }
        
        $dbCount = ProcurementContract::count();
        $this->command->info("🗄️ Records in database: " . number_format($dbCount));
    }

    /**
     * Get column mapping using specific indexes (0-based)
     * Based on the CSV structure we observed
     */
    private function getColumnIndexMap(): array
    {
        return [
            'reference_number' => 0,
            'procurement_identification_number' => 1,
            'vendor_name' => 2,
            'vendor_postal_code' => 3,
            'buyer_name' => 4,
            'contract_date' => 5,
            'contract_year' => 6,
            'economic_object_code' => 7,
            'description_of_work_english' => 8,
            'contract_period_start_date' => 9,
            'contract_period_end_date' => 10,
            'total_contract_value' => 11, // Use the first occurrence (column 11, 0-based)
            'original_contract_value' => 12, // Use the first occurrence (column 12, 0-based)
            'contract_amendment_value' => 13,
            'comments_english' => 14,
            'additional_comments_english' => 15,
            'agreement_type' => 16,
            'commodity' => 17,
            'commodity_code' => 18,
            'country_of_vendor' => 19,
            'solicitation_procedure' => 20,
            'limited_tendering_reason' => 21,
            'trade_agreement_exceptions' => 22,
            'indigenous_business' => 23,
            'intellectual_property' => 24,
            'potential_for_commercial_exploitation' => 25,
            'former_public_servant' => 26,
            'standing_offer_or_supply_arrangement_number' => 27,
            'instrument_type' => 28,
            'ministers_office_contracts' => 29,
            'number_of_bids' => 30,
            'reporting_period' => 31,
            'organization' => 32,
            'amendment_no' => 33,
            'procurement_count' => 34,
            'aggregate_total' => 35,
            'working_procurement_id' => 36,
            // Skip duplicate total_contract_value at index 37, mapped above
            'trade_agreements' => 38,
            'socio_economic_indicator' => 39,
            'section_6_government_contracts_regulations_exceptions' => 40,
            'procurement_strategy_for_indigenous_business' => 41,
            // Skip duplicate original_contract_value at index 42, mapped above
            // Skip amendment_value at index 43, using index 13 instead
            'award_criteria' => 44,
            'standing_offer' => 45,
            'comprehensive_land_claims_agreement' => 46,
            'csv_id' => 47,
        ];
    }

    private function cleanValue(?string $value, string $field): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Trim whitespace
        $value = trim($value);
        
        if ($value === '' || $value === '-' || $value === 'NA' || $value === 'N/A') {
            return null;
        }

        return match ($field) {
            'contract_date', 'contract_period_start_date', 'contract_period_end_date' => $this->parseDate($value),
            'total_contract_value', 'original_contract_value', 'contract_amendment_value', 'aggregate_total' => $this->parseDecimal($value, $field),
            'contract_year', 'number_of_bids', 'amendment_no', 'procurement_count' => $this->parseInteger($value, $field),
            'csv_id' => $this->validateCsvId($value),
            'reference_number', 'procurement_identification_number' => $this->validateIdentifier($value, $field),
            'vendor_postal_code' => $this->validatePostalCode($value),
            default => $this->validateString($value, $field),
        };
    }

    private function parseDecimal(?string $value, string $field): ?float
    {
        if ($value === null) return null;
        
        // Remove common currency symbols and formatting
        $cleaned = preg_replace('/[,$\s\$]/', '', $value);
        
        // Handle various formats
        if (empty($cleaned) || $cleaned === '0' || $cleaned === '0.00') {
            return 0.0;
        }
        
        if (!is_numeric($cleaned)) {
            // Try to extract numbers from mixed content
            if (preg_match('/([0-9]+\.?[0-9]*)/', $cleaned, $matches)) {
                $cleaned = $matches[1];
            } else {
                // Skip invalid values instead of throwing error
                return null;
            }
        }
        
        $result = (float) $cleaned;
        
        // Allow negative values for amendment values (could be reductions)
        if ($result < 0 && !in_array($field, ['contract_amendment_value'])) {
            return null; // Skip instead of error
        }
        
        if ($result > 999999999999) { // 999 billion limit
            return null; // Skip instead of error
        }
        
        return $result;
    }

    private function parseInteger(?string $value, string $field): ?int
    {
        if ($value === null) return null;
        
        // Clean the value
        $cleaned = trim($value);
        
        if (!is_numeric($cleaned) || (float)$cleaned != (int)$cleaned) {
            // Try to extract integer from mixed content
            if (preg_match('/([0-9]+)/', $cleaned, $matches)) {
                $cleaned = $matches[1];
            } else {
                return null; // Skip invalid values
            }
        }
        
        $result = (int) $cleaned;
        
        // Validate reasonable ranges
        if ($field === 'contract_year') {
            if ($result < 1990 || $result > date('Y') + 10) {
                return null; // Skip invalid years
            }
        }
        
        if ($result < 0) {
            return null; // Skip negative values
        }
        
        return $result;
    }

    private function validateCsvId(?string $value): ?string
    {
        if ($value === null) return null;
        
        // CSV ID should be unique and not too long - truncate if needed
        if (strlen($value) > 100) {
            $value = substr($value, 0, 100);
        }
        
        return $value;
    }

    private function validateIdentifier(?string $value, string $field): ?string
    {
        if ($value === null) return null;
        
        // Truncate if too long instead of throwing error
        if (strlen($value) > 255) {
            $value = substr($value, 0, 255);
        }
        
        return $value;
    }

    private function validatePostalCode(?string $value): ?string
    {
        if ($value === null) return null;
        
        // Basic postal code validation
        $value = strtoupper(trim($value));
        
        // Truncate if too long instead of throwing error
        if (strlen($value) > 20) {
            $value = substr($value, 0, 20);
        }
        
        return $value;
    }

    private function validateString(?string $value, string $field): ?string
    {
        if ($value === null) return null;
        
        // Check for reasonable string lengths
        $maxLengths = [
            'vendor_name' => 500,
            'buyer_name' => 500,
            'organization' => 500,
            'economic_object_code' => 50,
            'commodity' => 255,
            'commodity_code' => 50,
            'country_of_vendor' => 100,
            'solicitation_procedure' => 255,
            'agreement_type' => 100,
            'instrument_type' => 100,
            'reporting_period' => 50,
            'working_procurement_id' => 255,
        ];
        
        $defaultMaxLength = 2000; // More generous for text fields
        $maxLength = $maxLengths[$field] ?? $defaultMaxLength;
        
        // Always truncate long values instead of throwing errors
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        
        // Clean invalid characters instead of throwing errors
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        return $value;
    }

    private function parseDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            $parsedDate = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $date);
            if ($parsedDate) {
                return $parsedDate->format('Y-m-d');
            }

            $parsedDate = \DateTime::createFromFormat('Y-m-d', $date);
            if ($parsedDate) {
                return $parsedDate->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Return null for unparseable dates
        }

        return null;
    }
}
