@foreach($ministers->take(10) as $index => $minister)
    <div class="vendor-item group bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-3">
        <div class="flex items-center justify-between">
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
        </div>
        <div>
            <div class="text-right">
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
            <div class="flex gap-2 pt-3">
                <a href="{{ route('organization.detail', ['organization' => urlencode($minister->organization)]) }}" class="text-xs bg-purple-100 hover:bg-purple-200 text-purple-800 px-3 py-1 rounded-full font-medium transition-colors">
                    <i class="fas fa-building-columns mr-1"></i>View Organization
                </a>
                @if(isset($vendorName))
                    <a href="{{ route('vendor.organization.contracts', ['vendor' => rawurlencode($vendorName), 'organization' => urlencode($minister->organization)]) }}" class="text-xs bg-indigo-100 hover:bg-indigo-200 text-indigo-800 px-3 py-1 rounded-full font-medium transition-colors">
                        <i class="fas fa-handshake mr-1"></i>View Partnership
                    </a>
                @endif
            </div>
        </div>
    </div>
@endforeach
