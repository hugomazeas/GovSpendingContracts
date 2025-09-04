@props(['countries'])

<div class="card">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="fas fa-globe text-2xl text-accent-600 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">Top 5 Most Profitable Vendor Countries</h3>
    </div>
    
    @if($countries->isEmpty())
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-globe text-4xl text-gray-300 mb-4"></i>
            <p class="text-lg">{{ __('app.no_rank_data') }}</p>
        </div>
    @else
        @foreach($countries as $index => $country)
            <div class="vendor-item group">
                <div class="flex items-center space-x-4 flex-1">
                    <div class="w-8 h-8 bg-gradient-to-br from-accent-500 to-accent-600 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 vendor-name group-hover:text-accent-600 transition-colors">
                            {{ $country->country_of_vendor }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ number_format($country->contract_count) }} contracts
                        </div>
                    </div>
                </div>
                
                <div class="text-right">
                    <div class="text-lg font-bold text-green-600">
                        @if($country->total_value >= 1000000000)
                            ${{ number_format($country->total_value / 1000000000, 1) }}B
                        @elseif($country->total_value >= 1000000)
                            ${{ number_format($country->total_value / 1000000, 1) }}M
                        @elseif($country->total_value >= 1000)
                            ${{ number_format($country->total_value / 1000, 1) }}K
                        @else
                            ${{ number_format($country->total_value) }}
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">total value</div>
                </div>
            </div>
        @endforeach
    @endif
</div>