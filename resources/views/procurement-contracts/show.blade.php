@extends('layouts.app')

@section('title', __('app.contract_detail_title'))

@section('content')
    <!-- Breadcrumb Navigation -->
    <div class="mb-8">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center text-sm font-medium text-neutral-700 hover:text-primary-600">
                        <i class="fas fa-home mr-2"></i>
                        {{ __('app.dashboard') }}
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <a href="{{ route('contracts.index') }}" class="ml-1 text-sm font-medium text-neutral-700 hover:text-primary-600 md:ml-2">{{ __('app.contracts') }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-neutral-400 mx-2"></i>
                        <span class="ml-1 text-sm font-medium text-neutral-500 md:ml-2">{{ $contract->reference_number }}</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl md:text-4xl font-bold font-heading text-neutral-900 mb-4">
                <i class="fas fa-file-contract text-primary-600 mr-4"></i>
                {{ __('app.contract_details') }}
            </h1>
            <p class="text-lg text-neutral-600 leading-relaxed">
                {{ __('app.contract_reference') }}: <span class="font-semibold text-primary-700">{{ $contract->reference_number }}</span>
            </p>
        </div>
    </div>

    <!-- Contract Information Grid -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 mb-12">
        
        <!-- Main Contract Information -->
        <div class="xl:col-span-2 space-y-8">
            
            <!-- Basic Contract Details -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-info-circle text-primary-600 mr-3"></i>
                    {{ __('app.basic_contract_info') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.reference_number') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg font-mono">{{ $contract->reference_number ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.procurement_id') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg font-mono">{{ $contract->procurement_identification_number ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.contract_date') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->contract_date?->format('F j, Y') ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.contract_year') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->contract_year ?? __('app.not_available') }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.buyer_name') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->buyer_name ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.agreement_type') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->agreement_type ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.instrument_type') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->instrument_type ?? __('app.not_available') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.amendment_number') }}</label>
                            <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->amendment_no ?? __('app.not_available') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contract Description -->
            @if($contract->description_of_work_english)
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-file-text text-primary-600 mr-3"></i>
                    {{ __('app.description_of_work') }}
                </h2>
                <div class="bg-neutral-50 p-6 rounded-lg">
                    <p class="text-neutral-900 leading-relaxed">{{ $contract->description_of_work_english }}</p>
                </div>
            </div>
            @endif

            <!-- Contract Period -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-calendar-alt text-primary-600 mr-3"></i>
                    {{ __('app.contract_period') }}
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.start_date') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->contract_period_start_date?->format('F j, Y') ?? __('app.not_available') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.end_date') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->contract_period_end_date?->format('F j, Y') ?? __('app.not_available') }}</p>
                    </div>
                </div>
            </div>

            <!-- Comments -->
            @if($contract->comments_english || $contract->additional_comments_english)
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-comments text-primary-600 mr-3"></i>
                    {{ __('app.comments') }}
                </h2>
                
                @if($contract->comments_english)
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.comments') }}</label>
                    <div class="bg-neutral-50 p-4 rounded-lg">
                        <p class="text-neutral-900">{{ $contract->comments_english }}</p>
                    </div>
                </div>
                @endif
                
                @if($contract->additional_comments_english)
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.additional_comments') }}</label>
                    <div class="bg-neutral-50 p-4 rounded-lg">
                        <p class="text-neutral-900">{{ $contract->additional_comments_english }}</p>
                    </div>
                </div>
                @endif
            </div>
            @endif

        </div>

        <!-- Sidebar Information -->
        <div class="space-y-8">
            
            <!-- Vendor Information -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-building text-secondary-600 mr-3"></i>
                    {{ __('app.vendor_information') }}
                </h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.vendor_name') }}</label>
                        @if($contract->vendor_name)
                            <a href="{{ route('vendor.detail', rawurlencode($contract->vendor_name)) }}" 
                               class="block text-secondary-600 hover:text-secondary-800 font-medium bg-neutral-50 px-4 py-2 rounded-lg hover:bg-secondary-50 transition-colors">
                                {{ $contract->vendor_name }}
                            </a>
                        @else
                            <p class="text-neutral-500 bg-neutral-50 px-4 py-2 rounded-lg">{{ __('app.not_available') }}</p>
                        @endif
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.country_of_vendor') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->country_of_vendor ?? __('app.not_available') }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.vendor_postal_code') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg font-mono">{{ $contract->vendor_postal_code ?? __('app.not_available') }}</p>
                    </div>
                </div>
            </div>

            <!-- Organization Information -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-building-columns text-accent-600 mr-3"></i>
                    {{ __('app.organization_information') }}
                </h2>
                
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.organization') }}</label>
                    @if($contract->organization)
                        <a href="{{ route('organization.detail', ['organization' => urlencode($contract->organization)]) }}" 
                           class="block text-accent-600 hover:text-accent-800 font-medium bg-neutral-50 px-4 py-2 rounded-lg hover:bg-accent-50 transition-colors">
                            {{ $contract->organization }}
                        </a>
                    @else
                        <p class="text-neutral-500 bg-neutral-50 px-4 py-2 rounded-lg">{{ __('app.not_available') }}</p>
                    @endif
                </div>
            </div>

            <!-- Financial Information -->
            <div class="card">
                <h2 class="section-title">
                    <i class="fas fa-dollar-sign text-green-600 mr-3"></i>
                    {{ __('app.financial_information') }}
                </h2>
                
                <div class="space-y-4">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-4 rounded-lg border-l-4 border-green-500">
                        <label class="block text-sm font-semibold text-green-700 mb-1">{{ __('app.total_contract_value') }}</label>
                        <p class="text-2xl font-bold text-green-800">
                            {{ $contract->total_contract_value ? '$' . number_format($contract->total_contract_value, 2) : __('app.not_available') }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.original_contract_value') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">
                            {{ $contract->original_contract_value ? '$' . number_format($contract->original_contract_value, 2) : __('app.not_available') }}
                        </p>
                    </div>
                    
                    @if($contract->contract_amendment_value)
                    <div>
                        <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.amendment_value') }}</label>
                        <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">
                            ${{ number_format($contract->contract_amendment_value, 2) }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Contract Details -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
        
        <!-- Procurement Details -->
        <div class="card">
            <h2 class="section-title">
                <i class="fas fa-gavel text-primary-600 mr-3"></i>
                {{ __('app.procurement_details') }}
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.solicitation_procedure') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->solicitation_procedure ?? __('app.not_available') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.number_of_bids') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->number_of_bids ?? __('app.not_available') }}</p>
                </div>
                
                @if($contract->limited_tendering_reason)
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.limited_tendering_reason') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->limited_tendering_reason }}</p>
                </div>
                @endif
                
                @if($contract->award_criteria)
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.award_criteria') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->award_criteria }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Commodity and Economic Information -->
        <div class="card">
            <h2 class="section-title">
                <i class="fas fa-boxes text-primary-600 mr-3"></i>
                {{ __('app.commodity_information') }}
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.commodity') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg">{{ $contract->commodity ?? __('app.not_available') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.commodity_code') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg font-mono">{{ $contract->commodity_code ?? __('app.not_available') }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">{{ __('app.economic_object_code') }}</label>
                    <p class="text-neutral-900 bg-neutral-50 px-4 py-2 rounded-lg font-mono">{{ $contract->economic_object_code ?? __('app.not_available') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Special Indicators -->
    @if($contract->indigenous_business || $contract->ministers_office_contracts || $contract->former_public_servant)
    <div class="card mb-12">
        <h2 class="section-title">
            <i class="fas fa-flag text-primary-600 mr-3"></i>
            {{ __('app.special_indicators') }}
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @if($contract->indigenous_business)
            <div class="bg-amber-50 p-4 rounded-lg border-l-4 border-amber-400">
                <label class="block text-sm font-semibold text-amber-700 mb-1">{{ __('app.indigenous_business') }}</label>
                <p class="text-amber-800">{{ $contract->indigenous_business }}</p>
            </div>
            @endif
            
            @if($contract->ministers_office_contracts)
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400">
                <label class="block text-sm font-semibold text-blue-700 mb-1">{{ __('app.ministers_office_contract') }}</label>
                <p class="text-blue-800">{{ $contract->ministers_office_contracts }}</p>
            </div>
            @endif
            
            @if($contract->former_public_servant)
            <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-400">
                <label class="block text-sm font-semibold text-purple-700 mb-1">{{ __('app.former_public_servant') }}</label>
                <p class="text-purple-800">{{ $contract->former_public_servant }}</p>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Back Navigation -->
    <div class="text-center">
        <a href="{{ route('contracts.index') }}" class="btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>
            {{ __('app.back_to_contracts') }}
        </a>
    </div>

@endsection