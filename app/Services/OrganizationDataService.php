<?php

namespace App\Services;

use Illuminate\Support\Collection;

class OrganizationDataService
{
    public function calculateYearOverYearChanges(Collection $organizations, array $years): Collection
    {
        return $organizations->map(function ($org) use ($years) {
            $year1Spending = (float) $org->spending_year_1;
            $year2Spending = (float) $org->spending_year_2;
            $year3Spending = (float) $org->spending_year_3;
            $year4Spending = (float) $org->spending_year_4;

            return [
                'organization' => '<a href="'.route('organization.detail', ['organization' => urlencode($org->organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($org->organization).'</a>',
                'spending_'.$years[0] => $year1Spending > 0 ? '$'.number_format($year1Spending, 0) : '-',
                'spending_'.$years[1] => $year2Spending > 0 ? '$'.number_format($year2Spending, 0) : '-',
                'spending_'.$years[2] => $year3Spending > 0 ? '$'.number_format($year3Spending, 0) : '-',
                'change_year_1' => $this->calculatePercentageChange($year2Spending, $year1Spending),
                'change_year_2' => $this->calculatePercentageChange($year3Spending, $year2Spending),
                'change_year_3' => $this->calculatePercentageChange($year4Spending, $year3Spending),
            ];
        });
    }

    public function formatDataTableResponse(array $requestParams, Collection $organizations, int $totalRecords, int $filteredRecords): array
    {
        return [
            'draw' => intval($requestParams['draw']),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $organizations->values(),
        ];
    }

    public function applySorting(Collection $organizations, int $orderColumn, string $orderDir): Collection
    {
        $columns = ['organization', 'spending_year_1', 'spending_year_2', 'spending_year_3'];
        $orderByColumn = $columns[$orderColumn] ?? 'spending_year_1';

        return $organizations->sortBy($orderByColumn, SORT_REGULAR, $orderDir === 'desc');
    }

    public function applySearch(Collection $organizations, string $searchValue): Collection
    {
        if (empty($searchValue)) {
            return $organizations;
        }

        return $organizations->filter(function ($org) use ($searchValue) {
            return stripos($org->organization, $searchValue) !== false;
        });
    }

    private function calculatePercentageChange(float $oldValue, float $newValue): string
    {
        if ($oldValue <= 0) {
            return $newValue > 0 ? '--' : '';
        }

        $percentageChange = (($newValue - $oldValue) / $oldValue) * 100;

        if ($percentageChange > 0) {
            return '<span class="text-green-600">↗ +'.number_format($percentageChange, 1).'%</span>';
        } elseif ($percentageChange < 0) {
            return '<span class="text-red-600">↘ '.number_format($percentageChange, 1).'%</span>';
        } else {
            return '<span class="text-gray-600">→ 0%</span>';
        }
    }
}
