<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\ContractRepositoryInterface;
use App\Services\DashboardDataService;
use App\Services\ProcurementAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private readonly ProcurementAnalyticsService $analyticsService,
        private readonly ContractRepositoryInterface $contractRepository,
        private readonly DashboardDataService $dashboardDataService
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
            $spendingByYear = $this->contractRepository->getSpendingByYear($decodedOrganization);

            return $this->dashboardDataService->prepareChartData($spendingByYear, []);
        });

        return response()->json($chartData);
    }

    public function governmentSpendingChart(Request $request): JsonResponse
    {
        $cacheKey = 'government_spending_chart';

        $chartData = Cache::remember($cacheKey, 600, function () {
            $spendingByYear = $this->contractRepository->getSpendingByYear();

            return $this->dashboardDataService->prepareChartData($spendingByYear, []);
        });

        return response()->json($chartData);
    }

    public function organizationsPieChart(Request $request): JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "organizations_pie_chart_{$selectedYear}";

        $chartData = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            $pieData = $this->contractRepository->getOrganizationsPieChartData($selectedYear);

            return $this->dashboardDataService->generatePieChartData(
                $pieData['topOrganizations'],
                $pieData['totalYearSpending'],
                $selectedYear
            );
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
            $revenueByYear = $this->contractRepository->getSpendingByYearForVendor($decodedVendor);

            return $this->dashboardDataService->prepareChartData($revenueByYear, [
                'spending' => 'total_value',
            ]);
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

        return response()->json(
            $this->dashboardDataService->generateVendorMinisterChart($ministers, $decodedVendor)
        );
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

            return $this->dashboardDataService->prepareChartData($spendingByYear, [
                'spending' => 'total_value',
            ]);
        });

        return response()->json($chartData);
    }

    public function vendorOrganizationHistoricalTotals(string $vendor, string $organization): JsonResponse
    {
        $decodedVendor = urldecode($vendor);
        $decodedOrganization = urldecode($organization);

        $cacheKey = "vendor_org_historical_totals_{$decodedVendor}_{$decodedOrganization}";

        $historicalData = Cache::remember($cacheKey, 1800, function () use ($decodedVendor, $decodedOrganization) {
            $contracts = $this->contractRepository->getVendorOrganizationHistoricalContracts($decodedVendor, $decodedOrganization);

            return $this->dashboardDataService->calculateInflationAdjustedTotals($contracts);
        });

        return response()->json($historicalData);
    }

    public function vendorHistoricalTotals(string $vendor): JsonResponse
    {
        $decodedVendor = urldecode($vendor);

        $cacheKey = "vendor_historical_totals_{$decodedVendor}";

        $historicalData = Cache::remember($cacheKey, 1800, function () use ($decodedVendor) {
            $contracts = $this->contractRepository->getVendorHistoricalContracts($decodedVendor);

            return $this->dashboardDataService->calculateInflationAdjustedTotals($contracts);
        });

        return response()->json($historicalData);
    }

    public function dashboardHistoricalTotals(): JsonResponse
    {
        $cacheKey = 'dashboard_historical_totals';

        $historicalData = Cache::remember($cacheKey, 1800, function () {
            $contracts = $this->contractRepository->getAllHistoricalContracts();

            return $this->dashboardDataService->calculateInflationAdjustedTotals($contracts);
        });

        return response()->json($historicalData);
    }
}
