@extends('layouts.app')

@section('title', $decodedVendor . ' - ' . $decodedOrganization . ' - Contract Analytics')

@section('content')
    <!-- Back Button -->
    <div class="mb-6">
        <a href="{{ url('/') }}" class="btn-primary inline-flex items-center mr-4">
            <i class="fas fa-arrow-left mr-2"></i>
            {{ __('app.back_to_dashboard') }}
        </a>
        <a href="{{ route('vendor.detail', ['vendor' => rawurlencode($decodedVendor)]) }}" class="btn-secondary inline-flex items-center mr-4">
            <i class="fas fa-building mr-2"></i>
            {{ __('app.back_to_vendor') }}
        </a>
        <a href="{{ route('organization.detail', ['organization' => rawurlencode($decodedOrganization)]) }}" class="btn-secondary inline-flex items-center">
            <i class="fas fa-building-columns mr-2"></i>
            {{ __('app.back_to_organization') }}
        </a>
    </div>

    <!-- Page Header -->
    <div class="text-center mb-10 pb-8 border-b border-gray-200">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-handshake mr-3"></i>
            {{ $decodedVendor }}
            <span class="text-indigo-600 mx-3">×</span>
            {{ $decodedOrganization }}
        </h1>
        <p class="text-xl text-gray-600">
            Contract analytics and insights for this vendor-organization partnership
        </p>
    </div>

    <!-- Historical Overview & Spending Chart -->
    <x-historical-overview-with-chart
        :title="__('app.historical_partnership_totals')"
        :description="'Complete contract history and spending trends for ' . $decodedVendor . ' & ' . $decodedOrganization"
        :chartTitle="__('app.spending_over_time')"
        chartId="spending-chart"
        chartLoadingId="chart-loading"
        totalValueId="total-inflation-adjusted-value"
        totalCountId="total-contracts-count" />

    <!-- Year Filter -->
    <div class="text-center mb-10">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto border-2 border-indigo-100">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-3xl text-indigo-600 mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">{{ __('app.filter_by_year') }}</h2>
            </div>
            <p class="text-gray-600 mb-6">View contract data for {{ $decodedVendor }} and {{ $decodedOrganization }} for a specific year</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <label for="vendor-org-year" class="text-lg font-semibold text-gray-700">Year:</label>
                    <select id="vendor-org-year" class="year-selector px-4 py-3 text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition-all duration-200">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                Currently viewing: <span class="font-semibold text-indigo-600" id="vendor-org-current-year-display"></span> contract data
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <!-- Total Contracts -->
        <div class="stats-card">
            <div class="text-4xl text-blue-500 mb-4">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="text-3xl font-bold text-blue-600 mb-2" data-stat="total_contracts">
                {{ number_format($vendorOrgStats['total_contracts']) }}
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
                @currency($vendorOrgStats['total_value'])
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.total_value') }}
            </div>
        </div>

        <!-- Average Contract -->
        <div class="stats-card">
            <div class="text-4xl text-amber-500 mb-4">
                <i class="fas fa-calculator"></i>
            </div>
            <div class="text-3xl font-bold text-amber-600 mb-2" data-stat="avg_contract_value">
                @currencyAvg($vendorOrgStats['avg_contract_value'])
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                {{ __('app.avg_contract_value') }}
            </div>
        </div>
    </div>

    <!-- Vendor-Organization Contracts DataTable -->
    <div class="mb-10" id="vendor-org-contracts-section">
        <x-vendor-organization-contracts-datatable
            :ajaxUrl="route('vendor.organization.contracts.data', ['vendor' => rawurlencode($decodedVendor), 'organization' => rawurlencode($decodedOrganization)])"
            :vendorName="$decodedVendor"
            :organizationName="$decodedOrganization" />
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const vendor = '{{ rawurlencode($decodedVendor) }}';
            const organization = '{{ rawurlencode($decodedOrganization) }}';
            let spendingChart = null;

            // Set available years in global state
            const availableYears = @json($availableYears->toArray());
            YearState.setAvailableYears(availableYears);

            // Ensure we have a valid year selected
            const currentYear = YearState.get();
            if (!availableYears.includes(parseInt(currentYear))) {
                // If current year is not available, use the first available year
                YearState.set(availableYears[0]);
            }

            // Update current year display
            function updateCurrentYearDisplay(year) {
                const display = document.getElementById('vendor-org-current-year-display');
                if (display) {
                    display.textContent = year;
                }
            }

            // Load vendor-organization data for specific year
            function loadVendorOrganizationData(year) {
                updateCurrentYearDisplay(year);
                loadVendorOrganizationStats(year);
            }

            // Load spending chart (this doesn't change with year selection)
            function loadSpendingChart() {
                fetch(`/ajax/vendor/${vendor}/organization/${organization}/spending-chart`)
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
                            label: '{{ __('app.total_spending') }}',
                            data: data.spending,
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
                                            `{{ __('app.total_spending') }}: ${formattedValue}`,
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
                                    text: '{{ __('app.total_spending') }}',
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

            function loadVendorOrganizationStats(year) {
                fetch(`/ajax/vendor/${vendor}/organization/${organization}/stats?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        updateStatsGrid(data.stats);
                    })
                    .catch(error => {
                        console.error('Error loading vendor-organization stats:', error);
                    });
            }

            function updateStatsGrid(stats) {
                // Update Total Contracts
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

            // Listen for year changes
            YearState.addListener(function(year) {
                loadVendorOrganizationData(year);
                // Refresh the vendor-organization contracts table if it exists
                if (typeof window.vendorOrganizationContractsTable !== 'undefined') {
                    window.vendorOrganizationContractsTable.ajax.reload();
                }
            });

            // Load historical totals (all years combined)
            function loadHistoricalTotals() {
                fetch(`/ajax/vendor/${vendor}/organization/${organization}/historical-totals`)
                    .then(response => response.json())
                    .then(data => {
                        updateHistoricalTotals(data);
                    })
                    .catch(error => {
                        console.error('Error loading historical totals:', error);
                        // Show error state
                        document.getElementById('total-inflation-adjusted-value').innerHTML =
                            '<span class="text-red-500">Error loading data</span>';
                        document.getElementById('total-contracts-count').innerHTML =
                            '<span class="text-red-500">Error loading data</span>';
                    });
            }

            function updateHistoricalTotals(data) {
                // Update inflation-adjusted total value
                const totalValueEl = document.getElementById('total-inflation-adjusted-value');
                if (totalValueEl && data.inflation_adjusted_total) {
                    totalValueEl.textContent = data.inflation_adjusted_total;
                }

                // Update total contracts count
                const totalContractsEl = document.getElementById('total-contracts-count');
                if (totalContractsEl && data.total_contracts) {
                    totalContractsEl.textContent = data.total_contracts.toLocaleString();
                }
            }

            // Load initial data
            const finalYear = YearState.get();
            loadVendorOrganizationData(finalYear);

            // Load spending chart and historical totals (independent of year selection)
            loadSpendingChart();
            loadHistoricalTotals();
        });
    </script>
@endsection
