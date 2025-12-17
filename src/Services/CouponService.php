<?php
namespace App\Services;

use App\Entities\Coupon;
use App\Repositories\CouponRepository;
use App\Core\Helpers;

/**
 * Coupon Service Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseService'den türer
 * - Composition: CouponRepository'yi içerir
 * - Single Responsibility: Sadece kupon işlemleri
 */
class CouponService extends BaseService
{
    private CouponRepository $couponRepository;

    public function __construct()
    {
        $this->couponRepository = new CouponRepository();
    }

    /**
     * Tüm kuponları getirir
     */
    public function getAllCoupons(): array
    {
        return $this->couponRepository->findAllWithDetails();
    }

    /**
     * Kupon oluşturur
     */
    public function createCoupon(array $data): ?Coupon
    {
        $this->clearErrors();

        $coupon = new Coupon();
        $coupon->setCode($data['code'] ?? '');
        $coupon->setDiscount((float)($data['discount'] ?? 0));
        $coupon->setCompanyId($data['company_id'] ?: null);
        $coupon->setUsageLimit(($data['usage_limit'] ?? 0) > 0 ? (int)$data['usage_limit'] : null);
        $coupon->setExpireDate($data['expire_date'] ?: null);

        if (!$coupon->isValid()) {
            $this->errors = array_values($coupon->getErrors());
            return null;
        }

        try {
            $couponId = $this->couponRepository->createFromEntity($coupon);
            $coupon->setId($couponId);
            return $coupon;
        } catch (\PDOException $e) {
            if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                $this->addError('Bu kupon kodu zaten kullanılıyor. Farklı bir kod deneyin.');
            } else {
                $this->addError('Kupon oluşturulamadı: ' . $e->getMessage());
            }
            return null;
        }
    }

    /**
     * Kupon siler
     */
    public function deleteCoupon(string $couponId): bool
    {
        $this->clearErrors();

        if (!$this->couponRepository->delete($couponId)) {
            $this->addError('Kupon silinemedi.');
            return false;
        }

        return true;
    }

    /**
     * Kuponu doğrular ve uygular
     */
    public function validateAndApplyCoupon(string $code, string $userId, ?string $companyId, float $originalPrice): array
    {
        $this->clearErrors();

        $coupon = $this->couponRepository->findByCode($code);

        if (!$coupon) {
            $this->addError('Geçersiz kupon kodu.');
            return [];
        }

        // Kupon süresi kontrolü
        if ($coupon->isExpired()) {
            $this->addError('Kupon süresi dolmuş.');
            return [];
        }

        // Kullanım limiti kontrolü
        if ($coupon->isLimitReached()) {
            $this->addError('Kupon kullanım limiti doldu.');
            return [];
        }

        // Firma kontrolü
        if (!$coupon->isValidForCompany($companyId)) {
            $this->addError('Bu kupon seçilen firma için geçerli değil.');
            return [];
        }

        // Kullanıcı daha önce kullanmış mı kontrolü
        if ($this->couponRepository->hasUserUsedCoupon($userId, $coupon->getId())) {
            $this->addError('Bu kuponu daha önce kullandınız.');
            return [];
        }

        return [
            'coupon_id' => $coupon->getId(),
            'discount' => $coupon->getDiscount(),
            'discounted_price' => $coupon->calculateDiscountedPrice($originalPrice),
            'discount_amount' => $coupon->calculateDiscountAmount($originalPrice)
        ];
    }

    /**
     * Kupon kullanımını kaydeder
     */
    public function recordUsage(string $userId, string $couponId): bool
    {
        return $this->couponRepository->recordCouponUsage($userId, $couponId);
    }
}
