<?php
namespace App\Entities;

/**
 * Coupon Entity Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseEntity'den türer
 * - Encapsulation: Property'ler protected, getter/setter ile erişim
 */
class Coupon extends BaseEntity
{
    protected ?string $code = null;
    protected float $discount = 0.0;
    protected ?string $companyId = null;
    protected ?int $usageLimit = null;
    protected ?string $expireDate = null;
    
    // İlişkili veriler
    protected ?string $companyName = null;
    protected int $usageCount = 0;

    /**
     * Validasyon kurallarını uygular
     */
    public function validate(): array
    {
        $this->errors = [];

        if (empty($this->code)) {
            $this->addError('code', 'Kupon kodu gereklidir.');
        }

        if ($this->discount <= 0 || $this->discount > 100) {
            $this->addError('discount', 'İndirim oranı 1-100 arasında olmalıdır.');
        }

        return $this->errors;
    }

    /**
     * Kupon geçerli mi kontrol eder
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isLimitReached();
    }

    /**
     * Kupon süresi dolmuş mu kontrol eder
     */
    public function isExpired(): bool
    {
        if (empty($this->expireDate)) {
            return false;
        }
        return strtotime($this->expireDate) < time();
    }

    /**
     * Kullanım limiti dolmuş mu kontrol eder
     */
    public function isLimitReached(): bool
    {
        if ($this->usageLimit === null) {
            return false;
        }
        return $this->usageCount >= $this->usageLimit;
    }

    /**
     * Belirli firma için geçerli mi kontrol eder
     */
    public function isValidForCompany(?string $companyId): bool
    {
        if ($this->companyId === null) {
            return true; // Tüm firmalar için geçerli
        }
        return $this->companyId === $companyId;
    }

    /**
     * İndirimli fiyatı hesaplar
     */
    public function calculateDiscountedPrice(float $price): float
    {
        return $price * (1 - $this->discount / 100);
    }

    /**
     * İndirim miktarını hesaplar
     */
    public function calculateDiscountAmount(float $price): float
    {
        return $price * ($this->discount / 100);
    }

    // Getter metodları
    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getDiscount(): float
    {
        return $this->discount;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function getUsageLimit(): ?int
    {
        return $this->usageLimit;
    }

    public function getExpireDate(): ?string
    {
        return $this->expireDate;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getUsageCount(): int
    {
        return $this->usageCount;
    }

    // Setter metodları
    public function setCode(string $code): void
    {
        $this->code = strtoupper(trim($code));
    }

    public function setDiscount(float $discount): void
    {
        $this->discount = $discount;
    }

    public function setCompanyId(?string $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function setUsageLimit(?int $limit): void
    {
        $this->usageLimit = $limit;
    }

    public function setExpireDate(?string $date): void
    {
        $this->expireDate = $date;
    }
}
