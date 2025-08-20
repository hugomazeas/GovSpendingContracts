@props(['organizations'])

<div class="card">
    <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
        <i class="fas fa-building-columns text-2xl text-blue-500 mr-3"></i>
        <h3 class="text-xl font-semibold text-gray-800">Top Government Organizations by Spending</h3>
    </div>
    
    @foreach($organizations->take(5) as $index => $org)
        <a href="{{ route('organization.detail', ['organization' => urlencode($org->organization)]) }}" class="vendor-item-link">
            <div class="vendor-item clickable-organization group">
                <div class="flex items-center space-x-4 flex-1">
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">
                        {{ $index + 1 }}
                    </div>
                    <div class="flex-1">
                        <div class="font-semibold text-gray-800 vendor-name group-hover:text-primary-600 transition-colors">
                            {{ $org->organization }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ number_format($org->contract_count) }} contracts
                        </div>
                    </div>
                </div>
                
                <div class="text-right flex items-center space-x-2">
                    <div>
                        <div class="text-lg font-bold text-green-600">
                            @if($org->total_spending >= 1000000000)
                                ${{ number_format($org->total_spending / 1000000000, 1) }}B
                            @else
                                ${{ number_format($org->total_spending / 1000000, 1) }}M
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">total spent</div>
                    </div>
                    <i class="fas fa-arrow-right text-primary-500 opacity-0 group-hover:opacity-100 transition-opacity ml-2"></i>
                </div>
            </div>
        </a>
    @endforeach
</div>