<?php

namespace App\Http\Controllers;

use App\Models\ProcurementContract;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class OrganizationController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService
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
            // Get the last 4 years to calculate percentage changes (but only display 3)
            $currentYear = date('Y');
            $lastThreeYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3];
            $fourthYear = $currentYear - 4;
            $allYears = [$currentYear - 1, $currentYear - 2, $currentYear - 3, $currentYear - 4];

            // Base query for organizations with their spending data
            $query = ProcurementContract::selectRaw("
                    organization,
                    SUM(CASE WHEN contract_year = {$lastThreeYears[0]} THEN total_contract_value ELSE 0 END) as spending_year_1,
                    SUM(CASE WHEN contract_year = {$lastThreeYears[1]} THEN total_contract_value ELSE 0 END) as spending_year_2,
                    SUM(CASE WHEN contract_year = {$lastThreeYears[2]} THEN total_contract_value ELSE 0 END) as spending_year_3,
                    SUM(CASE WHEN contract_year = {$fourthYear} THEN total_contract_value ELSE 0 END) as spending_year_4,
                    SUM(total_contract_value) as total_spending
                ")
                ->whereNotNull('organization')
                ->whereNotNull('total_contract_value')
                ->whereIn('contract_year', $allYears)
                ->groupBy('organization');

            // Apply search filter
            if (! empty($searchValue)) {
                $query->where('organization', 'LIKE', "%{$searchValue}%");
            }

            // Clone query for counting
            $totalRecords = Cache::remember('organizations_total_count', 3600, function () use ($allYears) {
                return ProcurementContract::selectRaw('COUNT(DISTINCT organization) as count')
                    ->whereNotNull('organization')
                    ->whereNotNull('total_contract_value')
                    ->whereIn('contract_year', $allYears)
                    ->first()
                    ->count ?? 0;
            });

            $filteredQuery = clone $query;
            $filteredRecords = $filteredQuery->get()->count();

            // Apply sorting
            $columns = ['organization', 'spending_year_1', 'spending_year_2', 'spending_year_3'];
            $orderByColumn = $columns[$orderColumn] ?? 'spending_year_1';

            $query->orderBy($orderByColumn, $orderDir);

            // Apply pagination
            $organizations = $query->offset($start)
                ->limit($length)
                ->get();

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
}
