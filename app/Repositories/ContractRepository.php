<?php

namespace App\Repositories;

use App\Models\Contract;
use App\Repositories\Contracts\ContractRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

class ContractRepository implements ContractRepositoryInterface
{
    public function getDataTableData(Request $request): array
    {
        $query = Contract::query();

        $selectedYear = $request->get('year', date('Y'));
        $query->where('contract_year', $selectedYear);

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('vendor_name', 'like', "%{$searchValue}%")
                    ->orWhere('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%")
                    ->orWhere('organization', 'like', "%{$searchValue}%")
                    ->orWhere('commodity', 'like', "%{$searchValue}%");
            });
        }

        $totalRecords = Contract::where('contract_year', $selectedYear)->count();
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $sortDirection = $request->order[0]['dir'];

            $columns = [
                0 => 'reference_number',
                1 => 'vendor_name',
                2 => 'contract_date',
                3 => 'total_contract_value',
                4 => 'organization',
                5 => 'description_of_work_english',
            ];

            if (isset($columns[$columnIndex])) {
                $query->orderBy($columns[$columnIndex], $sortDirection);
            }
        } else {
            $query->orderBy('contract_date', 'desc');
        }

        $contracts = $query->offset($request->start ?? 0)
            ->limit($request->length ?? 10)
            ->get();

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'contracts' => $contracts,
        ];
    }

    public function getVendorDataTableData(string $vendorName, Request $request): array
    {
        $query = Contract::query()->where('vendor_name', $vendorName);

        $selectedYear = $request->get('year');
        if ($selectedYear) {
            $query->where('contract_year', $selectedYear);
        }

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%")
                    ->orWhere('organization', 'like', "%{$searchValue}%")
                    ->orWhere('commodity', 'like', "%{$searchValue}%");
            });
        }

        $totalQuery = Contract::where('vendor_name', $vendorName);
        if ($selectedYear) {
            $totalQuery->where('contract_year', $selectedYear);
        }

        $totalRecords = $totalQuery->count();
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $sortDirection = $request->order[0]['dir'];

            $columns = [
                0 => 'reference_number',
                1 => 'contract_date',
                2 => 'total_contract_value',
                3 => 'organization',
                4 => 'description_of_work_english',
            ];

            if (isset($columns[$columnIndex])) {
                $query->orderBy($columns[$columnIndex], $sortDirection);
            }
        } else {
            $query->orderBy('contract_date', 'desc');
        }

        $contracts = $query->offset($request->start ?? 0)
            ->limit($request->length ?? 10)
            ->get();

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'contracts' => $contracts,
        ];
    }

    public function getOrganizationDataTableData(string $organization, Request $request): array
    {
        $query = Contract::query()->where('organization', $organization);

        $selectedYear = $request->get('year');
        if ($selectedYear) {
            $query->where('contract_year', $selectedYear);
        }

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('vendor_name', 'like', "%{$searchValue}%")
                    ->orWhere('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%");
            });
        }

        $totalQuery = Contract::where('organization', $organization);
        if ($selectedYear) {
            $totalQuery->where('contract_year', $selectedYear);
        }

        $totalRecords = $totalQuery->count();
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $sortDirection = $request->order[0]['dir'];

            $columns = [
                0 => 'vendor_name',
                1 => 'reference_number',
                2 => 'contract_date',
                3 => 'total_contract_value',
                4 => 'description_of_work_english',
            ];

            if (isset($columns[$columnIndex])) {
                $query->orderBy($columns[$columnIndex], $sortDirection);
            }
        } else {
            $query->orderBy('contract_date', 'desc');
        }

        $contracts = $query->offset($request->start ?? 0)
            ->limit($request->length ?? 10)
            ->get();

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'contracts' => $contracts,
        ];
    }

    public function getVendorOrganizationDataTableData(string $vendorName, string $organization, Request $request): array
    {
        $query = Contract::query()
            ->where('vendor_name', $vendorName)
            ->where('organization', $organization);

        $selectedYear = $request->get('year');
        if ($selectedYear) {
            $query->where('contract_year', $selectedYear);
        }

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%")
                    ->orWhere('commodity', 'like', "%{$searchValue}%");
            });
        }

        $totalQuery = Contract::query()
            ->where('vendor_name', $vendorName)
            ->where('organization', $organization);
        if ($selectedYear) {
            $totalQuery->where('contract_year', $selectedYear);
        }

        $totalRecords = $totalQuery->count();
        $filteredRecords = $query->count();

        if ($request->has('order')) {
            $columnIndex = $request->order[0]['column'];
            $sortDirection = $request->order[0]['dir'];

            $columns = [
                0 => 'reference_number',
                1 => 'contract_date',
                2 => 'total_contract_value',
                3 => 'description_of_work_english',
            ];

            if (isset($columns[$columnIndex])) {
                $query->orderBy($columns[$columnIndex], $sortDirection);
            }
        } else {
            $query->orderBy('contract_date', 'desc');
        }

        $contracts = $query->offset($request->start ?? 0)
            ->limit($request->length ?? 10)
            ->get();

        return [
            'totalRecords' => $totalRecords,
            'filteredRecords' => $filteredRecords,
            'contracts' => $contracts,
        ];
    }

    public function getSpendingByYear(?string $organization = null): Collection
    {
        $query = Contract::selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value');

        if ($organization) {
            $query->where('organization', $organization);
        }

        return $query->groupBy('contract_year')
            ->orderBy('contract_year', 'asc')
            ->get();
    }

    public function getSpendingByYearForVendor(string $vendorName): Collection
    {
        return Contract::where('vendor_name', $vendorName)
            ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value')
            ->groupBy('contract_year')
            ->orderBy('contract_year', 'asc')
            ->get();
    }

    public function getOrganizationSpendingAnalysis(array $years): Collection
    {
        $currentYear = date('Y');
        $lastThreeYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3];
        $fourthYear = $currentYear - 4;

        return Contract::selectRaw('
                organization,
                SUM(CASE WHEN contract_year = ? THEN total_contract_value ELSE 0 END) as spending_year_1,
                SUM(CASE WHEN contract_year = ? THEN total_contract_value ELSE 0 END) as spending_year_2,
                SUM(CASE WHEN contract_year = ? THEN total_contract_value ELSE 0 END) as spending_year_3,
                SUM(CASE WHEN contract_year = ? THEN total_contract_value ELSE 0 END) as spending_year_4,
                SUM(total_contract_value) as total_spending
            ', [$lastThreeYears[0], $lastThreeYears[1], $lastThreeYears[2], $fourthYear])
            ->whereNotNull('organization')
            ->whereNotNull('total_contract_value')
            ->whereIn('contract_year', $years)
            ->groupBy('organization')
            ->get();
    }

    public function getOrganizationsPieChartData(string $year): array
    {
        $topOrganizations = Contract::where('contract_year', $year)
            ->whereNotNull('organization')
            ->whereNotNull('total_contract_value')
            ->selectRaw('organization, SUM(total_contract_value) as total_spending')
            ->groupBy('organization')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();

        $totalYearSpending = Contract::where('contract_year', $year)
            ->whereNotNull('total_contract_value')
            ->sum('total_contract_value');

        return [
            'topOrganizations' => $topOrganizations,
            'totalYearSpending' => $totalYearSpending,
        ];
    }

    public function getVendorHistoricalContracts(string $vendorName): Collection
    {
        return Contract::where('vendor_name', $vendorName)
            ->whereNotNull('total_contract_value')
            ->select('total_contract_value', 'contract_year')
            ->get();
    }

    public function getOrganizationHistoricalContracts(string $organization): Collection
    {
        return Contract::where('organization', $organization)
            ->whereNotNull('total_contract_value')
            ->select('total_contract_value', 'contract_year')
            ->get();
    }

    public function getVendorOrganizationHistoricalContracts(string $vendorName, string $organization): Collection
    {
        return Contract::where('vendor_name', $vendorName)
            ->where('organization', $organization)
            ->whereNotNull('total_contract_value')
            ->select('total_contract_value', 'contract_year')
            ->get();
    }

    public function getAllHistoricalContracts(): Collection
    {
        return Contract::whereNotNull('total_contract_value')
            ->select('total_contract_value', 'contract_year')
            ->get();
    }
}
