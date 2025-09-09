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
        Schema::table('contracts', function (Blueprint $table) {
            // Critical performance indexes based on query patterns
            $table->index('contract_year');
            $table->index('organization');
            $table->index('vendor_name');
            $table->index('total_contract_value');
            $table->index('contract_date');

            // Composite indexes for common query combinations
            $table->index(['contract_year', 'organization']);
            $table->index(['contract_year', 'vendor_name']);
            $table->index(['organization', 'contract_year']);
            $table->index(['organization', 'vendor_name']);
            $table->index(['contract_year', 'total_contract_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropIndex(['contracts_contract_year_index']);
            $table->dropIndex(['contracts_organization_index']);
            $table->dropIndex(['contracts_vendor_name_index']);
            $table->dropIndex(['contracts_total_contract_value_index']);
            $table->dropIndex(['contracts_contract_date_index']);
            $table->dropIndex(['contracts_contract_year_organization_index']);
            $table->dropIndex(['contracts_contract_year_vendor_name_index']);
            $table->dropIndex(['contracts_organization_contract_year_index']);
            $table->dropIndex(['contracts_organization_vendor_name_index']);
            $table->dropIndex(['contracts_contract_year_total_contract_value_index']);
        });
    }
};
