<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProcurementContractController extends Controller
{
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $availableYears = Cache::remember('available_years', 3600, function () {
            return $this->getAvailableYears();
        });

        // Default year for initial stats loading (will be overridden by frontend)
        $defaultYear = $availableYears->first() ?? date('Y');
        $stats = Cache::remember("dashboard_stats_{$defaultYear}", 300, function () use ($defaultYear) {
            return $this->getStatistics($defaultYear);
        });

        return view('procurement-contracts.dashboard', compact(
            'stats',
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
        // Single optimized query to get all statistics at once
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

    public function organizationDetail(Request $request, string $organization): \Illuminate\Contracts\View\View
    {
        $decodedOrganization = urldecode($organization);

        $availableYears = Cache::remember("available_years_{$decodedOrganization}", 1800, function () use ($decodedOrganization) {
            return $this->getAvailableYearsForOrganization($decodedOrganization);
        });

        // Default year for initial stats (will be overridden by frontend)
        $defaultYear = $availableYears->first() ?? date('Y');

        $contractsByYear = Cache::remember("org_yearly_{$decodedOrganization}", 600, function () use ($decodedOrganization) {
            return $this->getContractsByYearForOrganization($decodedOrganization);
        });

        $organizationStats = Cache::remember("org_stats_{$decodedOrganization}_{$defaultYear}", 300, function () use ($decodedOrganization, $defaultYear) {
            return $this->getOrganizationStats($decodedOrganization, $defaultYear);
        });

        return view('organization.dashboard', compact(
            'decodedOrganization',
            'organizationStats',
            'contractsByYear',
            'availableYears'
        ));
    }

    private function getOrganizationStats(string $organization, int $year): array
    {
        // Single optimized query for organization statistics
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

    private function getTopVendorsForOrganization(string $organization, int $year): \Illuminate\Support\Collection
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

    private function getTopContractsForOrganization(string $organization, int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
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

    private function getAvailableYearsForOrganization(string $organization): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }
}
