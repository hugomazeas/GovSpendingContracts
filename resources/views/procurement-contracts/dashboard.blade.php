@extends('layouts.app')

@section('title', __('app.dashboard_title'))

@section('content')
    <!-- Hero Section -->
    <div class="page-header z-4 animate-slide-up">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-5xl md:text-6xl font-bold font-heading text-neutral-900 mb-6">
                <span class="gradient-text">Government</span><br>
                <span class="text-neutral-900">Contracts</span>
                <div class="inline-flex items-center mt-2">
                    <span class="gradient-text mr-4">Canada</span>
                    <div
                        class="w-8 h-5 rounded-sm shadow-soft bg-gradient-to-r from-red-500 via-white to-red-500 relative overflow-hidden">
                        <div class="absolute inset-y-0 left-0 w-1/3 bg-red-500"></div>
                        <div class="absolute inset-y-0 right-0 w-1/3 bg-red-500"></div>
                        <div class="absolute inset-y-0 left-1/3 right-1/3 bg-white flex items-center justify-center">
                            <div class="w-2 h-2 text-red-500 text-xs flex items-center justify-center">
                                <i class="fas fa-maple-leaf text-xs"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </h1>
            <p class="text-xl md:text-2xl text-neutral-600 leading-relaxed max-w-3xl mx-auto">
                {{ __('app.dashboard_subtitle') }}
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="#data-table" class="btn-primary inline-flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i>
                    Explore Contracts
                </a>
                <a href="{{ route('organizations.index') }}"
                   class="btn-secondary inline-flex items-center justify-center">
                    <i class="fas fa-building-columns mr-2"></i>
                    View Organizations
                </a>
            </div>
        </div>
    </div>
    <!-- Government Spending Trends Chart -->
    <div class="relative bg-gradient-to-br from-neutral-50 to-white rounded-2xl p-6" style="height: 450px;">
        <canvas id="government-spending-chart" class="w-full h-full"></canvas>

        <!-- Loading state -->
        <div id="gov-chart-loading"
             class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-95 rounded-2xl">
            <div class="text-center">
                <div
                    class="animate-spin rounded-full h-10 w-10 border-4 border-primary-200 border-t-primary-600 mx-auto mb-4"></div>
                <p class="text-neutral-600 font-medium">Loading spending trends...</p>
            </div>
        </div>
    </div>
    <div class="mb-8 text-center">
        <h2 class="section-title">
            <i class="fas fa-chart-line text-primary-600 mr-3"></i>
            Contract Award Trends
        </h2>
        <p class="section-subtitle mx-auto">
            View announced government contract values across multiple years.
        </p>
    </div>
    <!-- Year Filter - Primary Feature -->
    <div class="text-center mb-16">
        <div class="card-featured max-w-3xl mx-auto">
            <div class="flex items-center justify-center mb-6">
                <div
                    class="flex items-center justify-center w-12 h-12 bg-gradient-to-r from-accent-500 to-accent-600 rounded-xl shadow-medium mr-4">
                    <i class="fas fa-calendar-alt text-xl text-white"></i>
                </div>
                <h2 class="section-title mb-0">{{ __('app.filter_by_year') }}</h2>
            </div>
            <p class="text-neutral-600 mb-8 text-lg">{{ __('app.filter_description') }}</p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
                <div class="flex items-center gap-4">
                    <label for="year"
                           class="text-lg font-semibold text-neutral-700 whitespace-nowrap">{{ __('app.year_label') }}</label>
                    <div class="relative">
                        <select id="year"
                                class="year-selector px-6 py-4 text-xl font-bold border-2 border-neutral-200 rounded-xl focus:ring-4 focus:ring-primary-200 focus:border-primary-500 bg-white transition-all duration-300 shadow-soft hover:shadow-medium min-w-[120px]">
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <!-- Loading spinner overlay -->
                        <div id="year-loading"
                             class="absolute inset-0 bg-white bg-opacity-95 rounded-xl items-center justify-center hidden">
                            <div
                                class="animate-spin rounded-full h-6 w-6 border-4 border-primary-200 border-t-primary-600"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-4 bg-gradient-to-r from-primary-50 to-accent-50 rounded-xl">
                <span id="year-status-text" class="text-neutral-700 font-medium">
                    {{ __('app.currently_viewing') }} <span class="font-bold text-primary-700"
                                                            id="current-year-display"></span> {{ __('app.procurement_data') }}
                </span>
                <span id="year-loading-text" class="hidden text-primary-700 font-medium">
                    <i class="fas fa-spinner fa-spin mr-2"></i>
                    {{ __('app.loading_data') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Organization Spending Distribution Section -->
    <div class="card-featured mb-20">
        <div class="mb-8 text-center">
            <h2 class="section-title">
                <i class="fas fa-chart-pie text-secondary-600 mr-3"></i>
                {{ __('app.year_statistics') }} <span class="pie-chart-year-label gradient-text font-bold">2025</span>
            </h2>
            <p class="section-subtitle mx-auto">
                Contract announcements for <span class="pie-chart-year-label">2025</span> - breakdown by organization
                and key metrics.
            </p>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-12 items-center">
            <!-- Pie Chart -->
            <div class="relative">
                <div class="bg-gradient-to-br from-neutral-50 to-white rounded-2xl p-8 shadow-soft">
                    <div class="relative h-80 lg:h-96">
                        <canvas id="organizations-pie-chart" class="w-full h-full"></canvas>
                        <!-- Loading state -->
                        <div id="pie-chart-loading"
                             class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-95 rounded-2xl">
                            <div class="text-center">
                                <div
                                    class="animate-spin rounded-full h-10 w-10 border-4 border-secondary-200 border-t-secondary-600 mx-auto mb-4"></div>
                                <p class="text-neutral-600 font-medium">Loading spending distribution...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Grid -->
            <div class="stats-grid-container">
                <x-stats-grid :stats="$stats"/>
            </div>
        </div>
    </div>

    <!-- Key Insights Section -->
    <div class="mb-16">
        <div class="text-center mb-12">
            <h2 class="section-title">
                <i class="fas fa-trophy text-yellow-500 mr-3"></i>
                Top Contract Recipients & Organizations
            </h2>
            <p class="section-subtitle mx-auto">
                See which vendors and organizations receive the most announced government contracts by volume and value.
            </p>
        </div>

        <!-- Vendor Leaderboards -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8 mb-12" id="vendor-leaderboards">
            <div class="card animate-pulse">
                <div class="flex items-center mb-6 pb-4 border-b border-neutral-200">
                    <div class="w-10 h-10 bg-neutral-300 rounded-xl mr-4"></div>
                    <div class="h-6 bg-neutral-300 rounded w-48"></div>
                </div>
                <div class="space-y-4">
                    @for($i = 0; $i < 5; $i++)
                        <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-neutral-300 rounded-full flex-shrink-0"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-neutral-300 rounded w-32"></div>
                                    <div class="h-3 bg-neutral-300 rounded w-20"></div>
                                </div>
                            </div>
                            <div class="h-6 bg-neutral-300 rounded w-16 flex-shrink-0"></div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="card animate-pulse">
                <div class="flex items-center mb-6 pb-4 border-b border-neutral-200">
                    <div class="w-10 h-10 bg-neutral-300 rounded-xl mr-4"></div>
                    <div class="h-6 bg-neutral-300 rounded w-48"></div>
                </div>
                <div class="space-y-4">
                    @for($i = 0; $i < 5; $i++)
                        <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl">
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-neutral-300 rounded-full flex-shrink-0"></div>
                                <div class="space-y-2">
                                    <div class="h-4 bg-neutral-300 rounded w-32"></div>
                                    <div class="h-3 bg-neutral-300 rounded w-20"></div>
                                </div>
                            </div>
                            <div class="h-6 bg-neutral-300 rounded w-16 flex-shrink-0"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Organization Leaderboard -->
        <div class="flex justify-center">
            <div class="w-full max-w-5xl" id="organization-leaderboard">
                <div class="card animate-pulse">
                    <div class="flex items-center mb-6 pb-4 border-b border-neutral-200">
                        <div class="w-10 h-10 bg-neutral-300 rounded-xl mr-4"></div>
                        <div class="h-6 bg-neutral-300 rounded w-64"></div>
                    </div>
                    <div class="space-y-4">
                        @for($i = 0; $i < 8; $i++)
                            <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-8 bg-neutral-300 rounded flex-shrink-0"></div>
                                    <div class="space-y-2">
                                        <div class="h-4 bg-neutral-300 rounded w-48"></div>
                                        <div class="h-3 bg-neutral-300 rounded w-24"></div>
                                    </div>
                                </div>
                                <div class="h-6 bg-neutral-300 rounded w-20 flex-shrink-0"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Public Transparency DataTable -->
    <div class="mb-16" id="data-table">
        <div class="text-center mb-12">
            <h2 class="section-title">
                <i class="fas fa-table text-primary-600 mr-3"></i>
                Contract Announcements Database
            </h2>
            <p class="section-subtitle mx-auto">
                Search and explore the government's announced procurement contracts with detailed information.
            </p>
        </div>
        <div class="card-featured">
            <x-transparency-datatable ajax-url="{{ route('contracts.data') }}"/>
        </div>
    </div>

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
                            borderWidth: 5,
                            fill: true,
                            tension: 0.6,
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
                                    display: false,
                                },
                                grid: {
                                    color: 'rgba(156, 163, 175, 0.2)'
                                },
                                ticks: {
                                    font: {
                                        size: 23,
                                        weight: 'bold'
                                    }
                                }
                            },
                            y: {
                                title: {
                                    display: false,

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
                                    },
                                    font: {
                                        size: 22,
                                        weight: 'bold'
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
