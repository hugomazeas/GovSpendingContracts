@foreach($ministers->take(10) as $index => $minister)
    <a href="{{ route('organization.detail', ['organization' => urlencode($minister->organization)]) }}" class="vendor-item-link">
        <div class="vendor-item clickable-organization group">
            <div class="flex items-center space-x-4 flex-1">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                    {{ $index + 1 }}
                </div>
                <div class="flex-1">
                    <div class="font-semibold text-gray-800 vendor-name group-hover:text-primary-600 transition-colors">
                        {{ $minister->organization }}
                    </div>
                    <div class="text-sm text-gray-600">
                        {{ number_format($minister->contract_count) }} contracts
                    </div>
                </div>
            </div>
            
            <div class="text-right flex items-center space-x-2">
                <div>
                    <div class="text-lg font-bold text-purple-600">
                        @if($minister->total_value >= 1000000000)
                            ${{ number_format($minister->total_value / 1000000000, 1) }}B
                        @elseif($minister->total_value >= 1000000)
                            ${{ number_format($minister->total_value / 1000000, 1) }}M
                        @elseif($minister->total_value >= 1000)
                            ${{ number_format($minister->total_value / 1000, 1) }}K
                        @else
                            ${{ number_format($minister->total_value, 0) }}
                        @endif
                    </div>
                    <div class="text-xs text-gray-500">total value</div>
                </div>
                <i class="fas fa-arrow-right text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity ml-2"></i>
            </div>
        </div>
    </a>
@endforeach