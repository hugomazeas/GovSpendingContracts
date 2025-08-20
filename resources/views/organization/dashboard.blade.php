@extends('layouts.app')

@section('title', $decodedOrganization . ' - Spending Details')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ url('/') }}" class="btn-primary inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Dashboard
        </a>
    </div>

    <!-- Page Header -->
    <div class="text-center mb-10 pb-8 border-b border-gray-200">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-building-columns mr-3"></i>
            {{ $decodedOrganization }}
        </h1>
        <p class="text-xl text-gray-600">
            Detailed spending analysis and contract breakdown
        </p>
    </div>

    <!-- Spending Over Time Chart -->
    <div class="card mb-16">
        <div class="relative" style="height: 400px;">
            <canvas id="spending-chart" class="w-full h-full"></canvas>

            <!-- Loading state -->
            <div id="chart-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-600">Loading spending data...</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Year Filter -->
    <div class="text-center mb-10">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto border-2 border-indigo-100">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-3xl text-indigo-600 mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">Filter by Year</h2>
            </div>
            <p class="text-gray-600 mb-6">View {{ $decodedOrganization }}'s procurement data for a specific year</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <label for="org-year" class="text-lg font-semibold text-gray-700">Year:</label>
                    <select id="org-year" class="year-selector px-4 py-3 text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition-all duration-200">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                Currently viewing: <span class="font-semibold text-indigo-600" id="org-current-year-display"></span> procurement data
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Contracts -->
        <div class="stats-card">
            <div class="text-4xl text-blue-500 mb-4">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="text-3xl font-bold text-blue-600 mb-2" data-stat="total_contracts">
                {{ number_format($organizationStats['total_contracts']) }}
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                Total Contracts
            </div>
        </div>

        <!-- Total Spending -->
        <div class="stats-card">
            <div class="text-4xl text-green-500 mb-4">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="text-3xl font-bold text-green-600 mb-2" data-stat="total_spending">
                @if($organizationStats['total_spending'] >= 1000000000)
                    ${{ number_format($organizationStats['total_spending'] / 1000000000, 1) }}B
                @else
                    ${{ number_format($organizationStats['total_spending'] / 1000000, 1) }}M
                @endif
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                Total Spending
            </div>
        </div>

        <!-- Unique Vendors -->
        <div class="stats-card">
            <div class="text-4xl text-indigo-500 mb-4">
                <i class="fas fa-building"></i>
            </div>
            <div class="text-3xl font-bold text-indigo-600 mb-2" data-stat="unique_vendors">
                {{ number_format($organizationStats['unique_vendors']) }}
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                Unique Vendors
            </div>
        </div>

        <!-- Average Contract -->
        <div class="stats-card">
            <div class="text-4xl text-amber-500 mb-4">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="text-3xl font-bold text-amber-600 mb-2" data-stat="avg_contract_value">
                ${{ number_format($organizationStats['avg_contract_value'] / 1000, 0) }}K
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                Avg Contract Value
            </div>
        </div>
    </div>

    <!-- Top Vendors and Yearly Spending -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- Top Vendors (Lazy Loaded) -->
        <div class="card" id="top-vendors">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-trophy text-2xl text-amber-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">Top Vendors</h3>
            </div>

            <!-- Loading skeleton -->
            <div class="space-y-4 animate-pulse">
                @for($i = 0; $i < 5; $i++)
                    <div class="flex items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl">
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-gray-300 rounded-full"></div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-300 rounded w-32"></div>
                                <div class="h-3 bg-gray-300 rounded w-20"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-300 rounded w-16"></div>
                    </div>
                @endfor
            </div>
        </div>

        <!-- Top Contracts (Lazy Loaded) -->
        <div class="card" id="top-contracts">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-list-alt text-2xl text-indigo-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">Largest Contracts</h3>
                <div class="ml-auto text-sm text-gray-600">
                    <i class="fas fa-info-circle mr-1"></i>
                    Biggest individual spending items
                </div>
            </div>

            <!-- Loading skeleton -->
            <div class="space-y-4 animate-pulse">
                @for($i = 0; $i < 8; $i++)
                    <div class="flex items-start justify-between p-4 mb-3 bg-gray-50 rounded-xl">
                        <div class="flex items-start space-x-4 flex-1">
                            <div class="w-8 h-8 bg-gray-300 rounded-full mt-1"></div>
                            <div class="flex-1 space-y-2">
                                <div class="h-4 bg-gray-300 rounded w-48"></div>
                                <div class="h-3 bg-gray-300 rounded w-full max-w-md"></div>
                                <div class="h-3 bg-gray-300 rounded w-32"></div>
                            </div>
                        </div>
                        <div class="h-6 bg-gray-300 rounded w-20"></div>
                    </div>
                @endfor
            </div>
        </div>

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const organization = '{{ rawurlencode($decodedOrganization) }}';
            let spendingChart = null;

            // Set available years in global state
            YearState.setAvailableYears(@json($availableYears->toArray()));

            // Update current year display
            function updateCurrentYearDisplay(year) {
                const display = document.getElementById('org-current-year-display');
                if (display) {
                    display.textContent = year;
                }
            }

            // Load organization data for specific year
            function loadOrganizationData(year) {
                updateCurrentYearDisplay(year);
                loadOrganizationStats(year);
                loadOrganizationDetails(year);
            }

            // Load spending chart (this doesn't change with year selection)
            function loadSpendingChart() {
                fetch(`/ajax/organization/${organization}/spending-chart`)
                    .then(response => response.json())
                    .then(data => {
                        createSpendingChart(data);
                    })
                    .catch(error => {
                        console.error('Error loading spending chart:', error);
                        hideChartLoading();
                    });
            }

            function createSpendingChart(data) {
                const ctx = document.getElementById('spending-chart').getContext('2d');

                // Destroy existing chart if it exists
                if (spendingChart) {
                    spendingChart.destroy();
                }

                // Hide loading state
                hideChartLoading();

                spendingChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.years,
                        datasets: [{
                            label: 'Total Spending',
                            data: data.spending,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: 'white',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgb(59, 130, 246)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed.y;
                                        const contracts = data.contracts[context.dataIndex];

                                        let formattedValue;
                                        if (value >= 1000000000) {
                                            formattedValue = `$${(value / 1000000000).toFixed(1)}B`;
                                        } else if (value >= 1000000) {
                                            formattedValue = `$${(value / 1000000).toFixed(1)}M`;
                                        } else if (value >= 1000) {
                                            formattedValue = `$${(value / 1000).toFixed(1)}K`;
                                        } else {
                                            formattedValue = `$${value.toFixed(0)}`;
                                        }

                                        return [
                                            `Total Spending: ${formattedValue}`,
                                            `Contracts: ${contracts.toLocaleString()}`
                                        ];
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Year',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Total Spending',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                },
                                ticks: {
                                    callback: function(value) {
                                        if (value >= 1000000000) {
                                            return `$${(value / 1000000000).toFixed(1)}B`;
                                        } else if (value >= 1000000) {
                                            return `$${(value / 1000000).toFixed(1)}M`;
                                        } else if (value >= 1000) {
                                            return `$${(value / 1000).toFixed(1)}K`;
                                        }
                                        return `$${value}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            function hideChartLoading() {
                const loading = document.getElementById('chart-loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }

            function loadOrganizationStats(year) {
                fetch(`/ajax/organization/${organization}/stats?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        updateStatsGrid(data.stats);
                    })
                    .catch(error => {
                        console.error('Error loading organization stats:', error);
                    });
            }

            function updateStatsGrid(stats) {
                // Update Total Contracts
                const totalContractsEl = document.querySelector('[data-stat="total_contracts"]');
                if (totalContractsEl) {
                    totalContractsEl.textContent = stats.total_contracts.toLocaleString();
                }

                // Update Total Spending
                const totalSpendingEl = document.querySelector('[data-stat="total_spending"]');
                if (totalSpendingEl) {
                    const spending = stats.total_spending;
                    if (spending >= 1000000000) {
                        totalSpendingEl.textContent = `$${(spending / 1000000000).toFixed(1)}B`;
                    } else {
                        totalSpendingEl.textContent = `$${(spending / 1000000).toFixed(1)}M`;
                    }
                }

                // Update Unique Vendors
                const uniqueVendorsEl = document.querySelector('[data-stat="unique_vendors"]');
                if (uniqueVendorsEl) {
                    uniqueVendorsEl.textContent = stats.unique_vendors.toLocaleString();
                }

                // Update Average Contract Value
                const avgContractEl = document.querySelector('[data-stat="avg_contract_value"]');
                if (avgContractEl) {
                    avgContractEl.textContent = `$${Math.round(stats.avg_contract_value / 1000)}K`;
                }
            }

            function loadOrganizationDetails(year) {
                fetch(`/ajax/organization/${organization}/details?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('top-vendors').innerHTML =
                            `<div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                                <i class="fas fa-trophy text-2xl text-amber-500 mr-3"></i>
                                <h3 class="text-xl font-semibold text-gray-800">Top Vendors</h3>
                            </div>
                            ${data.html.topVendors}`;

                        document.getElementById('top-contracts').innerHTML =
                            `<div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                                <i class="fas fa-list-alt text-2xl text-indigo-500 mr-3"></i>
                                <h3 class="text-xl font-semibold text-gray-800">Largest Contracts</h3>
                                <div class="ml-auto text-sm text-gray-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Biggest individual spending items
                                </div>
                            </div>
                            ${data.html.topContracts}`;
                    })
                    .catch(error => {
                        console.error('Error loading organization details:', error);
                        document.getElementById('top-vendors').innerHTML =
                            '<div class="text-center py-8 text-red-600">Error loading vendor data</div>';
                        document.getElementById('top-contracts').innerHTML =
                            '<div class="text-center py-8 text-red-600">Error loading contract data</div>';
                    });
            }

            // Listen for year changes
            YearState.addListener(function(year) {
                loadOrganizationData(year);
            });

            // Load initial data
            const currentYear = YearState.get();
            loadOrganizationData(currentYear);

            // Load spending chart (independent of year selection)
            loadSpendingChart();
        });
    </script>
@endsection
