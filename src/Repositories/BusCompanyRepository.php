<?php
namespace App\Repositories;

use App\Entities\BusCompany;
use App\Core\Helpers;

/**
 * BusCompany Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 */
class BusCompanyRepository extends BaseRepository
{
    protected string $table = 'Bus_Company';

    /**
     * Firmalar ile admin ve sefer sayılarını getirir
     */
    public function findAllWithStats(): array
    {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM User u WHERE u.company_id = c.id AND u.role = 'company.admin') as admin_count,
                (SELECT COUNT(*) FROM Trips t WHERE t.company_id = c.id) as trip_count
                FROM {$this->table} c
                ORDER BY c.name";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * BusCompany entity'den kayıt oluşturur
     */
    public function createFromEntity(BusCompany $company): string
    {
        $data = [
            'id' => $company->getId() ?? Helpers::generateId('company'),
            'name' => $company->getName(),
            'logo_path' => $company->getLogoPath()
        ];
        
        return $this->create($data);
    }

    /**
     * Firma ID ile entity döndürür
     */
    public function findCompanyById(string $id): ?BusCompany
    {
        $data = $this->findById($id);
        return $data ? new BusCompany($data) : null;
    }

    /**
     * Firma adı ile bulur
     */
    public function findByName(string $name): ?BusCompany
    {
        $data = $this->findOneBy(['name' => $name]);
        return $data ? new BusCompany($data) : null;
    }

    /**
     * Firma sayısı
     */
    public function countCompanies(): int
    {
        return $this->count();
    }
}
