<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->index()->nullable();
            $table->string('procurement_identification_number')->nullable()->index();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_postal_code')->nullable();
            $table->string('buyer_name')->nullable();
            $table->date('contract_date')->nullable();
            $table->integer('contract_year')->nullable();
            $table->string('economic_object_code')->nullable();
            $table->text('description_of_work_english')->nullable();
            $table->date('contract_period_start_date')->nullable();
            $table->date('contract_period_end_date')->nullable();
            $table->decimal('total_contract_value', 15, 2)->nullable();
            $table->decimal('original_contract_value', 15, 2)->nullable();
            $table->decimal('contract_amendment_value', 15, 2)->nullable();
            $table->text('comments_english')->nullable();
            $table->text('additional_comments_english')->nullable();
            $table->string('agreement_type')->nullable();
            $table->string('commodity')->nullable();
            $table->string('commodity_code')->nullable();
            $table->string('country_of_vendor')->nullable();
            $table->string('solicitation_procedure')->nullable();
            $table->string('limited_tendering_reason')->nullable();
            $table->text('trade_agreement_exceptions')->nullable();
            $table->string('indigenous_business')->nullable();
            $table->string('intellectual_property')->nullable();
            $table->string('potential_for_commercial_exploitation')->nullable();
            $table->string('former_public_servant')->nullable();
            $table->string('standing_offer_or_supply_arrangement_number')->nullable();
            $table->string('instrument_type')->nullable();
            $table->string('ministers_office_contracts')->nullable();
            $table->integer('number_of_bids')->nullable();
            $table->string('reporting_period')->nullable();
            $table->string('organization')->nullable();
            $table->integer('amendment_no')->nullable();
            $table->integer('procurement_count')->nullable();
            $table->decimal('aggregate_total', 15, 2)->nullable();
            $table->string('working_procurement_id')->nullable();
            $table->text('trade_agreements')->nullable();
            $table->string('socio_economic_indicator')->nullable();
            $table->text('section_6_government_contracts_regulations_exceptions')->nullable();
            $table->string('procurement_strategy_for_indigenous_business')->nullable();
            $table->text('award_criteria')->nullable();
            $table->string('standing_offer')->nullable();
            $table->string('comprehensive_land_claims_agreement')->nullable();
            $table->string('csv_id')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
