<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface ProcurementAnalyticsRepositoryInterface
{
    public function getGeneralStatistics(int $year): array;

    public function getOrganizationStats(string $organization, int $year): array;

    public function getTopVendorsForOrganization(string $organization, int $year): Collection;

    public function getTopContractsForOrganization(string $organization, int $year): Collection;

    public function getContractsByYearForOrganization(string $organization): Collection;

    public function getVendorStats(string $vendor, int $year): array;

    public function getTopMinistersForVendor(string $vendor, int $year): Collection;

    public function getContractsByYearForVendor(string $vendor): Collection;

    public function getTopVendorsByCount(int $year): Collection;

    public function getTopVendorsByValue(int $year): Collection;

    public function getTopOrganizationsBySpending(int $year): Collection;

    public function getTopVendorCountriesByValue(int $year): Collection;

    public function getAvailableYears(): Collection;

    public function getAvailableYearsForOrganization(string $organization): Collection;

    public function getAvailableYearsForVendor(string $vendor): Collection;

    public function getAvailableYearsForVendorOrganization(string $vendor, string $organization): Collection;

    public function getVendorOrganizationStats(string $vendor, string $organization, int $year): array;

    public function getVendorOrganizationSpendingOverTime(string $vendor, string $organization): Collection;
}
