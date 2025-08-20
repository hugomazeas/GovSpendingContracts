<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Models\ProcurementContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function statsGrid(Request $request): \Illuminate\Http\JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "dashboard_stats_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return $this->getStatistics($selectedYear);
        });

        return response()->json([
            'html' => view('components.stats-grid', ['stats' => $stats])->render(),
        ]);
    }

    public function vendorLeaderboards(Request $request): \Illuminate\Http\JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "vendor_leaderboards_{$selectedYear}";

        $data = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return [
                'topVendorsByCount' => $this->getTopVendorsByCount($selectedYear),
                'topVendorsByValue' => $this->getTopVendorsByValue($selectedYear),
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

    public function organizationLeaderboard(Request $request): \Illuminate\Http\JsonResponse
    {
        $selectedYear = $request->get('year', date('Y'));

        $cacheKey = "organization_leaderboard_{$selectedYear}";

        $organizations = Cache::remember($cacheKey, 300, function () use ($selectedYear) {
            return $this->getTopOrganizationsBySpending($selectedYear);
        });

        return response()->json([
            'html' => view('components.organizations-leaderboard', [
                'organizations' => $organizations,
            ])->render(),
        ]);
    }

    public function organizationDetails(Request $request, string $organization): \Illuminate\Http\JsonResponse
    {
        $decodedOrganization = urldecode($organization);
        $selectedYear = $request->get('year');
        $availableYears = $this->getAvailableYearsForOrganization($decodedOrganization);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "org_details_{$decodedOrganization}_{$selectedYear}";

        $data = Cache::remember($cacheKey, 300, function () use ($decodedOrganization, $selectedYear) {
            return [
                'topVendors' => $this->getTopVendorsForOrganization($decodedOrganization, $selectedYear),
                'topContracts' => $this->getTopContractsForOrganization($decodedOrganization, $selectedYear),
            ];
        });

        return response()->json([
            'html' => [
                'topVendors' => view('partials.organization.top-vendors', [
                    'topVendorsForOrg' => $data['topVendors'],
                ])->render(),
                'topContracts' => view('partials.organization.top-contracts', [
                    'topContracts' => $data['topContracts'],
                ])->render(),
            ],
        ]);
    }

    public function organizationStats(Request $request, string $organization): \Illuminate\Http\JsonResponse
    {
        $decodedOrganization = urldecode($organization);
        $selectedYear = $request->get('year');
        $availableYears = $this->getAvailableYearsForOrganization($decodedOrganization);

        if (! $selectedYear || ! in_array($selectedYear, $availableYears->toArray())) {
            $selectedYear = $availableYears->first() ?? date('Y');
        }

        $cacheKey = "org_stats_ajax_{$decodedOrganization}_{$selectedYear}";

        $stats = Cache::remember($cacheKey, 300, function () use ($decodedOrganization, $selectedYear) {
            return $this->getOrganizationStats($decodedOrganization, $selectedYear);
        });

        return response()->json([
            'stats' => $stats,
        ]);
    }

    public function organizationSpendingChart(Request $request, string $organization): \Illuminate\Http\JsonResponse
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

    public function governmentSpendingChart(Request $request): \Illuminate\Http\JsonResponse
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
    
    public function organizationsPieChart(Request $request): \Illuminate\Http\JsonResponse
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
                'rgba(156, 163, 175, 0.8)'   // Gray for others
            ];
            
            // Add top 10 organizations
            foreach ($topOrganizations as $org) {
                $labels[] = strlen($org->organization) > 30 ? 
                    substr($org->organization, 0, 30) . '...' : 
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
                'year' => $selectedYear
            ];
        });

        return response()->json($chartData);
    }

    private function getTopVendorsByCount(int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('vendor_name')
            ->groupBy('vendor_name')
            ->orderByDesc('contract_count')
            ->limit(10)
            ->get();
    }

    private function getTopVendorsByValue(int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->where('contract_year', $year)
            ->whereNotNull('vendor_name')
            ->whereNotNull('total_contract_value')
            ->groupBy('vendor_name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    private function getTopOrganizationsBySpending(int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::selectRaw('organization, COUNT(*) as contract_count, SUM(total_contract_value) as total_spending')
            ->where('contract_year', $year)
            ->whereNotNull('organization')
            ->whereNotNull('total_contract_value')
            ->groupBy('organization')
            ->orderByDesc('total_spending')
            ->limit(10)
            ->get();
    }

    private function getTopVendorsForOrganization(string $organization, int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->selectRaw('vendor_name, COUNT(*) as contract_count, SUM(total_contract_value) as total_value')
            ->whereNotNull('vendor_name')
            ->whereNotNull('total_contract_value')
            ->groupBy('vendor_name')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();
    }

    private function getTopContractsForOrganization(string $organization, int $year): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->whereNotNull('total_contract_value')
            ->orderByDesc('total_contract_value')
            ->limit(20)
            ->get(['vendor_name', 'total_contract_value', 'contract_date', 'description_of_work_english', 'reference_number']);
    }

    private function getAvailableYearsForOrganization(string $organization): \Illuminate\Support\Collection
    {
        return ProcurementContract::where('organization', $organization)
            ->selectRaw('DISTINCT contract_year')
            ->whereNotNull('contract_year')
            ->orderByDesc('contract_year')
            ->pluck('contract_year');
    }

    private function getStatistics(int $year): array
    {
        // Single optimized query to get all statistics at once
        $stats = ProcurementContract::where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_value,
                AVG(total_contract_value) as avg_contract_value,
                COUNT(DISTINCT vendor_name) as unique_vendors
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_value' => $stats->total_value ?? 0,
            'unique_vendors' => $stats->unique_vendors ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'year' => $year,
        ];
    }

    private function getOrganizationStats(string $organization, int $year): array
    {
        // Single optimized query for organization statistics
        $stats = ProcurementContract::where('organization', $organization)
            ->where('contract_year', $year)
            ->selectRaw('
                COUNT(*) as total_contracts,
                SUM(total_contract_value) as total_spending,
                AVG(total_contract_value) as avg_contract_value,
                COUNT(DISTINCT vendor_name) as unique_vendors,
                MIN(contract_date) as earliest_date,
                MAX(contract_date) as latest_date
            ')
            ->whereNotNull('total_contract_value')
            ->first();

        return [
            'total_contracts' => $stats->total_contracts ?? 0,
            'total_spending' => $stats->total_spending ?? 0,
            'avg_contract_value' => $stats->avg_contract_value ?? 0,
            'unique_vendors' => $stats->unique_vendors ?? 0,
            'date_range' => [
                'earliest' => $stats->earliest_date,
                'latest' => $stats->latest_date,
            ],
        ];
    }
}
