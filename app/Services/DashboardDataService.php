<?php

namespace App\Services;

use App\Helpers\CurrencyFormatter;
use Illuminate\Support\Collection;

class DashboardDataService
{
    public function generatePieChartData(Collection $topOrganizations, float $totalYearSpending, int $selectedYear): array
    {
        $topOrganizationsSpending = $topOrganizations->sum('total_spending');
        $othersSpending = $totalYearSpending - $topOrganizationsSpending;

        $labels = [];
        $data = [];
        $colors = $this->getPieChartColors();

        foreach ($topOrganizations as $org) {
            $labels[] = $this->truncateOrganizationName($org->organization);
            $data[] = $org->total_spending;
        }

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
    }

    public function generateVendorMinisterChart(Collection $ministers, string $vendorName): array
    {
        $chartData = [
            'labels' => $ministers->take(8)->pluck('organization')->map(function ($org) {
                return $this->truncateText($org, 25);
            })->toArray(),
            'values' => $ministers->take(8)->pluck('total_value')->toArray(),
        ];

        return [
            'html' => view('partials.vendor.minister-leaderboard', [
                'ministers' => $ministers,
                'vendorName' => $vendorName,
            ])->render(),
            'chartData' => $chartData,
        ];
    }

    public function calculateInflationAdjustedTotals(Collection $contracts): array
    {
        $totalContracts = $contracts->count();
        $totalValue = $contracts->sum('total_contract_value');

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
    }

    public function prepareChartData(Collection $data, array $fields): array
    {
        return [
            'years' => $data->pluck($fields['year'] ?? 'contract_year')->toArray(),
            'spending' => $data->pluck($fields['spending'] ?? 'total_spending')->toArray(),
            'contracts' => $data->pluck($fields['contracts'] ?? 'contract_count')->toArray(),
        ];
    }

    private function getPieChartColors(): array
    {
        return [
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
            'rgba(156, 163, 175, 0.8)',  // Gray for others
        ];
    }

    private function truncateOrganizationName(string $name): string
    {
        return $this->truncateText($name, 30);
    }

    private function truncateText(string $text, int $length): string
    {
        return strlen($text) > $length ? substr($text, 0, $length).'...' : $text;
    }
}
