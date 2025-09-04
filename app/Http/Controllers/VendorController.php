<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VendorController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService
    ) {}

    public function detail(Request $request, string $vendor): View
    {
        $decodedVendor = urldecode($vendor);

        $availableYears = Cache::remember("available_years_vendor_{$decodedVendor}", 1800, function () use ($decodedVendor) {
            return $this->analyticsService->getAvailableYearsForVendor($decodedVendor);
        });

        // Default year for initial stats (will be overridden by frontend)
        $defaultYear = $availableYears->first() ?? date('Y');

        $contractsByYear = Cache::remember("vendor_yearly_{$decodedVendor}", 600, function () use ($decodedVendor) {
            return $this->analyticsService->getContractsByYearForVendor($decodedVendor);
        });

        $vendorStats = Cache::remember("vendor_stats_{$decodedVendor}_{$defaultYear}", 300, function () use ($decodedVendor, $defaultYear) {
            return $this->analyticsService->getVendorStats($decodedVendor, $defaultYear);
        });

        return view('vendor.dashboard', compact(
            'decodedVendor',
            'vendorStats',
            'contractsByYear',
            'availableYears'
        ));
    }

    public function contractsData(Request $request, string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);

        $query = ProcurementContract::query()
            ->where('vendor_name', $decodedVendor);

        // Filter by year - this is critical for performance
        $selectedYear = $request->get('year');
        if (! $selectedYear) {
            // Get the most recent year with data for this vendor
            $selectedYear = $this->analyticsService->getAvailableYearsForVendor($decodedVendor)->first() ?? date('Y');
        }
        $query->where('contract_year', $selectedYear);

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%")
                    ->orWhere('organization', 'like', "%{$searchValue}%")
                    ->orWhere('commodity', 'like', "%{$searchValue}%");
            });
        }

        $totalRecords = ProcurementContract::where('vendor_name', $decodedVendor)
            ->where('contract_year', $selectedYear)
            ->count();
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

        $data = $contracts->map(function ($contract) use ($decodedVendor) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $contract->total_contract_value ? '$'.number_format($contract->total_contract_value, 2) : '-',
                'organization' => $contract->organization ?
                    '<div class="flex flex-col gap-1"><a href="'.route('organization.detail', ['organization' => urlencode($contract->organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($contract->organization).'</a><a href="'.route('vendor.organization.contracts', ['vendor' => urlencode($decodedVendor), 'organization' => urlencode($contract->organization)]).'" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors"><i class="fas fa-handshake mr-1"></i>View partnership</a></div>' :
                    '-',
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

    public function vendorOrganizationContracts(Request $request, string $vendor, string $organization): View
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);

        $availableYears = Cache::remember("available_years_vendor_org_{$decodedVendor}_{$decodedOrganization}", 1800, function () use ($decodedVendor, $decodedOrganization) {
            return $this->analyticsService->getAvailableYearsForVendorOrganization($decodedVendor, $decodedOrganization);
        });

        $defaultYear = $availableYears->first() ?? date('Y');

        $vendorOrgStats = Cache::remember("vendor_org_stats_{$decodedVendor}_{$decodedOrganization}_{$defaultYear}", 300, function () use ($decodedVendor, $decodedOrganization, $defaultYear) {
            return $this->analyticsService->getVendorOrganizationStats($decodedVendor, $decodedOrganization, $defaultYear);
        });

        return view('vendor.organization-contracts', compact(
            'decodedVendor',
            'decodedOrganization',
            'vendorOrgStats',
            'availableYears'
        ));
    }

    public function vendorOrganizationContractsData(Request $request, string $vendor, string $organization): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);

        $query = ProcurementContract::query()
            ->where('vendor_name', $decodedVendor)
            ->where('organization', $decodedOrganization);

        $selectedYear = $request->get('year');
        if (! $selectedYear) {
            // Get the most recent year with data for this vendor-organization combination
            $selectedYear = $this->analyticsService->getAvailableYearsForVendorOrganization($decodedVendor, $decodedOrganization)->first() ?? date('Y');
        }
        $query->where('contract_year', $selectedYear);

        if ($request->has('search') && $request->search['value']) {
            $searchValue = $request->search['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('reference_number', 'like', "%{$searchValue}%")
                    ->orWhere('description_of_work_english', 'like', "%{$searchValue}%")
                    ->orWhere('commodity', 'like', "%{$searchValue}%");
            });
        }

        $totalRecords = ProcurementContract::where('vendor_name', $decodedVendor)
            ->where('organization', $decodedOrganization)
            ->where('contract_year', $selectedYear)
            ->count();
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

        $data = $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $contract->total_contract_value ? '$'.number_format($contract->total_contract_value, 2) : '-',
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
}
