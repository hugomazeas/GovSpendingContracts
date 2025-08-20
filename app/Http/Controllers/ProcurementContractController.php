<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use Illuminate\Http\Request;

class ProcurementContractController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $selectedYear = $request->get('year', date('Y'));
        $availableYears = $this->getAvailableYears();

        // Ensure selected year is valid
        if (!in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $stats = $this->getStatistics($selectedYear);
        $topVendorsByCount = $this->getTopVendorsByCount($selectedYear);
        $topVendorsByValue = $this->getTopVendorsByValue($selectedYear);
        $topOrganizationsBySpending = $this->getTopOrganizationsBySpending($selectedYear);

        return view('procurement-contracts.dashboard', compact(
            'stats',
            'topVendorsByCount',
            'topVendorsByValue',
            'topOrganizationsBySpending',
            'selectedYear',
            'availableYears'
        ));
    }

    public function data(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = ProcurementContract::query();

        // Filter by year - this is critical for performance
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

        $totalRecords = ProcurementContract::where('contract_year', $selectedYear)->count();
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

        $data = $contracts->map(function ($contract) {
            return [
                'reference_number' => $contract->reference_number,
                'vendor_name' => $contract->vendor_name,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $contract->total_contract_value ? '$'.number_format($contract->total_contract_value, 2) : '-',
                'organization' => $contract->organization,
                'description_of_work_english' => $contract->description_of_work_english ?
                    (strlen($contract->description_of_work_english) > 100 ?
                        substr($contract->description_of_work_english, 0, 100).'...' :
                        $contract->description_of_work_english) : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    private function getStatistics(int $year): array
    {
        $query = ProcurementContract::where('contract_year', $year);

        $totalContracts = $query->count();
        $totalValue = $query->sum('total_contract_value');
        $uniqueVendors = $query->distinct('vendor_name')->count();
        $avgContractValue = $query->avg('total_contract_value');

        return [
            'total_contracts' => $totalContracts,
            'total_value' => $totalValue,
            'unique_vendors' => $uniqueVendors,
            'avg_contract_value' => $avgContractValue,
            'year' => $year,
        ];
    }

    private function getTopVendorsByCount(int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('vendor_name')
            ->groupBy('vendor_name')
            ->orderByDesc('contract_count')
            ->limit(10)
            ->get();
    }

    private function getTopVendorsByValue(int $year): \Illuminate\Support\Collection
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

    private function getTopOrganizationsBySpending(int $year): \Illuminate\Support\Collection
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

    public function organizationDetail(string $organization): \Illuminate\Contracts\View\View
    {
        $decodedOrganization = urldecode($organization);

        $contractsByYear = $this->getContractsByYearForOrganization($decodedOrganization);
        $organizationStats = $this->getOrganizationStats($decodedOrganization);
        $topVendorsForOrg = $this->getTopVendorsForOrganization($decodedOrganization);
        $topContracts = $this->getTopContractsForOrganization($decodedOrganization);
        return view('organization.dashboard', compact(
            'decodedOrganization',
            'organizationStats',
            'topVendorsForOrg',
            'contractsByYear',
            'topContracts'
        ));
    }

    private function getOrganizationStats(string $organization): array
    {
        $contracts = ProcurementContract::where('organization', $organization);

        return [
            'total_contracts' => $contracts->count(),
            'total_spending' => $contracts->sum('total_contract_value'),
            'avg_contract_value' => $contracts->avg('total_contract_value'),
            'unique_vendors' => $contracts->distinct('vendor_name')->count(),
            'date_range' => [
                'earliest' => $contracts->min('contract_date'),
                'latest' => $contracts->max('contract_date'),
            ],
        ];
    }

    private function getTopVendorsForOrganization(string $organization): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('vendor_name')
            ->whereNotNull('total_contract_value')
            ->groupBy('vendor_name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    private function getContractsByYearForOrganization(string $organization): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
            ->whereNotNull('contract_year')
            ->whereNotNull('total_contract_value')
            ->groupBy('contract_year')
            ->orderBy('contract_year', 'desc')
            ->get();
    }

    private function getTopContractsForOrganization(string $organization): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->whereNotNull('total_contract_value')
            ->orderByDesc('total_contract_value')
            ->limit(20)
            ->get(['vendor_name', 'total_contract_value', 'contract_date', 'description_of_work_english', 'reference_number']);
    }

    private function getAvailableYears(): \Illuminate\Support\Collection
    {
        return ProcurementContract::selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }
}
