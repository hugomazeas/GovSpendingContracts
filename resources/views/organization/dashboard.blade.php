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

    <!-- Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Total Contracts -->
        <div class="stats-card">
            <div class="text-4xl text-blue-500 mb-4">
                <i class="fas fa-file-contract"></i>
            </div>
            <div class="text-3xl font-bold text-blue-600 mb-2">
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
            <div class="text-3xl font-bold text-green-600 mb-2">
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
            <div class="text-3xl font-bold text-indigo-600 mb-2">
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
            <div class="text-3xl font-bold text-amber-600 mb-2">
                ${{ number_format($organizationStats['avg_contract_value'] / 1000, 0) }}K
            </div>
            <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
                Avg Contract Value
            </div>
        </div>
    </div>
    
    <!-- Top Vendors and Yearly Spending -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
        <!-- Top Vendors -->
        <div class="card">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-trophy text-2xl text-amber-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">Top Vendors</h3>
            </div>
            
            @foreach($topVendorsForOrg->take(10) as $index => $vendor)
                <div class="flex items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">
                                {{ Str::title(strtolower($vendor->vendor_name)) }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ number_format($vendor->contract_count) }} contracts
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600">
                            ${{ number_format($vendor->total_value, 0) }}
                        </div>
                        <div class="text-xs text-gray-500">total value</div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Spending by Year -->
        <div class="card">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <i class="fas fa-chart-line text-2xl text-blue-500 mr-3"></i>
                <h3 class="text-xl font-semibold text-gray-800">Spending by Year</h3>
            </div>
            
            @foreach($contractsByYear->take(8) as $year)
                <div class="flex items-center justify-between p-4 mb-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-lg flex items-center justify-center text-sm font-semibold">
                            {{ $year->contract_year }}
                        </div>
                        <div>
                            <div class="font-semibold text-gray-800">
                                {{ number_format($year->contract_count) }} contracts
                            </div>
                            <div class="text-sm text-gray-600">
                                Average: ${{ number_format($year->total_spending / $year->contract_count, 0) }}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-lg font-bold text-green-600">
                            @if($year->total_spending >= 1000000000)
                                ${{ number_format($year->total_spending / 1000000000, 1) }}B
                            @else
                                ${{ number_format($year->total_spending / 1000000, 1) }}M
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">total spent</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    
    <!-- Top Contracts -->
    <div class="card">
        <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
            <i class="fas fa-list-alt text-2xl text-indigo-500 mr-3"></i>
            <h3 class="text-xl font-semibold text-gray-800">Largest Contracts</h3>
            <div class="ml-auto text-sm text-gray-600">
                <i class="fas fa-info-circle mr-1"></i>
                Biggest individual spending items
            </div>
        </div>
        
        @foreach($topContracts->take(15) as $index => $contract)
            <div class="flex items-start justify-between p-4 mb-3 bg-gray-50 rounded-xl hover:bg-blue-50 transition-colors">
                <div class="flex items-start space-x-4 flex-1">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mt-1">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 mb-1">
                            {{ Str::title(strtolower($contract->vendor_name)) }}
                        </div>
                        <div class="text-sm text-gray-700 mb-2 max-w-md">
                            {{ $contract->description_of_work_english ? 
                                (strlen($contract->description_of_work_english) > 120 ? 
                                    substr($contract->description_of_work_english, 0, 120) . '...' : 
                                    $contract->description_of_work_english) : 
                                'No description available' }}
                        </div>
                        <div class="text-xs text-gray-500 flex items-center space-x-4">
                            <span class="font-mono bg-gray-200 px-2 py-1 rounded">
                                {{ $contract->reference_number }}
                            </span>
                            <span>
                                {{ $contract->contract_date ? $contract->contract_date->format('M j, Y') : 'Date not available' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="text-right ml-4">
                    <div class="text-xl font-bold text-green-600">
                        ${{ number_format($contract->total_contract_value, 0) }}
                    </div>
                    <div class="text-xs text-gray-500">contract value</div>
                </div>
            </div>
        @endforeach
    </div>
@endsection