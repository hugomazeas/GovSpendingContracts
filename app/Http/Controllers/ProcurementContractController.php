<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProcurementContractController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ProcurementContractRepositoryInterface $contractRepository
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

        $data = $repositoryData['contracts']->map(function ($contract) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'vendor_name' => $contract->vendor_name ?
                    '<a href="'.route('vendor.detail', rawurlencode($contract->vendor_name)).'" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors">'.e($contract->vendor_name).'</a>' :
                    '-',
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $contract->total_contract_value ? '$'.number_format($contract->total_contract_value, 2) : '-',
                'organization' => $contract->organization ?
                    '<a href="'.route('organization.detail', ['organization' => urlencode($contract->organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($contract->organization).'</a>' :
                    '-',
                'description_of_work_english' => $contract->description_of_work_english ?
                    (strlen($contract->description_of_work_english) > 100 ?
                        substr($contract->description_of_work_english, 0, 100).'...' :
                        $contract->description_of_work_english) : '-',
            ];
        });

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
            'data' => $data,
        ]);
    }
}
