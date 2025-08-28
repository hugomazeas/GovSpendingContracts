<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class VendorController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService
    ) {}

    public function detail(Request $request, string $vendor): \Illuminate\Contracts\View\View
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
        $selectedYear = $request->get('year', date('Y'));
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

        $data = $contracts->map(function ($contract) {
            return [
                'reference_number' => $contract->reference_number,
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
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}
