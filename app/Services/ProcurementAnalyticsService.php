<?php

namespace App\Services;

use App\Repositories\Contracts\ProcurementAnalyticsRepositoryInterface;
use Illuminate\Support\Collection;

class ProcurementAnalyticsService
{
    public function __construct(
        private readonly ProcurementAnalyticsRepositoryInterface $analyticsRepository
    ) {}

    public function getGeneralStatistics(int $year): array
    {
        return $this->analyticsRepository->getGeneralStatistics($year);
    }

    // Organization Analytics
    public function getOrganizationStats(string $organization, int $year): array
    {
        return $this->analyticsRepository->getOrganizationStats($organization, $year);
    }

    public function getTopVendorsForOrganization(string $organization, int $year): Collection
    {
        return $this->analyticsRepository->getTopVendorsForOrganization($organization, $year);
    }

    public function getTopContractsForOrganization(string $organization, int $year): Collection
    {
        return $this->analyticsRepository->getTopContractsForOrganization($organization, $year);
    }

    public function getContractsByYearForOrganization(string $organization): Collection
    {
        return $this->analyticsRepository->getContractsByYearForOrganization($organization);
    }

    // Vendor Analytics
    public function getVendorStats(string $vendor, int $year): array
    {
        return $this->analyticsRepository->getVendorStats($vendor, $year);
    }

    public function getTopMinistersForVendor(string $vendor, int $year): Collection
    {
        return $this->analyticsRepository->getTopMinistersForVendor($vendor, $year);
    }

    public function getContractsByYearForVendor(string $vendor): Collection
    {
        return $this->analyticsRepository->getContractsByYearForVendor($vendor);
    }

    // General Vendor Leaderboards
    public function getTopVendorsByCount(int $year): Collection
    {
        return $this->analyticsRepository->getTopVendorsByCount($year);
    }

    public function getTopVendorsByValue(int $year): Collection
    {
        return $this->analyticsRepository->getTopVendorsByValue($year);
    }

    public function getTopOrganizationsBySpending(int $year): Collection
    {
        return $this->analyticsRepository->getTopOrganizationsBySpending($year);
    }

    public function getTopVendorCountriesByValue(int $year): Collection
    {
        return $this->analyticsRepository->getTopVendorCountriesByValue($year);
    }

    // Available Years Methods
    public function getAvailableYears(): Collection
    {
        return $this->analyticsRepository->getAvailableYears();
    }

    public function getAvailableYearsForOrganization(string $organization): Collection
    {
        return $this->analyticsRepository->getAvailableYearsForOrganization($organization);
    }

    public function getAvailableYearsForVendor(string $vendor): Collection
    {
        return $this->analyticsRepository->getAvailableYearsForVendor($vendor);
    }

    public function getAvailableYearsForVendorOrganization(string $vendor, string $organization): Collection
    {
        return $this->analyticsRepository->getAvailableYearsForVendorOrganization($vendor, $organization);
    }

    public function getVendorOrganizationStats(string $vendor, string $organization, int $year): array
    {
        return $this->analyticsRepository->getVendorOrganizationStats($vendor, $organization, $year);
    }

    public function getVendorOrganizationSpendingOverTime(string $vendor, string $organization): Collection
    {
        return $this->analyticsRepository->getVendorOrganizationSpendingOverTime($vendor, $organization);
    }
}
