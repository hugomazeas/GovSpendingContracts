<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\ProcurementAnalyticsService;
use App\Services\VendorDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VendorController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ContractRepositoryInterface $contractRepository,
        private readonly VendorDataService $vendorDataService
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

        if (! $request->get('year')) {
            $selectedYear = $this->analyticsService->getAvailableYearsForVendor($decodedVendor)->first() ?? date('Y');
            $request->merge(['year' => $selectedYear]);
        }

        $repositoryData = $this->contractRepository->getVendorDataTableData($decodedVendor, $request);

        $data = $this->vendorDataService->formatVendorContractsData($repositoryData['contracts'], $decodedVendor);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
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

        if (! $request->get('year')) {
            $selectedYear = $this->analyticsService->getAvailableYearsForVendorOrganization($decodedVendor, $decodedOrganization)->first() ?? date('Y');
            $request->merge(['year' => $selectedYear]);
        }

        $repositoryData = $this->contractRepository->getVendorOrganizationDataTableData($decodedVendor, $decodedOrganization, $request);

        $data = $this->vendorDataService->formatVendorOrganizationContractsData($repositoryData['contracts']);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
            'data' => $data,
        ]);
    }
}
