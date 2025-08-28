@extends('layouts.app')

@section('title', __('app.dashboard_title'))

@section('content')
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-line mr-3"></i>
            {{ __('app.dashboard_title') }}
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            {{ __('app.dashboard_subtitle') }}
        </p>
    </div>
    <!-- Government Spending Trends Chart -->
    <div class="card mb-16">
        <div class="relative" style="height: 400px;">
            <canvas id="government-spending-chart" class="w-full h-full"></canvas>

            <!-- Loading state -->
            <div id="gov-chart-loading"
                 class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                    <p class="text-sm text-gray-600">Loading government spending trends...</p>
                </div>
            </div>
        </div>
    </div>
    <!-- Year Filter - Primary Feature -->
    <div class="text-center mb-10">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto border-2 border-indigo-100">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-3xl text-indigo-600 mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">{{ __('app.filter_by_year') }}</h2>
            </div>
            <p class="text-gray-600 mb-6">{{ __('app.filter_description') }}</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <label for="year" class="text-lg font-semibold text-gray-700">{{ __('app.year_label') }}</label>
                    <div class="relative">
                        <select id="year"
                                class="year-selector px-4 py-3 text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white transition-all duration-200">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <!-- Loading spinner overlay -->
                        <div id="year-loading" class="absolute inset-0 bg-white bg-opacity-95 rounded-lg items-center justify-center hidden">
                            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-indigo-600"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500">
                <span id="year-status-text">{{ __('app.currently_viewing') }} <span class="font-semibold text-indigo-600" id="current-year-display"></span> {{ __('app.procurement_data') }}</span>
                <span id="year-loading-text" class="hidden">
                    <i class="fas fa-spinner fa-spin mr-2 text-indigo-600"></i>
                    <span class="font-semibold text-indigo-600">{{ __('app.loading_data') }}</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Organization Spending Distribution Pie Chart -->
    <div class="card mb-16">
        <div class="flex flex-col sm:flex-row sm:items-center mb-6 pb-4 border-b border-gray-200">
            <div class="flex items-center mb-4 sm:mb-0">
                <i class="fas fa-chart-pie text-2xl sm:text-3xl text-purple-600 mr-3"></i>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800">
                    {{ __('app.year_statistics') }} <span class="pie-chart-year-label">2025</span>
                </h3>
            </div>
            <div class="sm:ml-auto text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                {{ __('app.spending_breakdown') }} <span class="pie-chart-year-label">2025</span>
            </div>
        </div>
        <div class="flex flex-col lg:flex-row lg:justify-evenly gap-6">
            <div class="flex-1 max-w-full lg:max-w-md">
                <div class="relative h-64 sm:h-80 lg:h-96">
                    <canvas id="organizations-pie-chart" class="w-full h-full"></canvas>
                    <!-- Loading state -->
                    <div id="pie-chart-loading"
                         class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90">
                        <div class="text-center">
                            <div class="animate-spin rounded-full h-6 w-6 sm:h-8 sm:w-8 border-b-2 border-purple-600 mx-auto mb-2"></div>
                            <p class="text-xs sm:text-sm text-gray-600">Loading spending distribution...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="flex-1 stats-grid-container">
                <x-stats-grid :stats="$stats"/>
            </div>
        </div>
    </div>

    <!-- Vendor Leaderboards - First Row (Lazy Loaded) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8" id="vendor-leaderboards">
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 animate-pulse">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <div class="w-8 h-8 bg-gray-300 rounded mr-3"></div>
                <div class="h-6 bg-gray-300 rounded w-48"></div>
            </div>
            <div class="space-y-4">
                @for($i = 0; $i < 5; $i++)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
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

        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 animate-pulse">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <div class="w-8 h-8 bg-gray-300 rounded mr-3"></div>
                <div class="h-6 bg-gray-300 rounded w-48"></div>
            </div>
            <div class="space-y-4">
                @for($i = 0; $i < 5; $i++)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
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

    <!-- Organization Leaderboard - Second Row (Lazy Loaded) -->
    <div class="flex justify-center mb-8">
        <div class="w-full max-w-4xl" id="organization-leaderboard">
            <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 animate-pulse">
                <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                    <div class="w-8 h-8 bg-gray-300 rounded mr-3"></div>
                    <div class="h-6 bg-gray-300 rounded w-64"></div>
                </div>
                <div class="space-y-4">
                    @for($i = 0; $i < 8; $i++)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-10 h-8 bg-gray-300 rounded"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-gray-300 rounded w-48"></div>
                                    <div class="h-3 bg-gray-300 rounded w-24"></div>
                                </div>
                            </div>
                            <div class="h-6 bg-gray-300 rounded w-20"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    <!-- Public Transparency DataTable -->
    <x-transparency-datatable ajax-url="{{ route('contracts.data') }}"/>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let governmentSpendingChart = null;
            let organizationsPieChart = null;
            let isLoading = false;

            // Set available years in global state
            YearState.setAvailableYears(@json($availableYears->toArray()));

            // Loading state management
            function showLoadingState() {
                if (isLoading) return; // Prevent multiple simultaneous loading states

                isLoading = true;

                // Show loading indicators
                document.getElementById('year-loading').classList.remove('hidden');
                document.getElementById('year-loading').classList.add('flex');
                document.getElementById('year-status-text').classList.add('hidden');
                document.getElementById('year-loading-text').classList.remove('hidden');

                // Disable year selector
                document.querySelector('.year-selector').disabled = true;
                document.querySelector('.year-selector').style.opacity = '0.7';

                // Freeze scroll
                document.body.style.overflow = 'hidden';
                document.body.style.position = 'fixed';
                document.body.style.width = '100%';
                document.body.style.top = `-${window.scrollY}px`;
            }

            function hideLoadingState() {
                isLoading = false;

                // Hide loading indicators
                document.getElementById('year-loading').classList.add('hidden');
                document.getElementById('year-loading').classList.remove('flex');
                document.getElementById('year-status-text').classList.remove('hidden');
                document.getElementById('year-loading-text').classList.add('hidden');

                // Re-enable year selector
                document.querySelector('.year-selector').disabled = false;
                document.querySelector('.year-selector').style.opacity = '1';

                // Unfreeze scroll
                const scrollY = document.body.style.top;
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
                document.body.style.top = '';
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }

            // Update current year display
            function updateCurrentYearDisplay(year) {
                const displays = document.querySelectorAll('.current-year-display');
                const pieChartYearLabels = document.querySelectorAll('.pie-chart-year-label');

                displays.forEach(display => {
                    display.textContent = year;
                });

                pieChartYearLabels.forEach(label => {
                    label.textContent = year;
                });
            }

            // Load dashboard data for specific year
            function loadDashboardData(year) {
                showLoadingState();

                // Update current year display
                updateCurrentYearDisplay(year);

                // Track loading promises
                const loadingPromises = [
                    loadOrganizationsPieChart(year),
                    loadStatsGrid(year),
                    loadVendorLeaderboards(year),
                    loadOrganizationLeaderboard(year)
                ];

                // Update DataTable URL if it exists
                updateDataTableUrl(year);

                // Wait for all loading to complete
                Promise.allSettled(loadingPromises).finally(() => {
                    // Small delay to ensure smooth transition
                    setTimeout(hideLoadingState, 300);
                });
            }

            // Load government spending chart (independent of year selection)
            function loadGovernmentSpendingChart() {
                fetch(`{{ route('ajax.dashboard.government-spending-chart') }}`)
                    .then(response => response.json())
                    .then(data => {
                        createGovernmentSpendingChart(data);
                    })
                    .catch(error => {
                        console.error('Error loading government spending chart:', error);
                        hideGovChartLoading();
                    });
            }

            function createGovernmentSpendingChart(data) {
                const ctx = document.getElementById('government-spending-chart').getContext('2d');

                // Destroy existing chart if it exists
                if (governmentSpendingChart) {
                    governmentSpendingChart.destroy();
                }

                // Hide loading state
                hideGovChartLoading();

                governmentSpendingChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.years,
                        datasets: [{
                            label: 'Total Government Spending',
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
                                    label: function (context) {
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
                                    text: 'Total Government Spending',
                                    font: {
                                        size: 12,
                                        weight: 'bold'
                                    }
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                },
                                ticks: {
                                    callback: function (value) {
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

            function hideGovChartLoading() {
                const loading = document.getElementById('gov-chart-loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }

            // Load organizations pie chart for specific year
            function loadOrganizationsPieChart(year) {
                return fetch(`{{ route('ajax.dashboard.organizations-pie-chart') }}?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        createOrganizationsPieChart(data);
                    })
                    .catch(error => {
                        console.error('Error loading organizations pie chart:', error);
                        hidePieChartLoading();
                    });
            }

            function createOrganizationsPieChart(data) {
                const ctx = document.getElementById('organizations-pie-chart').getContext('2d');

                // Destroy existing chart if it exists
                if (organizationsPieChart) {
                    organizationsPieChart.destroy();
                }

                // Hide loading state
                hidePieChartLoading();

                organizationsPieChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.data,
                            backgroundColor: data.colors,
                            borderColor: 'white',
                            borderWidth: 2,
                            hoverBorderWidth: 3,
                            hoverBorderColor: 'white'
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
                                    font: {
                                        size: 12,
                                        weight: '500'
                                    },
                                    color: '#374151',
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(17, 24, 39, 0.95)',
                                titleColor: 'white',
                                bodyColor: 'white',
                                borderColor: 'rgba(156, 163, 175, 0.3)',
                                borderWidth: 1,
                                callbacks: {
                                    label: function (context) {
                                        const value = context.parsed;
                                        const total = data.total;
                                        const percentage = ((value / total) * 100).toFixed(1);

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
                                            `${context.label}`,
                                            `Amount: ${formattedValue}`,
                                            `Percentage: ${percentage}%`
                                        ];
                                    }
                                }
                            }
                        },
                        interaction: {
                            intersect: false,
                            mode: 'point'
                        }
                    }
                });
            }

            function hidePieChartLoading() {
                const loading = document.getElementById('pie-chart-loading');
                if (loading) {
                    loading.style.display = 'none';
                }
            }

            function loadStatsGrid(year) {
                return fetch(`{{ route('ajax.dashboard.stats-grid') }}?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        const statsContainer = document.querySelector('.stats-grid-container');
                        if (statsContainer) {
                            statsContainer.innerHTML = data.html;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading stats grid:', error);
                    });
            }

            function loadVendorLeaderboards(year) {
                return fetch(`{{ route('ajax.dashboard.vendor-leaderboards') }}?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('vendor-leaderboards').innerHTML =
                            `<div>${data.html.byCount}</div><div>${data.html.byValue}</div>`;
                    })
                    .catch(error => {
                        console.error('Error loading vendor leaderboards:', error);
                        document.getElementById('vendor-leaderboards').innerHTML =
                            '<div class="text-center py-8 text-red-600">Error loading vendor data</div>';
                    });
            }

            function loadOrganizationLeaderboard(year) {
                return fetch(`{{ route('ajax.dashboard.organization-leaderboard') }}?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('organization-leaderboard').innerHTML = data.html;
                    })
                    .catch(error => {
                        console.error('Error loading organization leaderboard:', error);
                        document.getElementById('organization-leaderboard').innerHTML =
                            '<div class="text-center py-8 text-red-600">Error loading organization data</div>';
                    });
            }

            function updateDataTableUrl(year) {
                // Update DataTable AJAX URL if the component exists
                if (window.dataTable && window.dataTable.ajax) {
                    const newUrl = `{{ route('contracts.data') }}?year=${year}`;
                    window.dataTable.ajax.url(newUrl).load();
                }
            }

            // Listen for year changes
            YearState.addListener(function (year) {
                loadDashboardData(year);
            });

            // Load initial data
            const currentYear = YearState.get();
            loadDashboardData(currentYear);

            // Load government spending chart (independent of year selection)
            loadGovernmentSpendingChart();
        });
    </script>
@endsection
