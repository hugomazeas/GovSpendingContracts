@props(['stats'])


<div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-6 mb-8">
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
            @currency($stats['total_value'])
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
            @currencyAvg($stats['avg_contract_value'])
        </div>
        <div class="text-gray-600 text-sm font-medium uppercase tracking-wide">
            Avg Contract Value
        </div>
    </div>
</div>
