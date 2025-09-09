<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    /** @use HasFactory<\Database\Factories\ContractFactory> */
    use HasFactory;

    protected $table = 'procurement_contracts';

    protected $fillable = [
        'reference_number',
        'procurement_identification_number',
        'vendor_name',
        'vendor_postal_code',
        'buyer_name',
        'contract_date',
        'contract_year',
        'economic_object_code',
        'description_of_work_english',
        'contract_period_start_date',
        'contract_period_end_date',
        'total_contract_value',
        'original_contract_value',
        'contract_amendment_value',
        'comments_english',
        'additional_comments_english',
        'agreement_type',
        'commodity',
        'commodity_code',
        'country_of_vendor',
        'solicitation_procedure',
        'limited_tendering_reason',
        'trade_agreement_exceptions',
        'indigenous_business',
        'intellectual_property',
        'potential_for_commercial_exploitation',
        'former_public_servant',
        'standing_offer_or_supply_arrangement_number',
        'instrument_type',
        'ministers_office_contracts',
        'number_of_bids',
        'reporting_period',
        'organization',
        'amendment_no',
        'procurement_count',
        'aggregate_total',
        'working_procurement_id',
        'trade_agreements',
        'socio_economic_indicator',
        'section_6_government_contracts_regulations_exceptions',
        'procurement_strategy_for_indigenous_business',
        'award_criteria',
        'standing_offer',
        'comprehensive_land_claims_agreement',
        'csv_id',
    ];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'contract_period_start_date' => 'date',
            'contract_period_end_date' => 'date',
            'total_contract_value' => 'decimal:2',
            'original_contract_value' => 'decimal:2',
            'contract_amendment_value' => 'decimal:2',
            'aggregate_total' => 'decimal:2',
            'contract_year' => 'integer',
            'number_of_bids' => 'integer',
            'amendment_no' => 'integer',
            'procurement_count' => 'integer',
        ];
    }
}
