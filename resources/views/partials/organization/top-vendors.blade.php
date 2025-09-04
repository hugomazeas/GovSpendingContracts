@foreach($topVendorsForOrg->take(10) as $index => $vendor)
    <div class="vendor-item clickable-organization group bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between">
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
            
            <div class="text-right">
                <div class="text-lg font-bold text-green-600">
                    ${{ number_format($vendor->total_value, 0) }}
                </div>
                <div class="text-xs text-gray-500">{{ __('app.total_value_label') }}</div>
            </div>
        </div>
        
        <div class="flex gap-2 mt-3 pt-3 border-t border-gray-100">
            <a href="{{ route('vendor.detail', rawurlencode($vendor->vendor_name)) }}" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-3 py-1 rounded-full font-medium transition-colors">
                <i class="fas fa-building mr-1"></i>View Vendor
            </a>
            @if(isset($organizationName))
            <a href="{{ route('vendor.organization.contracts', ['vendor' => rawurlencode($vendor->vendor_name), 'organization' => rawurlencode($organizationName)]) }}" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-3 py-1 rounded-full font-medium transition-colors">
                <i class="fas fa-handshake mr-1"></i>View Partnership
            </a>
            @endif
        </div>
    </div>
@endforeach