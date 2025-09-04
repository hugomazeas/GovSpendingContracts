<?php

namespace App\Http\Controllers\Ajax;

use App\Helpers\CurrencyFormatter;
use App\Http\Controllers\Controller;
use App\Models\ProcurementContract;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService
    ) {}

    public function statsGrid(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "dashboard_stats_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return $this->analyticsService->getGeneralStatistics($selectedYear);
        });

        return response()->json([
            'html' => view('components.stats-grid', ['stats' => $stats])->render(),
        ]);
    }

    public function vendorLeaderboards(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "vendor_leaderboards_{$selectedYear}";

        $data = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return [
                'topVendorsByCount' => $this->analyticsService->getTopVendorsByCount($selectedYear),
                'topVendorsByValue' => $this->analyticsService->getTopVendorsByValue($selectedYear),
            ];
        });

        return response()->json([
            'html' => [
                'byCount' => view('components.vendors-leaderboard', [
                    'vendors' => $data['topVendorsByCount'],
                    'title' => 'Top Vendors by Contract Count',
                    'icon' => 'fas fa-trophy',
                    'metric' => 'contracts',
                ])->render(),
                'byValue' => view('components.vendors-leaderboard', [
                    'vendors' => $data['topVendorsByValue'],
                    'title' => 'Top Vendors by Total Value',
                    'icon' => 'fas fa-dollar-sign',
                    'metric' => 'value',
                ])->render(),
            ],
        ]);
    }

    public function organizationLeaderboard(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "organization_leaderboard_{$selectedYear}";

        $organizations = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return $this->analyticsService->getTopOrganizationsBySpending($selectedYear);
        });

        return response()->json([
            'html' => view('components.organizations-leaderboard', [
                'organizations' => $organizations,
            ])->render(),
        ]);
    }

    public function vendorCountriesLeaderboard(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "vendor_countries_leaderboard_{$selectedYear}";

        $countries = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return $this->analyticsService->getTopVendorCountriesByValue($selectedYear);
        });

        return response()->json([
            'html' => view('components.vendor-countries-leaderboard', [
                'countries' => $countries,
            ])->render(),
        ]);
    }

    public function organizationDetails(Request $request, string $organization): JsonResponse
    {
        $decodedOrganization = urldecode($organization);
        $selectedYear = $request->get('year');
        $availableYears = $this->analyticsService->getAvailableYearsForOrganization($decodedOrganization);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "org_details_{$decodedOrganization}_{$selectedYear}";

        $data = Cache::remember($cacheKey, 300, function () use ($decodedOrganization, $selectedYear) {
            return [
                'topVendors' => $this->analyticsService->getTopVendorsForOrganization($decodedOrganization, $selectedYear),
                'topContracts' => $this->analyticsService->getTopContractsForOrganization($decodedOrganization, $selectedYear),
            ];
        });

        return response()->json([
            'html' => [
                'topVendors' => view('partials.organization.top-vendors', [
                    'topVendorsForOrg' => $data['topVendors'],
                    'organizationName' => $decodedOrganization,
                ])->render(),
                'topContracts' => view('partials.organization.top-contracts', [
                    'topContracts' => $data['topContracts'],
                ])->render(),
            ],
        ]);
    }

    public function organizationStats(Request $request, string $organization): JsonResponse
    {
        $decodedOrganization = urldecode($organization);
        $selectedYear = $request->get('year');
        $availableYears = $this->analyticsService->getAvailableYearsForOrganization($decodedOrganization);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "org_stats_ajax_{$decodedOrganization}_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($decodedOrganization, $selectedYear) {
            return $this->analyticsService->getOrganizationStats($decodedOrganization, $selectedYear);
        });

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function organizationSpendingChart(Request $request, string $organization): JsonResponse
    {
        $decodedOrganization = urldecode($organization);

        $cacheKey = "org_spending_chart_{$decodedOrganization}";

        $chartData = Cache::remember($cacheKey, 600, function () use ($decodedOrganization) {
            $spendingByYear = ProcurementContract::where('organization', $decodedOrganization)
                ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
                ->whereNotNull('contract_year')
                ->whereNotNull('total_contract_value')
                ->groupBy('contract_year')
                ->orderBy('contract_year', 'asc')
                ->get();

            $years = $spendingByYear->pluck('contract_year')->toArray();
            $spending = $spendingByYear->pluck('total_spending')->toArray();
            $contracts = $spendingByYear->pluck('contract_count')->toArray();

            return [
                'years' => $years,
                'spending' => $spending,
                'contracts' => $contracts,
            ];
        });

        return response()->json($chartData);
    }

    public function governmentSpendingChart(Request $request): JsonResponse
    {
        $cacheKey = 'government_spending_chart';

        $chartData = Cache::remember($cacheKey, 600, function () {
            $spendingByYear = ProcurementContract::selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
                ->whereNotNull('contract_year')
                ->whereNotNull('total_contract_value')
                ->groupBy('contract_year')
                ->orderBy('contract_year', 'asc')
                ->get();

            $years = $spendingByYear->pluck('contract_year')->toArray();
            $spending = $spendingByYear->pluck('total_spending')->toArray();
            $contracts = $spendingByYear->pluck('contract_count')->toArray();

            return [
                'years' => $years,
                'spending' => $spending,
                'contracts' => $contracts,
            ];
        });

        return response()->json($chartData);
    }

    public function organizationsPieChart(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "organizations_pie_chart_{$selectedYear}";

        $chartData = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            // Get top 10 organizations by spending for the year
            $topOrganizations = ProcurementContract::where('contract_year', $selectedYear)
                ->whereNotNull('organization')
                ->whereNotNull('total_contract_value')
                ->selectRaw('organization, SUM(total_contract_value) as total_spending')
                ->groupBy('organization')
                ->orderByDesc('total_spending')
                ->limit(10)
                ->get();

            // Get total spending for the year to calculate "Others"
            $totalYearSpending = ProcurementContract::where('contract_year', $selectedYear)
                ->whereNotNull('total_contract_value')
                ->sum('total_contract_value');

            $topOrganizationsSpending = $topOrganizations->sum('total_spending');
            $othersSpending = $totalYearSpending - $topOrganizationsSpending;

            $labels = [];
            $data = [];
            $colors = [
                'rgba(99, 102, 241, 0.8)',   // Purple - 1st
                'rgba(59, 130, 246, 0.8)',   // Blue - 2nd
                'rgba(34, 197, 94, 0.8)',    // Green - 3rd
                'rgba(245, 158, 11, 0.8)',   // Amber - 4th
                'rgba(239, 68, 68, 0.8)',    // Red - 5th
                'rgba(168, 85, 247, 0.8)',   // Violet - 6th
                'rgba(20, 184, 166, 0.8)',   // Teal - 7th
                'rgba(251, 146, 60, 0.8)',   // Orange - 8th
                'rgba(244, 63, 94, 0.8)',    // Rose - 9th
                'rgba(139, 92, 246, 0.8)',   // Purple variant - 10th
                'rgba(156, 163, 175, 0.8)',   // Gray for others
            ];

            // Add top 10 organizations
            foreach ($topOrganizations as $org) {
                $labels[] = strlen($org->organization) > 30 ?
                    substr($org->organization, 0, 30).'...' :
                    $org->organization;
                $data[] = $org->total_spending;
            }

            // Add "Others" if there's remaining spending
            if ($othersSpending > 0) {
                $labels[] = 'Others';
                $data[] = $othersSpending;
            }

            return [
                'labels' => $labels,
                'data' => $data,
                'colors' => array_slice($colors, 0, count($labels)),
                'total' => $totalYearSpending,
                'year' => $selectedYear,
            ];
        });

        return response()->json($chartData);
    }

    public function vendorStats(Request $request, string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $selectedYear = $request->get('year');
        $availableYears = $this->analyticsService->getAvailableYearsForVendor($decodedVendor);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "vendor_stats_ajax_{$decodedVendor}_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($decodedVendor, $selectedYear) {
            return $this->analyticsService->getVendorStats($decodedVendor, $selectedYear);
        });

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function vendorSpendingChart(Request $request, string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);

        $cacheKey = "vendor_revenue_chart_{$decodedVendor}";

        $chartData = Cache::remember($cacheKey, 600, function () use ($decodedVendor) {
            $revenueByYear = ProcurementContract::where('vendor_name', $decodedVendor)
                ->selectRaw('contract_year, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
                ->whereNotNull('contract_year')
                ->whereNotNull('total_contract_value')
                ->groupBy('contract_year')
                ->orderBy('contract_year', 'asc')
                ->get();

            $years = $revenueByYear->pluck('contract_year')->toArray();
            $revenue = $revenueByYear->pluck('total_value')->toArray();
            $contracts = $revenueByYear->pluck('contract_count')->toArray();

            return [
                'years' => $years,
                'revenue' => $revenue,
                'contracts' => $contracts,
            ];
        });

        return response()->json($chartData);
    }

    public function vendorMinisterLeaderboard(Request $request, string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $selectedYear = $request->get('year');
        $availableYears = $this->analyticsService->getAvailableYearsForVendor($decodedVendor);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "vendor_minister_leaderboard_{$decodedVendor}_{$selectedYear}";

        $ministers = Cache::remember($cacheKey, 300, function () use ($decodedVendor, $selectedYear) {
            return $this->analyticsService->getTopMinistersForVendor($decodedVendor, $selectedYear);
        });

        // Create chart data
        $chartData = [
            'labels' => $ministers->take(8)->pluck('organization')->map(function ($org) {
                return strlen($org) > 25 ? substr($org, 0, 25).'...' : $org;
            })->toArray(),
            'values' => $ministers->take(8)->pluck('total_value')->toArray(),
        ];

        return response()->json([
            'html' => view('partials.vendor.minister-leaderboard', [
                'ministers' => $ministers,
                'vendorName' => $decodedVendor,
            ])->render(),
            'chartData' => $chartData,
        ]);
    }

    public function vendorOrganizationStats(Request $request, string $vendor, string $organization): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);
        $selectedYear = $request->get('year');
        $availableYears = $this->analyticsService->getAvailableYearsForVendorOrganization($decodedVendor, $decodedOrganization);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "vendor_org_stats_ajax_{$decodedVendor}_{$decodedOrganization}_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($decodedVendor, $decodedOrganization, $selectedYear) {
            return $this->analyticsService->getVendorOrganizationStats($decodedVendor, $decodedOrganization, $selectedYear);
        });

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function vendorOrganizationSpendingChart(Request $request, string $vendor, string $organization): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);

        $cacheKey = "vendor_org_spending_chart_{$decodedVendor}_{$decodedOrganization}";

        $chartData = Cache::remember($cacheKey, 600, function () use ($decodedVendor, $decodedOrganization) {
            $spendingByYear = $this->analyticsService->getVendorOrganizationSpendingOverTime($decodedVendor, $decodedOrganization);

            $years = $spendingByYear->pluck('contract_year')->toArray();
            $spending = $spendingByYear->pluck('total_value')->toArray();
            $contracts = $spendingByYear->pluck('contract_count')->toArray();

            return [
                'years' => $years,
                'spending' => $spending,
                'contracts' => $contracts,
            ];
        });

        return response()->json($chartData);
    }

    public function vendorOrganizationHistoricalTotals(string $vendor, string $organization): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);

        $cacheKey = "vendor_org_historical_totals_{$decodedVendor}_{$decodedOrganization}";

        $historicalData = Cache::remember($cacheKey, 1800, function () use ($decodedVendor, $decodedOrganization) {
            // Get all contracts for this vendor-organization partnership across all years
            $contracts = ProcurementContract::where('vendor_name', $decodedVendor)
                ->where('organization', $decodedOrganization)
                ->whereNotNull('total_contract_value')
                ->select('total_contract_value', 'contract_year')
                ->get();

            $totalContracts = $contracts->count();
            $totalValue = $contracts->sum('total_contract_value');

            // Calculate inflation-adjusted total
            $inflationAdjustedTotal = $contracts->sum(function ($contract) {
                return CurrencyFormatter::calculateInflationAdjusted(
                    $contract->total_contract_value,
                    $contract->contract_year
                );
            });

            return [
                'total_contracts' => $totalContracts,
                'total_value' => $totalValue,
                'inflation_adjusted_total' => CurrencyFormatter::format($inflationAdjustedTotal),
            ];
        });

        return response()->json($historicalData);
    }

    public function vendorHistoricalTotals(string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);

        $cacheKey = "vendor_historical_totals_{$decodedVendor}";

        $historicalData = Cache::remember($cacheKey, 1800, function () use ($decodedVendor) {
            // Get all contracts for this vendor across all years
            $contracts = ProcurementContract::where('vendor_name', $decodedVendor)
                ->whereNotNull('total_contract_value')
                ->select('total_contract_value', 'contract_year')
                ->get();

            $totalContracts = $contracts->count();
            $totalValue = $contracts->sum('total_contract_value');

            // Calculate inflation-adjusted total
            $inflationAdjustedTotal = $contracts->sum(function ($contract) {
                return CurrencyFormatter::calculateInflationAdjusted(
                    $contract->total_contract_value,
                    $contract->contract_year
                );
            });

            return [
                'total_contracts' => $totalContracts,
                'total_value' => $totalValue,
                'inflation_adjusted_total' => CurrencyFormatter::format($inflationAdjustedTotal),
            ];
        });

        return response()->json($historicalData);
    }

    public function dashboardHistoricalTotals(): JsonResponse
    {
        $cacheKey = "dashboard_historical_totals";

        $historicalData = Cache::remember($cacheKey, 1800, function () {
            // Get all contracts across all vendors and organizations
            $contracts = ProcurementContract::whereNotNull('total_contract_value')
                ->select('total_contract_value', 'contract_year')
                ->get();

            $totalContracts = $contracts->count();
            $totalValue = $contracts->sum('total_contract_value');

            // Calculate inflation-adjusted total
            $inflationAdjustedTotal = $contracts->sum(function ($contract) {
                return CurrencyFormatter::calculateInflationAdjusted(
                    $contract->total_contract_value,
                    $contract->contract_year
                );
            });

            return [
                'total_contracts' => $totalContracts,
                'total_value' => $totalValue,
                'inflation_adjusted_total' => CurrencyFormatter::format($inflationAdjustedTotal),
            ];
        });

        return response()->json($historicalData);
    }
}
