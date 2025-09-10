<?php

namespace App\Repositories;

use App\Models\Contract;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TimelineRepository implements TimelineRepositoryInterface
{
    public function getTopOrganizations(int $limit): Collection
    {
        return Contract::query()
            ->select('organization', DB::raw('SUM(total_contract_value) as total_value'))
            ->whereNotNull('organization')
            ->where('organization', '!=', '')
            ->groupBy('organization')
            ->orderByDesc('total_value')
            ->limit($limit)
            ->get();
    }
    
    public function getContractsByOrganizations(
        array $organizations, 
        int $minimumContractValue, 
        int $startYear, 
        int $contractsPerOrg
    ): Collection {
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
                ->where('total_contract_value', '>', $minimumContractValue)
                ->where('contract_period_start_date', '>=', config('timeline.minimum_contract_date'))
                ->orderBy('total_contract_value', 'desc')
                ->limit($contractsPerOrg)
                ->get();
            
            $allContracts = $allContracts->merge($orgContracts);
        }
        
        return $allContracts;
    }
}