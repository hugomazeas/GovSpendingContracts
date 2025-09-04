@foreach($topContracts->take(15) as $index => $contract)
    <div class="flex items-start justify-between p-4 mb-3 bg-gray-50 rounded-xl hover:bg-primary-50 transition-colors cursor-pointer" 
         onclick="window.location.href='{{ route('contract.detail', $contract->id) }}'">
        <div class="flex items-start space-x-4 flex-1">
            <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-secondary-500 text-white rounded-full flex items-center justify-center text-sm font-semibold mt-1">
                {{ $index + 1 }}
            </div>
            <div class="flex-1">
                <div class="font-semibold text-gray-800 mb-1">
                    {{ Str::title(strtolower($contract->vendor_name)) }}
                </div>
                <div class="text-sm text-gray-700 mb-2 max-w-md">
                    {{ $contract->description_of_work_english ? 
                        (strlen($contract->description_of_work_english) > 120 ? 
                            substr($contract->description_of_work_english, 0, 120) . '...' : 
                            $contract->description_of_work_english) : 
                        'No description available' }}
                </div>
                <div class="text-xs text-gray-500 flex items-center space-x-4">
                    <span class="font-mono bg-gray-200 px-2 py-1 rounded">
                        {{ $contract->reference_number }}
                    </span>
                    <span>
                        {{ $contract->contract_date ? $contract->contract_date->format('M j, Y') : 'Date not available' }}
                    </span>
                </div>
            </div>
        </div>
        <div class="text-right ml-4 pointer-events-none">
            <div class="text-xl font-bold text-green-600">
                ${{ number_format($contract->total_contract_value, 0) }}
            </div>
            <div class="text-xs text-gray-500">{{ __('app.contract_value_label') }}</div>
        </div>
    </div>
@endforeach