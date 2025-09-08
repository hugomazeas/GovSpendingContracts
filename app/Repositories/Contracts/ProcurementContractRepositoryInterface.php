<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface ProcurementContractRepositoryInterface
{
    public function getDataTableData(Request $request): array;

    public function getVendorDataTableData(string $vendorName, Request $request): array;

    public function getOrganizationDataTableData(string $organization, Request $request): array;

    public function getVendorOrganizationDataTableData(string $vendorName, string $organization, Request $request): array;

    public function getSpendingByYear(?string $organization = null): Collection;

    public function getSpendingByYearForVendor(string $vendorName): Collection;

    public function getOrganizationSpendingAnalysis(array $years): Collection;

    public function getOrganizationsPieChartData(string $year): array;

    public function getVendorHistoricalContracts(string $vendorName): Collection;

    public function getOrganizationHistoricalContracts(string $organization): Collection;

    public function getVendorOrganizationHistoricalContracts(string $vendorName, string $organization): Collection;

    public function getAllHistoricalContracts(): Collection;
}
