@extends('layouts.app')

@section('title', __('app.contracts_title'))

@section('content')
    <!-- Page Header -->
    <div class="page-header">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-4xl md:text-5xl font-bold font-heading text-neutral-900 mb-6">
                <i class="fas fa-file-contract text-primary-600 mr-4"></i>
                <span class="gradient-text">Government Contracts</span>
            </h1>
            <p class="text-xl md:text-2xl text-neutral-600 leading-relaxed max-w-3xl mx-auto">
                Search and explore the government's announced procurement contracts with detailed information.
            </p>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="text-center mb-16">
        <div class="card-featured max-w-2xl mx-auto">
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

    <!-- Contracts DataTable -->
    <div class="mb-16" id="data-table">
        <div class="text-center mb-12">
            <h2 class="section-title">
                <i class="fas fa-table text-primary-600 mr-3"></i>
                Contract Announcements Database
            </h2>
            <p class="section-subtitle mx-auto">
                Browse all government contract announcements with advanced search and filtering capabilities.
            </p>
        </div>
        <div class="card-featured">
            <x-transparency-datatable ajax-url="{{ route('contracts.data') }}"/>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let isLoading = false;

            // Set available years in global state
            YearState.setAvailableYears(@json($availableYears->toArray()));

            // Loading state management
            function showLoadingState() {
                if (isLoading) return;

                isLoading = true;

                // Show loading indicators
                document.getElementById('year-loading').classList.remove('hidden');
                document.getElementById('year-loading').classList.add('flex');
                document.getElementById('year-status-text').classList.add('hidden');
                document.getElementById('year-loading-text').classList.remove('hidden');

                // Disable year selector
                document.querySelector('.year-selector').disabled = true;
                document.querySelector('.year-selector').style.opacity = '0.7';
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
            }

            // Update current year display
            function updateCurrentYearDisplay(year) {
                const display = document.getElementById('current-year-display');
                if (display) {
                    display.textContent = year;
                }
            }

            function updateDataTableUrl(year) {
                // Update DataTable AJAX URL if the component exists
                if (window.dataTable && window.dataTable.ajax) {
                    const newUrl = `{{ route('contracts.data') }}?year=${year}`;
                    window.dataTable.ajax.url(newUrl).load();
                }
            }

            // Load contracts data for specific year
            function loadContractsData(year) {
                showLoadingState();
                
                // Update current year display
                updateCurrentYearDisplay(year);

                // Update DataTable URL
                updateDataTableUrl(year);

                // Hide loading state after a short delay
                setTimeout(hideLoadingState, 300);
            }

            // Listen for year changes
            YearState.addListener(function (year) {
                loadContractsData(year);
            });

            // Load initial data
            const currentYear = YearState.get();
            updateCurrentYearDisplay(currentYear);
            updateDataTableUrl(currentYear);
        });
    </script>
@endsection
