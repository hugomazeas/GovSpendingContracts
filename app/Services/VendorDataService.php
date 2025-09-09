<?php

namespace App\Services;

use Illuminate\Support\Collection;

class VendorDataService
{
    public function formatVendorContractsData(Collection $contracts, string $decodedVendor): Collection
    {
        return $contracts->map(function ($contract) use ($decodedVendor) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $this->formatCurrency($contract->total_contract_value),
                'organization' => $this->formatOrganizationLink($contract->organization, $decodedVendor),
                'description_of_work_english' => $this->truncateDescription($contract->description_of_work_english),
            ];
        });
    }

    public function formatVendorOrganizationContractsData(Collection $contracts): Collection
    {
        return $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $this->formatCurrency($contract->total_contract_value),
                'description_of_work_english' => $this->truncateDescription($contract->description_of_work_english),
            ];
        });
    }

    public function formatOrganizationContractsData(Collection $contracts, string $decodedOrganization): Collection
    {
        return $contracts->map(function ($contract) use ($decodedOrganization) {
            return [
                'id' => $contract->id,
                'vendor_name' => $this->formatVendorLink($contract->vendor_name, $decodedOrganization),
                'reference_number' => $contract->reference_number,
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $this->formatCurrency($contract->total_contract_value),
                'description_of_work_english' => $this->truncateDescription($contract->description_of_work_english),
            ];
        });
    }

    public function formatGeneralContractsData(Collection $contracts): Collection
    {
        return $contracts->map(function ($contract) {
            return [
                'id' => $contract->id,
                'reference_number' => $contract->reference_number,
                'vendor_name' => $this->formatSimpleVendorLink($contract->vendor_name),
                'contract_date' => $contract->contract_date?->format('Y-m-d'),
                'total_contract_value' => $this->formatCurrency($contract->total_contract_value),
                'organization' => $this->formatSimpleOrganizationLink($contract->organization),
                'description_of_work_english' => $this->truncateDescription($contract->description_of_work_english),
            ];
        });
    }

    private function formatCurrency(?float $value): string
    {
        return $value ? '$'.number_format($value, 2) : '-';
    }

    private function formatOrganizationLink(?string $organization, string $vendor): string
    {
        if (! $organization) {
            return '-';
        }

        return '<div class="flex flex-col gap-1">'.
            '<a href="'.route('organization.detail', ['organization' => urlencode($organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($organization).'</a>'.
            '<a href="'.route('vendor.organization.contracts', ['vendor' => urlencode($vendor), 'organization' => urlencode($organization)]).'" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors"><i class="fas fa-handshake mr-1"></i>View partnership</a>'.
            '</div>';
    }

    private function formatVendorLink(?string $vendor, string $organization): string
    {
        if (! $vendor) {
            return '-';
        }

        return '<div class="flex flex-col gap-1">'.
            '<a href="'.route('vendor.detail', ['vendor' => urlencode($vendor)]).'" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors">'.e($vendor).'</a>'.
            '<a href="'.route('vendor.organization.contracts', ['vendor' => urlencode($vendor), 'organization' => urlencode($organization)]).'" class="text-xs text-indigo-600 hover:text-indigo-800 hover:underline font-medium transition-colors"><i class="fas fa-handshake mr-1"></i>View partnership</a>'.
            '</div>';
    }

    private function formatSimpleVendorLink(?string $vendor): string
    {
        if (! $vendor) {
            return '-';
        }

        return '<a href="'.route('vendor.detail', rawurlencode($vendor)).'" class="text-blue-600 hover:text-blue-800 hover:underline font-medium transition-colors">'.e($vendor).'</a>';
    }

    private function formatSimpleOrganizationLink(?string $organization): string
    {
        if (! $organization) {
            return '-';
        }

        return '<a href="'.route('organization.detail', ['organization' => urlencode($organization)]).'" class="text-purple-600 hover:text-purple-800 hover:underline font-medium transition-colors">'.e($organization).'</a>';
    }

    private function truncateDescription(?string $description): string
    {
        if (! $description) {
            return '-';
        }

        return strlen($description) > 100 ? substr($description, 0, 100).'...' : $description;
    }
}
