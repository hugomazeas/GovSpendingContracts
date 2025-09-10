<?php

namespace App\Services;

use App\Repositories\TimelineRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class TimelineService
{
    public function __construct(
        private TimelineRepositoryInterface $timelineRepository
    ) {}

    public function getTopOrganizations(): Collection
    {
        $limit = config('timeline.top_organizations_count');

        return Cache::remember('timeline_top_organizations', 3600, fn() => 
            $this->timelineRepository->getTopOrganizations($limit)
        );
    }

    public function getTimelineData(array $organizations, int $minimum_contract_value): Collection
    {
        $years = config('timeline.years_to_display');
        $currentYear = now()->year;
        $startYear = $currentYear - $years;

        $cacheKey = 'timeline_data_' . md5(implode(',', $organizations) . $minimum_contract_value . $years);

        return Cache::remember($cacheKey, 1800, function () use ($organizations, $minimum_contract_value, $startYear) {
            $contractsPerOrg = config('timeline.contracts_per_organization');
            
            $allContracts = $this->timelineRepository->getContractsByOrganizations(
                $organizations, 
                $minimum_contract_value, 
                $startYear, 
                $contractsPerOrg
            );
            
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

    public function getFullTimelineData(): array
    {
        $organizations = $this->getTopOrganizations();
        $organizationNames = $organizations->pluck('organization')->toArray();
        $minimumContractValue = config('timeline.minimum_contract_value');
        $timelineData = $this->getTimelineData($organizationNames, $minimumContractValue);

        return [
            'organizations' => $organizations,
            'timeline' => $timelineData
        ];
    }
}
