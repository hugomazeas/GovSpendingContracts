<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TimelineService
{
    public function getTopOrganizations(): Collection
    {
        $limit = config('timeline.top_organizations_count');

        return Cache::remember('timeline_top_organizations', 3600, function () use ($limit) {
            return Contract::query()
                ->select('organization', DB::raw('SUM(total_contract_value) as total_value'))
                ->whereNotNull('organization')
                ->where('organization', '!=', '')
                ->groupBy('organization')
                ->orderByDesc('total_value')
                ->limit($limit)
                ->get();
        });
    }

    public function getTimelineData(array $organizations, int $minimum_contract_value): Collection
    {
        $years = config('timeline.years_to_display');
        $currentYear = now()->year;
        $startYear = $currentYear - $years;

        $cacheKey = 'timeline_data_' . md5(implode(',', $organizations) . $minimum_contract_value . $years);

        return Cache::remember($cacheKey, 1800, function () use ($organizations, $minimum_contract_value, $startYear) {
            $contractsPerOrg = config('timeline.contracts_per_organization', 10);
            $allContracts = collect();
            
            foreach ($organizations as $organization) {
                $orgContracts = Contract::query()
                    ->select([
                        'id',
                        'organization',
                        'contract_date',
                        'contract_period_start_date',
                        'contract_period_end_date',
                        'total_contract_value',
                        'description_of_work_english',
                        'vendor_name'
                    ])
                    ->where('organization', $organization)
                    ->where('contract_year', '>=', $startYear)
                    ->whereNotNull('contract_date')
                    ->where('total_contract_value', '>', $minimum_contract_value)
                    ->where('contract_period_start_date', '>=', '1999-01-01')
                    ->orderBy('total_contract_value', 'desc')
                    ->limit($contractsPerOrg)
                    ->get();
                
                $allContracts = $allContracts->merge($orgContracts);
            }
            
            return $allContracts->map(function ($contract) {
                return [
                    'id' => $contract->id,
                    'organization' => $contract->organization,
                    'start' => $contract->contract_period_start_date?->format('Y-m-d') ?? $contract->contract_date->format('Y-m-d'),
                    'end' => $contract->contract_period_end_date?->format('Y-m-d') ?? $contract->contract_date->format('Y-m-d'),
                    'value' => (float) $contract->total_contract_value,
                    'description' => substr($contract->description_of_work_english ?? '', 0, 100),
                    'vendor' => $contract->vendor_name,
                    'type' => $contract->contract_period_start_date && $contract->contract_period_end_date ? 'period' : 'event'
                ];
            });
        });
    }
}
