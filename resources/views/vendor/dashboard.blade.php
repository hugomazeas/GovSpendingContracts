@extends('layouts.app')

@section('title', $decodedVendor . ' - Vendor Analytics')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ url('/') }}" class="btn-primary inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>
            {{ __('app.back_to_dashboard') }}
        </a>
    </div>

    <!-- Page Header -->
    <div class="text-center mb-10 pb-8 border-b border-gray-200">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-building mr-3"></i>
            {{ $decodedVendor }}
        </h1>
        <p class="text-xl text-gray-600">
            Vendor performance analytics and contract insights
        </p>
    </div>

    <!-- Historical Vendor Overview & Revenue Chart -->
    <x-historical-overview-with-chart
        :title="__('app.historical_vendor_totals')"
        :description="'Complete government contract history and revenue trends for ' . $decodedVendor"
        :chartTitle="__('app.revenue_of_contracts')"
        chartId="revenue-chart"
        chartLoadingId="chart-loading"
        totalValueId="vendor-total-inflation-adjusted-value"
        totalCountId="vendor-total-contracts-count" />

    <!-- Year Filter -->
    <div class="text-center mb-10">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto border-2 border-indigo-100">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-3xl text-indigo-600 mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">{{ __('app.filter_by_year') }}</h2>
            </div>
            <p class="text-gray-600 mb-6">View {{ $decodedVendor }}'s contract data for a specific year</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <label for="vendor-year" class="text-lg font-semibold text-gray-700">Year:</label>
                    <select id="vendor-year" class="year-selector px-4 py-3 text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition-all duration-200">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                Currently viewing: <span class="font-semibold text-indigo-600" id="vendor-current-year-display"></span> contract data
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- {{ __('app.total_contracts') }} -->
        <div class="stats-card">
            <div class="text-4xl text-blue-500 mb-4">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="text-3xl font-bold text-blue-600 mb-2" data-stat="total_contracts">
                {{ number_format($vendorStats['total_contracts']) }}
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.total_contracts') }}
            </div>
        </div>

        <!-- Total Value -->
        <div class="stats-card">
            <div class="text-4xl text-green-500 mb-4">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="text-3xl font-bold mb-2" data-stat="total_value">
                @currency($vendorStats['total_value'])
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.total_value') }}
            </div>
        </div>

        <!-- {{ __('app.minister_clients') }} -->
        <div class="stats-card">
            <div class="text-4xl text-purple-500 mb-4">
                <i class="fas fa-building-columns"></i>
            </div>
            <div class="text-3xl font-bold text-purple-600 mb-2" data-stat="minister_clients">
                {{ number_format($vendorStats['minister_clients']) }}
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.minister_clients') }}
            </div>
        </div>

        <!-- Average Contract -->
        <div class="stats-card">
            <div class="text-4xl text-amber-500 mb-4">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="text-3xl font-bold text-amber-600 mb-2" data-stat="avg_contract_value">
                @currencyAvg($vendorStats['avg_contract_value'])
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.avg_contract_value') }}
            </div>
        </div>
    </div>

    <!-- {{ __('app.minister_distribution') }} and Leaderboard -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- {{ __('app.minister_distribution') }} Pie Chart -->
        <div class="card">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-chart-pie text-2xl text-purple-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">{{ __('app.minister_distribution') }}</h3>
            </div>
            <div class="relative" style="height: 300px;">
                <canvas id="minister-pie-chart" class="w-full h-full"></canvas>

                <!-- Loading state -->
                <div id="pie-chart-loading" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                        <p class="text-sm text-gray-600">Loading minister data...</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- {{ __('app.best_selling_ministers') }} Leaderboard -->
        <div class="card" id="minister-leaderboard">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-trophy text-2xl text-amber-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">{{ __('app.best_selling_ministers') }}</h3>
            </div>

            <!-- Loading skeleton -->
            <div class="space-y-4 animate-pulse">
                @for($i = 0; $i < 8; $i++)
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
    </div>

    <!-- Vendor Contracts DataTable -->
    <div class="mb-10" id="vendor-contracts-section">
        <x-vendor-contracts-datatable
            :ajaxUrl="route('vendor.contracts.data', ['vendor' => rawurlencode($decodedVendor)])"
            :vendorName="$decodedVendor" />
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vendor = '{{ rawurlencode($decodedVendor) }}';
            let revenueChart = null;
            let ministerPieChart = null;

            // Set available years in global state
            YearState.setAvailableYears(@json($availableYears->toArray()));

            // Update current year display
            function updateCurrentYearDisplay(year) {
                const display = document.getElementById('vendor-current-year-display');
                if (display) {
                    display.textContent = year;
                }
            }

            // Load vendor data for specific year
            function loadVendorData(year) {
                updateCurrentYearDisplay(year);
                loadVendorStats(year);
                loadMinisterLeaderboard(year);
                loadMinisterPieChart(year);
            }

            // Load revenue chart (this doesn't change with year selection)
            function loadRevenueChart() {
                fetch(`/ajax/vendor/${vendor}/spending-chart`)
                    .then(response => response.json())
                    .then(data => {
                        createRevenueChart(data);
                    })
                    .catch(error => {
                        console.error('Error loading revenue chart:', error);
                        hideChartLoading();
                    });
            }

            function createRevenueChart(data) {
                const ctx = document.getElementById('revenue-chart').getContext('2d');

                // Destroy existing chart if it exists
                if (revenueChart) {
                    revenueChart.destroy();
                }

                // Hide loading state
                hideChartLoading();

                revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.years,
                        datasets: [{
                            label: '{{ __('app.total_revenue') }}',
                            data: data.revenue,
                            borderColor: 'rgb(34, 197, 94)',
                            backgroundColor: 'rgba(34, 197, 94, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: 'rgb(34, 197, 94)',
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
                                borderColor: 'rgb(34, 197, 94)',
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
                                            `{{ __('app.total_revenue') }}: ${formattedValue}`,
                                            `{{ __('app.contracts') }}: ${contracts.toLocaleString()}`
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
                                    text: '{{ __('app.total_revenue') }}',
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

            function hidePieChartLoading() {
                const loading = document.getElementById('pie-chart-loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }

            function loadVendorStats(year) {
                fetch(`/ajax/vendor/${vendor}/stats?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        updateStatsGrid(data.stats);
                    })
                    .catch(error => {
                        console.error('Error loading vendor stats:', error);
                    });
            }

            function updateStatsGrid(stats) {
                // Update {{ __('app.total_contracts') }}
                const totalContractsEl = document.querySelector('[data-stat="total_contracts"]');
                if (totalContractsEl) {
                    totalContractsEl.textContent = stats.total_contracts.toLocaleString();
                }

                // Update Total Value
                const totalValueEl = document.querySelector('[data-stat="total_value"]');
                if (totalValueEl) {
                    const value = stats.total_value;
                    let formattedValue;
                    if (value >= 1000000000) {
                        formattedValue = `$${(value / 1000000000).toFixed(1)}B`;
                    } else if (value >= 1000000) {
                        formattedValue = `$${(value / 1000000).toFixed(1)}M`;
                    } else if (value >= 1000) {
                        formattedValue = `$${(value / 1000).toFixed(1)}K`;
                    } else {
                        formattedValue = `$${Math.round(value)}`;
                    }
                    totalValueEl.textContent = formattedValue;
                }

                // Update {{ __('app.minister_clients') }}
                const ministerClientsEl = document.querySelector('[data-stat="minister_clients"]');
                if (ministerClientsEl) {
                    ministerClientsEl.textContent = stats.minister_clients.toLocaleString();
                }

                // Update Average Contract Value
                const avgContractEl = document.querySelector('[data-stat="avg_contract_value"]');
                if (avgContractEl) {
                    const value = stats.avg_contract_value;
                    let formattedValue;
                    if (value >= 1000000) {
                        formattedValue = `$${(value / 1000000).toFixed(1)}M`;
                    } else if (value >= 1000) {
                        formattedValue = `$${Math.round(value / 1000)}K`;
                    } else {
                        formattedValue = `$${Math.round(value)}`;
                    }
                    avgContractEl.textContent = formattedValue;
                }
            }

            function loadMinisterLeaderboard(year) {
                fetch(`/ajax/vendor/${vendor}/minister-leaderboard?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('minister-leaderboard').innerHTML =
                            `<div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                                <i class="fas fa-trophy text-2xl text-amber-500 mr-3"></i>
                                <h3 class="text-xl font-semibold text-gray-800">{{ __('app.best_selling_ministers') }}</h3>
                            </div>
                            ${data.html}`;
                    })
                    .catch(error => {
                        console.error('Error loading minister leaderboard:', error);
                        document.getElementById('minister-leaderboard').innerHTML =
                            '<div class="text-center py-8 text-red-600">Error loading minister data</div>';
                    });
            }

            function loadMinisterPieChart(year) {
                fetch(`/ajax/vendor/${vendor}/minister-leaderboard?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        createMinisterPieChart(data.chartData);
                    })
                    .catch(error => {
                        console.error('Error loading minister pie chart data:', error);
                        hidePieChartLoading();
                    });
            }

            function createMinisterPieChart(chartData) {
                const ctx = document.getElementById('minister-pie-chart').getContext('2d');

                // Destroy existing chart if it exists
                if (ministerPieChart) {
                    ministerPieChart.destroy();
                }

                // Hide loading state
                hidePieChartLoading();

                const colors = [
                    '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6',
                    '#06B6D4', '#84CC16', '#F97316', '#EC4899', '#6366F1'
                ];

                ministerPieChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.values,
                            backgroundColor: colors.slice(0, chartData.labels.length),
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 20,
                                    usePointStyle: true,
                                    font: {
                                        size: 11
                                    },
                                    generateLabels: function(chart) {
                                        const dataset = chart.data.datasets[0];
                                        return chart.data.labels.map((label, index) => {
                                            const value = dataset.data[index];
                                            let formattedValue;
                                            if (value >= 1000000) {
                                                formattedValue = `$${(value / 1000000).toFixed(1)}M`;
                                            } else if (value >= 1000) {
                                                formattedValue = `$${(value / 1000).toFixed(0)}K`;
                                            } else {
                                                formattedValue = `$${value.toFixed(0)}`;
                                            }
                                            return {
                                                text: `${label}: ${formattedValue}`,
                                                fillStyle: dataset.backgroundColor[index],
                                                strokeStyle: dataset.borderColor,
                                                lineWidth: dataset.borderWidth
                                            };
                                        });
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const value = context.parsed;
                                        let formattedValue;
                                        if (value >= 1000000) {
                                            formattedValue = `$${(value / 1000000).toFixed(1)}M`;
                                        } else if (value >= 1000) {
                                            formattedValue = `$${(value / 1000).toFixed(0)}K`;
                                        } else {
                                            formattedValue = `$${value.toFixed(0)}`;
                                        }
                                        return `${context.label}: ${formattedValue}`;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Listen for year changes
            YearState.addListener(function(year) {
                loadVendorData(year);
                // Refresh the vendor contracts table if it exists
                if (typeof window.vendorContractsTable !== 'undefined') {
                    window.vendorContractsTable.ajax.reload();
                }
            });

            // Load initial data
            const currentYear = YearState.get();
            loadVendorData(currentYear);

            // Load vendor historical totals (all years combined)
            function loadVendorHistoricalTotals() {
                fetch(`/ajax/vendor/${vendor}/historical-totals`)
                    .then(response => response.json())
                    .then(data => {
                        updateVendorHistoricalTotals(data);
                    })
                    .catch(error => {
                        console.error('Error loading vendor historical totals:', error);
                        // Show error state
                        document.getElementById('vendor-total-inflation-adjusted-value').innerHTML = 
                            '<span class="text-red-500">Error loading data</span>';
                        document.getElementById('vendor-total-contracts-count').innerHTML = 
                            '<span class="text-red-500">Error loading data</span>';
                    });
            }

            function updateVendorHistoricalTotals(data) {
                // Update inflation-adjusted total value
                const totalValueEl = document.getElementById('vendor-total-inflation-adjusted-value');
                if (totalValueEl && data.inflation_adjusted_total) {
                    totalValueEl.textContent = data.inflation_adjusted_total;
                }

                // Update total contracts count
                const totalContractsEl = document.getElementById('vendor-total-contracts-count');
                if (totalContractsEl && data.total_contracts) {
                    totalContractsEl.textContent = data.total_contracts.toLocaleString();
                }
            }

            // Load revenue chart and historical totals (independent of year selection)
            loadRevenueChart();
            loadVendorHistoricalTotals();
        });
    </script>
@endsection
