<?php

namespace App\Services;

use App\Models\ProcurementContract;
use Illuminate\Support\Collection;

class ProcurementAnalyticsService
{
    public function getGeneralStatistics(int $year): array
    {
        $stats = ProcurementContract::where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_value,
                AVG(total_contract_value) as avg_contract_value,
                COUNT(DISTINCT vendor_name) as unique_vendors
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_value' => $stats->total_value ?? 0,
            'unique_vendors' => $stats->unique_vendors ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'year' => $year,
        ];
    }

    // Organization Analytics
    public function getOrganizationStats(string $organization, int $year): array
    {
        $stats = ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_spending,
                AVG(total_contract_value) as avg_contract_value,
                COUNT(DISTINCT vendor_name) as unique_vendors,
                MIN(contract_date) as earliest_date,
                MAX(contract_date) as latest_date
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_spending' => $stats->total_spending ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'unique_vendors' => $stats->unique_vendors ?? 0,
            'date_range' => [
                'earliest' => $stats->earliest_date,
                'latest' => $stats->latest_date,
            ],
        ];
    }

    public function getTopVendorsForOrganization(string $organization, int $year): Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('vendor_name')
            ->whereNotNull('total_contract_value')
            ->groupBy('vendor_name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    public function getTopContractsForOrganization(string $organization, int $year): Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->whereNotNull('total_contract_value')
            ->orderByDesc('total_contract_value')
            ->limit(20)
            ->get(['id', 'vendor_name', 'total_contract_value', 'contract_date', 'description_of_work_english', 'reference_number']);
    }

    public function getContractsByYearForOrganization(string $organization): Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value')
            ->groupBy('contract_year')
            ->orderBy('contract_year', 'desc')
            ->get();
    }

    // Vendor Analytics
    public function getVendorStats(string $vendor, int $year): array
    {
        $stats = ProcurementContract::where('vendor_name', $vendor)
            ->where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_value,
                AVG(total_contract_value) as avg_contract_value,
                COUNT(DISTINCT organization) as minister_clients,
                MIN(contract_date) as earliest_date,
                MAX(contract_date) as latest_date
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_value' => $stats->total_value ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'minister_clients' => $stats->minister_clients ?? 0,
            'date_range' => [
                'earliest' => $stats->earliest_date,
                'latest' => $stats->latest_date,
            ],
        ];
    }

    public function getTopMinistersForVendor(string $vendor, int $year): Collection
    {
        return ProcurementContract::where('vendor_name', $vendor)
            ->where('contract_year', $year)
            ->selectRaw('organization, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('organization')
            ->whereNotNull('total_contract_value')
            ->groupBy('organization')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    public function getContractsByYearForVendor(string $vendor): Collection
    {
        return ProcurementContract::where('vendor_name', $vendor)
            ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value')
            ->groupBy('contract_year')
            ->orderBy('contract_year', 'desc')
            ->get();
    }

    // General Vendor Leaderboards
    public function getTopVendorsByCount(int $year): Collection
    {
        return ProcurementContract::selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('vendor_name')
            ->groupBy('vendor_name')
            ->orderByDesc('contract_count')
            ->limit(10)
            ->get();
    }

    public function getTopVendorsByValue(int $year): Collection
    {
        return ProcurementContract::selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('vendor_name')
            ->whereNotNull('total_contract_value')
            ->groupBy('vendor_name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    public function getTopOrganizationsBySpending(int $year): Collection
    {
        return ProcurementContract::selectRaw('organization, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
            ->where('contract_year', $year)
            ->whereNotNull('organization')
            ->whereNotNull('total_contract_value')
            ->groupBy('organization')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();
    }

    public function getTopVendorCountriesByValue(int $year): Collection
    {
        return ProcurementContract::selectRaw('country_of_vendor, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('country_of_vendor')
            ->where('country_of_vendor', '!=', '')
            ->whereNotNull('total_contract_value')
            ->groupBy('country_of_vendor')
            ->orderByDesc('total_value')
            ->limit(5)
            ->get();
    }

    // Available Years Methods
    public function getAvailableYears(): Collection
    {
        return ProcurementContract::selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }

    public function getAvailableYearsForOrganization(string $organization): Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }

    public function getAvailableYearsForVendor(string $vendor): Collection
    {
        return ProcurementContract::where('vendor_name', $vendor)
            ->selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }

    public function getAvailableYearsForVendorOrganization(string $vendor, string $organization): Collection
    {
        return ProcurementContract::where('vendor_name', $vendor)
            ->where('organization', $organization)
            ->selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }

    public function getVendorOrganizationStats(string $vendor, string $organization, int $year): array
    {
        $stats = ProcurementContract::where('vendor_name', $vendor)
            ->where('organization', $organization)
            ->where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_value,
                AVG(total_contract_value) as avg_contract_value,
                MIN(contract_date) as earliest_date,
                MAX(contract_date) as latest_date
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_value' => $stats->total_value ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'date_range' => [
                'earliest' => $stats->earliest_date,
                'latest' => $stats->latest_date,
            ],
        ];
    }

    public function getVendorOrganizationSpendingOverTime(string $vendor, string $organization): Collection
    {
        return ProcurementContract::where('vendor_name', $vendor)
            ->where('organization', $organization)
            ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value')
            ->groupBy('contract_year')
            ->orderBy('contract_year', 'asc')
            ->get();
    }
}
