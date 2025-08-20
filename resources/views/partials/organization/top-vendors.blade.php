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