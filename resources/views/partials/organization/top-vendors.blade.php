@foreach($topVendorsForOrg->take(10) as $index => $vendor)
    <a href="{{ route('vendor.detail', rawurlencode($vendor->vendor_name)) }}" class="vendor-item-link">
        <div class="vendor-item clickable-organization group">
            <div class="flex items-center space-x-4 flex-1">
                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-800 vendor-name group-hover:text-primary-600 transition-colors">
                        {{ Str::title(strtolower($vendor->vendor_name)) }}
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ number_format($vendor->contract_count) }} contracts
                    </div>
                </div>
            </div>
            
            <div class="text-right flex items-center space-x-2">
                <div>
                    <div class="text-lg font-bold text-green-600">
                        ${{ number_format($vendor->total_value, 0) }}
                    </div>
                    <div class="text-xs text-gray-500">total value</div>
                </div>
                <i class="fas fa-arrow-right text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity ml-2"></i>
            </div>
        </div>
    </a>
@endforeach