<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\ProcurementContractRepositoryInterface;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ProcurementContractRepositoryInterface $contractRepository
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

        return Cache::remember($cacheKey, 1800, function () use ($request, $searchValue, $orderColumn, $orderDir, $start, $length) {
            $currentYear = date('Y');
            $lastThreeYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3];
            $fourthYear = $currentYear - 4;
            $allYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3, $currentYear - 4];

            $organizations = $this->contractRepository->getOrganizationSpendingAnalysis($allYears);

            // Apply search filter
            if (! empty($searchValue)) {
                $organizations = $organizations->filter(function ($org) use ($searchValue) {
                    return stripos($org->organization, $searchValue) !== false;
                });
            }

            $totalRecords = Cache::remember('organizations_total_count', 3600, function () use ($allYears) {
                return $this->contractRepository->getOrganizationSpendingAnalysis($allYears)->count();
            });

            $filteredRecords = $organizations->count();

            // Apply sorting
            $columns = ['organization', 'spending_year_1', 'spending_year_2', 'spending_year_3'];
            $orderByColumn = $columns[$orderColumn] ?? 'spending_year_1';

            $organizations = $organizations->sortBy($orderByColumn, SORT_REGULAR, $orderDir === 'desc');

            // Apply pagination
            $organizations = $organizations->slice($start, $length);

            $data = $organizations->map(function ($org) use ($lastThreeYears) {
                $year1Spending = (float) $org->spending_year_1;
                $year2Spending = (float) $org->spending_year_2;
                $year3Spending = (float) $org->spending_year_3;
                $year4Spending = (float) $org->spending_year_4;

                // Calculate year-over-year percentage changes with HTML color coding
                $change1to2 = '';
                $change2to3 = '';
                $change3to4 = '';

                // Change from year 2 to year 1 (most recent change)
                if ($year2Spending > 0) {
                    $percentageChange = (($year1Spending - $year2Spending) / $year2Spending) * 100;
                    if ($percentageChange > 0) {
                        $change1to2 = '<span class="text-green-600">↗ +'.number_format($percentageChange, 1).'%</span>';
                    } elseif ($percentageChange < 0) {
                        $change1to2 = '<span class="text-red-600">↘ '.number_format($percentageChange, 1).'%</span>';
                    } else {
                        $change1to2 = '<span class="text-gray-600">→ 0%</span>';
                    }
                } elseif ($year1Spending > 0) {
                    $change1to2 = '--';
                }

                // Change from year 3 to year 2
                if ($year3Spending > 0) {
                    $percentageChange = (($year2Spending - $year3Spending) / $year3Spending) * 100;
                    if ($percentageChange > 0) {
                        $change2to3 = '<span class="text-green-600">↗ +'.number_format($percentageChange, 1).'%</span>';
                    } elseif ($percentageChange < 0) {
                        $change2to3 = '<span class="text-red-600">↘ '.number_format($percentageChange, 1).'%</span>';
                    } else {
                        $change2to3 = '<span class="text-gray-600">→ 0%</span>';
                    }
                } elseif ($year2Spending > 0) {
                    $change2to3 = '--';
                }

                // Change from year 4 to year 3 (oldest change)
                if ($year4Spending > 0) {
                    $percentageChange = (($year3Spending - $year4Spending) / $year4Spending) * 100;
                    if ($percentageChange > 0) {
                        $change3to4 = '<span class="text-green-600">↗ +'.number_format($percentageChange, 1).'%</span>';
                    } elseif ($percentageChange < 0) {
                        $change3to4 = '<span class="text-red-600">↘ '.number_format($percentageChange, 1).'%</span>';
                    } else {
                        $change3to4 = '<span class="text-gray-600">→ 0%</span>';
                    }
                } elseif ($year3Spending > 0) {
                    $change3to4 = '--';
                }

                return [
                    'organization' => '<a href="'.route('organization.detail', ['organization' => urlencode($org->organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($org->organization).'</a>',
                    'spending_'.$lastThreeYears[0] => $year1Spending > 0 ? '$'.number_format($year1Spending, 0) : '-',
                    'spending_'.$lastThreeYears[1] => $year2Spending > 0 ? '$'.number_format($year2Spending, 0) : '-',
                    'spending_'.$lastThreeYears[2] => $year3Spending > 0 ? '$'.number_format($year3Spending, 0) : '-',
                    'change_year_1' => $change1to2,
                    'change_year_2' => $change2to3,
                    'change_year_3' => $change3to4,
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data,
            ]);
        });
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

        $data = $repositoryData['contracts']->map(function ($contract) use ($decodedOrganization) {
            return [
                'id' => $contract->id,
                'vendor_name' => $contract->vendor_name ?
                    '<div class="flex flex-col gap-1"><a href="'.route('vendor.detail', ['vendor' => urlencode($contract->vendor_name)]).'" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors">'.e($contract->vendor_name).'</a><a href="'.route('vendor.organization.contracts', ['vendor' => urlencode($contract->vendor_name), 'organization' => urlencode($decodedOrganization)]).'" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors"><i class="fas fa-handshake mr-1"></i>View partnership</a></div>' :
                    '-',
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
            'recordsTotal' => $repositoryData['totalRecords'],
            'recordsFiltered' => $repositoryData['filteredRecords'],
            'data' => $data,
        ]);
    }
}
