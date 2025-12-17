<?php
namespace App\Services;

use App\Entities\BusCompany;
use App\Repositories\BusCompanyRepository;
use App\Core\Helpers;

/**
 * BusCompany Service Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseService'den türer
 * - Composition: BusCompanyRepository'yi içerir
 */
class BusCompanyService extends BaseService
{
    private BusCompanyRepository $companyRepository;

    public function __construct()
    {
        $this->companyRepository = new BusCompanyRepository();
    }

    /**
     * Tüm firmaları istatistiklerle getirir
     */
    public function getAllCompanies(): array
    {
        return $this->companyRepository->findAllWithStats();
    }

    /**
     * Firma detaylarını getirir
     */
    public function getCompanyById(string $companyId): ?BusCompany
    {
        return $this->companyRepository->findCompanyById($companyId);
    }

    /**
     * Yeni firma oluşturur
     */
    public function createCompany(array $data): ?BusCompany
    {
        $this->clearErrors();

        $company = new BusCompany();
        $company->setName(Helpers::sanitize($data['name'] ?? ''));

        if (!$company->isValid()) {
            $this->errors = array_values($company->getErrors());
            return null;
        }

        try {
            $companyId = $this->companyRepository->createFromEntity($company);
            $company->setId($companyId);
            return $company;
        } catch (\PDOException $e) {
            $this->addError('Firma oluşturulamadı: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Firma siler
     */
    public function deleteCompany(string $companyId): bool
    {
        $this->clearErrors();

        if (!$this->companyRepository->delete($companyId)) {
            $this->addError('Firma silinemedi.');
            return false;
        }

        return true;
    }

    /**
     * Toplam firma sayısı
     */
    public function getTotalCompanyCount(): int
    {
        return $this->companyRepository->countCompanies();
    }
}
