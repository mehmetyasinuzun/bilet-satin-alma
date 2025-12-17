<?php
namespace App\Repositories;

use App\Entities\User;
use App\Core\Helpers;

/**
 * User Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 * - Polymorphism: create(), findById() gibi metodları override eder
 * - Encapsulation: User entity'si ile çalışır, veritabanı detaylarını gizler
 */
class UserRepository extends BaseRepository
{
    protected string $table = 'User';

    /**
     * Email ile kullanıcı bulur
     */
    public function findByEmail(string $email): ?User
    {
        $data = $this->findOneBy(['email' => strtolower($email)]);
        return $data ? new User($data) : null;
    }

    /**
     * ID ile User entity döndürür
     */
    public function findUserById(string $id): ?User
    {
        $data = $this->findById($id);
        return $data ? new User($data) : null;
    }

    /**
     * User entity'den kayıt oluşturur
     */
    public function createFromEntity(User $user): string
    {
        $user->hashPassword();
        
        $data = [
            'id' => $user->getId() ?? Helpers::generateId('user'),
            'full_name' => $user->getFullName(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'role' => $user->getRole(),
            'company_id' => $user->getCompanyId(),
            'balance' => $user->getBalance(),
            'created_at' => Helpers::getCurrentDateTime()
        ];
        
        return $this->create($data);
    }

    /**
     * Kullanıcı bakiyesini günceller
     */
    public function updateBalance(string $userId, float $newBalance): bool
    {
        return $this->update($userId, ['balance' => $newBalance]);
    }

    /**
     * Bakiyeden düşer
     */
    public function deductBalance(string $userId, float $amount): bool
    {
        $sql = "UPDATE {$this->table} SET balance = balance - ? WHERE id = ? AND balance >= ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $userId, $amount]);
    }

    /**
     * Bakiye ekler
     */
    public function addBalance(string $userId, float $amount): bool
    {
        $sql = "UPDATE {$this->table} SET balance = balance + ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$amount, $userId]);
    }

    /**
     * Kullanıcı bakiyesini döndürür
     */
    public function getBalance(string $userId): float
    {
        $sql = "SELECT balance FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return (float) ($stmt->fetchColumn() ?? 0);
    }

    /**
     * Role göre kullanıcıları getirir
     */
    public function findByRole(string $role): array
    {
        return $this->findBy(['role' => $role], 'full_name');
    }

    /**
     * Firma adminlerini getirir
     */
    public function findCompanyAdmins(): array
    {
        $sql = "SELECT u.*, bc.name as company_name
                FROM {$this->table} u
                LEFT JOIN Bus_Company bc ON u.company_id = bc.id
                WHERE u.role = 'company.admin'
                ORDER BY u.full_name";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Toplam kullanıcı sayısı
     */
    public function countUsers(): int
    {
        return $this->count(['role' => 'user']);
    }

    /**
     * Email zaten kullanılıyor mu kontrol eder
     */
    public function emailExists(string $email, ?string $excludeUserId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $sql .= " LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
