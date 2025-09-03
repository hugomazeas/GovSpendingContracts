@props([
    'title',
    'description',
    'chartTitle',
    'chartId' => 'spending-chart',
    'chartLoadingId' => 'chart-loading',
    'totalValueId' => 'total-inflation-adjusted-value',
    'totalCountId' => 'total-contracts-count',
    'totalValueLabel' => null,
    'totalCountLabel' => null,
    'totalValueDescription' => null,
    'totalCountDescription' => null
])

<!-- Historical Overview & Chart -->
<div class="card mb-16">
    <!-- Header -->
    <div class="text-center mb-8 pb-4 border-b border-gray-200">
        <div class="flex items-center justify-center mb-4">
            <i class="fas fa-chart-area text-4xl text-green-500 mr-4"></i>
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $title }}</h2>
                <p class="text-lg text-gray-600 mt-2">{{ $description }}</p>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Left Side: Stacked Metrics (2/5 width) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Total Inflation-Adjusted Value -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-2xl p-6 border-2 border-green-200">
                <div class="flex items-center">
                    <div class="text-4xl text-green-600 mr-4">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-2xl font-bold text-green-700 mb-1" id="{{ $totalValueId }}">
                            <div class="animate-pulse bg-green-200 h-8 w-40 rounded"></div>
                        </div>
                        <div class="text-green-600 font-semibold uppercase tracking-wide text-sm">
                            {{ $totalValueLabel ?? __('app.total_inflation_adjusted_value') }}
                        </div>
                        <div class="text-xs text-green-600 mt-1 opacity-75">
                            {{ $totalValueDescription ?? __('app.adjusted_to_current_dollars') }}
                        </div>
                    </div>
                </div>
            </div>
            <!-- Total Contracts -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-6 border-2 border-blue-200">
                <div class="flex items-center">
                    <div class="text-4xl text-blue-600 mr-4">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-2xl font-bold text-blue-700 mb-1" id="{{ $totalCountId }}">
                            <div class="animate-pulse bg-blue-200 h-8 w-24 rounded"></div>
                        </div>
                        <div class="text-blue-600 font-semibold uppercase tracking-wide text-sm">
                            {{ $totalCountLabel ?? __('app.total_contracts_all_years') }}
                        </div>
                        <div class="text-xs text-blue-600 mt-1 opacity-75">
                            {{ $totalCountDescription ?? __('app.across_all_available_years') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side: Chart (3/5 width) -->
        <div class="lg:col-span-3">
            <div class="flex items-center mb-4">
                <i class="fas fa-chart-line text-2xl text-blue-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">{{ $chartTitle }}</h3>
            </div>
            <div class="relative" style="height: 400px;">
                <canvas id="{{ $chartId }}" class="w-full h-full"></canvas>

                <!-- Loading state -->
                <div id="{{ $chartLoadingId }}" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-90">
                    <div class="text-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto mb-2"></div>
                        <p class="text-sm text-gray-600">Loading chart data...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
