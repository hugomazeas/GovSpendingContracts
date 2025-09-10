<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface TimelineRepositoryInterface
{
    public function getTopOrganizations(int $limit): Collection;
    
    public function getContractsByOrganizations(
        array $organizations, 
        int $minimumContractValue, 
        int $startYear, 
        int $contractsPerOrg
    ): Collection;
}