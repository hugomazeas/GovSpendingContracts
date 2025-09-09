<?php

namespace App\Services;

use App\Models\Contract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimelineService
{
    public function getTopOrganizations(): Collection
    {
        $limit = config('timeline.top_organizations_count');
        return Contract::query()
            ->select('organization', DB::raw('SUM(total_contract_value) as total_value'))
            ->whereNotNull('organization')
            ->where('organization', '!=', '')
            ->groupBy('organization')
            ->orderByDesc('total_value')
            ->limit($limit)
            ->get();
    }

    public function getTimelineData(array $organizations, int $minimum_contract_value): Collection
    {
        $years = config('timeline.years_to_display');
        $currentYear = now()->year;
        $startYear = $currentYear - $years;

        $contracts = collect();

        foreach ($organizations as $organization) {
            $orgContracts = Contract::query()
                ->select([
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
                ->where('contract_period_start_date', '>=', '1900-01-01')
                ->orderBy('total_contract_value', 'desc')
                ->limit(20)
                ->get();

            $contracts = $contracts->merge($orgContracts);
        }

        return $contracts
            ->sortBy('contract_date')
            ->values()
            ->map(function ($contract) {
                return [
                    'organization' => $contract->organization,
                    'start' => $contract->contract_period_start_date?->format('Y-m-d') ?? $contract->contract_date->format('Y-m-d'),
                    'end' => $contract->contract_period_end_date?->format('Y-m-d') ?? $contract->contract_date->format('Y-m-d'),
                    'value' => (float) $contract->total_contract_value,
                    'description' => substr($contract->description_of_work_english ?? '', 0, 100),
                    'vendor' => $contract->vendor_name,
                    'type' => $contract->contract_period_start_date && $contract->contract_period_end_date ? 'period' : 'event'
                ];
            });
    }
}
