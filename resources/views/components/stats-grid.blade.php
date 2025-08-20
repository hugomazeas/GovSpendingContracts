@props(['stats'])

<!-- Year-specific Stats Header -->
<div class="text-center mb-6">
    <h3 class="text-2xl font-bold text-gray-800 mb-2">
        {{ $stats['year'] ?? 'Current' }} Year Statistics
    </h3>
    <p class="text-gray-600">Government procurement data for fiscal year {{ $stats['year'] ?? date('Y') }}</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Contracts -->
    <div class="stats-card">
        <div class="text-4xl text-blue-500 mb-4">
            <i class="fas fa-file-contract"></i>
        </div>
        <div class="text-3xl font-bold text-blue-600 mb-2">
            {{ number_format($stats['total_contracts']) }}
        </div>
        <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
            Total Contracts
        </div>
    </div>
    
    <!-- Total Value -->
    <div class="stats-card">
        <div class="text-4xl text-green-500 mb-4">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="text-3xl font-bold text-green-600 mb-2">
            ${{ number_format($stats['total_value'] / 1000000000, 1) }}B
        </div>
        <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
            Total Value
        </div>
    </div>
    
    <!-- Unique Vendors -->
    <div class="stats-card">
        <div class="text-4xl text-indigo-500 mb-4">
            <i class="fas fa-building"></i>
        </div>
        <div class="text-3xl font-bold text-indigo-600 mb-2">
            {{ number_format($stats['unique_vendors']) }}
        </div>
        <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
            Unique Vendors
        </div>
    </div>
    
    <!-- Average Contract Value -->
    <div class="stats-card">
        <div class="text-4xl text-amber-500 mb-4">
            <i class="fas fa-calculator"></i>
        </div>
        <div class="text-3xl font-bold text-amber-600 mb-2">
            ${{ number_format($stats['avg_contract_value'] / 1000, 0) }}K
        </div>
        <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
            Avg Contract Value
        </div>
    </div>
</div>