<?php
namespace App\Repositories;

use App\Entities\Coupon;
use App\Core\Helpers;

/**
 * Coupon Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 */
class CouponRepository extends BaseRepository
{
    protected string $table = 'Coupons';

    /**
     * Kuponları firma bilgisiyle getirir
     */
    public function findAllWithDetails(): array
    {
        $sql = "SELECT c.*, bc.name as company_name,
                (SELECT COUNT(*) FROM User_Coupons uc WHERE uc.coupon_id = c.id) as usage_count
                FROM {$this->table} c
                LEFT JOIN Bus_Company bc ON c.company_id = bc.id
                ORDER BY c.created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Kupon kodu ile bulur
     */
    public function findByCode(string $code): ?Coupon
    {
        $sql = "SELECT c.*, bc.name as company_name,
                (SELECT COUNT(*) FROM User_Coupons uc WHERE uc.coupon_id = c.id) as usage_count
                FROM {$this->table} c
                LEFT JOIN Bus_Company bc ON c.company_id = bc.id
                WHERE c.code = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([strtoupper($code)]);
        $data = $stmt->fetch();
        
        return $data ? new Coupon($data) : null;
    }

    /**
     * Coupon entity'den kayıt oluşturur
     */
    public function createFromEntity(Coupon $coupon): string
    {
        $data = [
            'id' => $coupon->getId() ?? Helpers::generateId('coupon'),
            'code' => $coupon->getCode(),
            'discount' => $coupon->getDiscount(),
            'company_id' => $coupon->getCompanyId(),
            'usage_limit' => $coupon->getUsageLimit(),
            'expire_date' => $coupon->getExpireDate()
        ];
        
        return $this->create($data);
    }

    /**
     * Kullanıcı bu kuponu kullanmış mı kontrol eder
     */
    public function hasUserUsedCoupon(string $userId, string $couponId): bool
    {
        $sql = "SELECT 1 FROM User_Coupons WHERE user_id = ? AND coupon_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $couponId]);
        return (bool) $stmt->fetch();
    }

    /**
     * Kupon kullanımını kaydeder
     */
    public function recordCouponUsage(string $userId, string $couponId): bool
    {
        $sql = "INSERT INTO User_Coupons (id, user_id, coupon_id, created_at) VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            Helpers::generateId('uc'),
            $userId,
            $couponId,
            Helpers::getCurrentDateTime()
        ]);
    }
}
