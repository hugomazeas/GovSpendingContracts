@extends('layouts.app')

@section('title', 'Government Procurement Dashboard')

@section('content')
    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            <i class="fas fa-chart-line mr-3"></i>
            Government Procurement Dashboard
        </h1>
        <p class="text-xl text-gray-600 max-w-3xl mx-auto">
            Comprehensive overview of government procurement contracts and vendor performance. 
            Transparency in how your tax dollars are spent.
        </p>
    </div>

    <!-- Year Filter - Primary Feature -->
    <div class="text-center mb-10">
        <div class="bg-white rounded-xl shadow-lg p-6 max-w-2xl mx-auto border-2 border-indigo-100">
            <div class="flex items-center justify-center mb-4">
                <i class="fas fa-calendar-alt text-3xl text-indigo-600 mr-3"></i>
                <h2 class="text-2xl font-bold text-gray-800">Filter by Year</h2>
            </div>
            <p class="text-gray-600 mb-6">View procurement data for a specific year to ensure accurate, inflation-adjusted analysis</p>
            
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <div class="flex items-center gap-3">
                    <label for="year" class="text-lg font-semibold text-gray-700">Year:</label>
                    <select name="year" id="year" class="px-4 py-3 text-xl font-bold border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 bg-white">
                        @foreach($availableYears as $year)
                            <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-search mr-2"></i>
                    View {{ $selectedYear }} Data
                </button>
            </form>
            
            <div class="mt-4 text-sm text-gray-500">
                Currently viewing: <span class="font-semibold text-indigo-600">{{ $selectedYear }}</span> procurement data
            </div>
        </div>
    </div>

    <!-- Statistics Grid -->
    <x-stats-grid :stats="$stats" />
    
    <!-- Vendor Leaderboards - First Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <x-vendors-leaderboard 
            :vendors="$topVendorsByCount" 
            title="Top Vendors by Contract Count" 
            icon="fas fa-trophy"
            metric="contracts" />
            
        <x-vendors-leaderboard 
            :vendors="$topVendorsByValue" 
            title="Top Vendors by Total Value" 
            icon="fas fa-dollar-sign"
            metric="value" />
    </div>
    
    <!-- Organization Leaderboard - Second Row -->
    <div class="flex justify-center mb-8">
        <div class="w-full max-w-4xl">
            <x-organizations-leaderboard :organizations="$topOrganizationsBySpending" />
        </div>
    </div>
    
    <!-- Public Transparency DataTable -->
    <x-transparency-datatable ajax-url="{{ route('contracts.data', ['year' => $selectedYear]) }}" />
@endsection