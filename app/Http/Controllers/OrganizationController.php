<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
use App\Services\OrganizationDataService;
use App\Services\ProcurementAnalyticsService;
use App\Services\VendorDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ProcurementContractRepositoryInterface $contractRepository,
        private readonly OrganizationDataService $organizationDataService,
        private readonly VendorDataService $vendorDataService
    ) {}

    public function index(): View
    {
        return view('organizations.index');
    }

    public function data(Request $request): JsonResponse
    {
        $searchValue = $request->search['value'] ?? '';
        $orderColumn = $request->order[0]['column'] ?? 0;
        $orderDir = $request->order[0]['dir'] ?? 'desc';
        $start = $request->start ?? 0;
        $length = $request->length ?? 10;

        // Create cache key based on request parameters
        $cacheKey = 'organizations_data_'.md5(serialize([
            'search' => $searchValue,
            'order_col' => $orderColumn,
            'order_dir' => $orderDir,
            'start' => $start,
            'length' => $length,
        ]));

        $responseData = Cache::remember($cacheKey, 1800, function () use ($request, $searchValue, $orderColumn, $orderDir, $start, $length) {
            $currentYear = date('Y');
            $lastThreeYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3];
            $allYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3, $currentYear - 4];

            $organizations = $this->contractRepository->getOrganizationSpendingAnalysis($allYears);
            $organizations = $this->organizationDataService->applySearch($organizations, $searchValue);

            $totalRecords = Cache::remember('organizations_total_count', 3600, function () use ($allYears) {
                return $this->contractRepository->getOrganizationSpendingAnalysis($allYears)->count();
            });

            $filteredRecords = $organizations->count();
            $organizations = $this->organizationDataService->applySorting($organizations, $orderColumn, $orderDir);
            $organizations = $organizations->slice($start, $length);

            $data = $this->organizationDataService->calculateYearOverYearChanges($organizations, $lastThreeYears);

            return $this->organizationDataService->formatDataTableResponse([
                'draw' => $request->draw,
            ], $data, $totalRecords, $filteredRecords);
        });

        return response()->json($responseData);
    }

    public function detail(Request $request, string $organization): View
    {
        $decodedOrganization = urldecode($organization);

        $availableYears = Cache::remember("available_years_{$decodedOrganization}", 1800, function () use ($decodedOrganization) {
            return $this->analyticsService->getAvailableYearsForOrganization($decodedOrganization);
        });

        // Default year for initial stats (will be overridden by frontend)
        $defaultYear = $availableYears->first() ?? date('Y');

        $contractsByYear = Cache::remember("org_yearly_{$decodedOrganization}", 600, function () use ($decodedOrganization) {
            return $this->analyticsService->getContractsByYearForOrganization($decodedOrganization);
        });

        $organizationStats = Cache::remember("org_stats_{$decodedOrganization}_{$defaultYear}", 300, function () use ($decodedOrganization, $defaultYear) {
            return $this->analyticsService->getOrganizationStats($decodedOrganization, $defaultYear);
        });

        return view('organization.dashboard', compact(
            'decodedOrganization',
            'organizationStats',
            'contractsByYear',
            'availableYears'
        ));
    }

    public function contractsData(Request $request, string $organization): JsonResponse
    {
        $decodedOrganization = urldecode($organization);

        if (! $request->get('year')) {
            $selectedYear = $this->analyticsService->getAvailableYearsForOrganization($decodedOrganization)->first() ?? date('Y');
            $request->merge(['year' => $selectedYear]);
        }

        $repositoryData = $this->contractRepository->getOrganizationDataTableData($decodedOrganization, $request);

        $data = $this->vendorDataService->formatOrganizationContractsData($repositoryData['contracts'], $decodedOrganization);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
            'data' => $data,
        ]);
    }
}
