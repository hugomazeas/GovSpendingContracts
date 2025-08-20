@props(['vendors', 'title', 'icon', 'metric' => 'contracts'])

<div class="card">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="{{ $icon }} text-2xl text-amber-500 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">{{ $title }}</h3>
    </div>
    
    @foreach($vendors->take(5) as $index => $vendor)
        <div class="vendor-item">
            <div class="flex items-center space-x-4 flex-1">
                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-800 vendor-name">
                        {{ Str::title(strtolower($vendor->vendor_name)) }}
                    </div>
                    <div class="text-sm text-gray-600">
                        @if($metric === 'contracts')
                            Total Value: ${{ number_format($vendor->total_value, 0) }}
                        @else
                            {{ number_format($vendor->contract_count) }} contracts
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="text-right">
                @if($metric === 'contracts')
                    <div class="text-lg font-bold text-green-600">
                        {{ number_format($vendor->contract_count) }}
                    </div>
                    <div class="text-xs text-gray-500">contracts</div>
                @else
                    <div class="text-lg font-bold text-green-600">
                        ${{ number_format($vendor->total_value, 0) }}
                    </div>
                    <div class="text-xs text-gray-500">total value</div>
                @endif
            </div>
        </div>
    @endforeach
</div>