<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
use App\Services\ProcurementAnalyticsService;
use App\Services\VendorDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProcurementContractController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ProcurementContractRepositoryInterface $contractRepository,
        private readonly VendorDataService $vendorDataService
    ) {}

    public function index(Request $request): View
    {
        $availableYears = Cache::remember('available_years', 3600, function () {
            return $this->analyticsService->getAvailableYears();
        });

        // Default year for initial stats loading (will be overridden by frontend)
        $defaultYear = $availableYears->first() ?? date('Y');
        $stats = Cache::remember("dashboard_stats_{$defaultYear}", 300, function () use ($defaultYear) {
            return $this->analyticsService->getGeneralStatistics($defaultYear);
        });

        return view('procurement-contracts.dashboard', compact(
            'stats',
            'availableYears'
        ));
    }

    public function contracts(Request $request): View
    {
        $availableYears = Cache::remember('available_years', 3600, function () {
            return $this->analyticsService->getAvailableYears();
        });

        return view('procurement-contracts.index', compact('availableYears'));
    }

    public function show(ProcurementContract $contract): View
    {
        return view('procurement-contracts.show', compact('contract'));
    }

    public function data(Request $request): JsonResponse
    {
        $repositoryData = $this->contractRepository->getDataTableData($request);

        $data = $this->vendorDataService->formatGeneralContractsData($repositoryData['contracts']);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
            'data' => $data,
        ]);
    }
}
