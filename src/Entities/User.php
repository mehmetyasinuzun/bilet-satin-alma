<?php
namespace App\Entities;

use App\Core\Helpers;

/**
 * User Entity Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseEntity'den türer
 * - Encapsulation: Private property'ler, getter/setter metodlar
 * - Polymorphism: validate() metodunu kendi ihtiyaçlarına göre implement eder
 */
class User extends BaseEntity
{
    protected ?string $fullName = null;
    protected ?string $email = null;
    protected ?string $password = null;
    protected string $role = 'user';
    protected ?string $companyId = null;
    protected float $balance = 0.0;

    // Rol sabitleri
    public const ROLE_ADMIN = 'admin';
    public const ROLE_COMPANY_ADMIN = 'company.admin';
    public const ROLE_USER = 'user';

    /**
     * Validasyon kurallarını uygular
     */
    public function validate(): array
    {
        $this->errors = [];

        if (empty($this->fullName)) {
            $this->addError('full_name', 'Ad Soyad gereklidir.');
        }

        if (empty($this->email)) {
            $this->addError('email', 'E-posta gereklidir.');
        } elseif (!Helpers::isValidEmail($this->email)) {
            $this->addError('email', 'Geçerli bir e-posta adresi girin.');
        }

        if (empty($this->password) && $this->id === null) {
            $this->addError('password', 'Şifre gereklidir.');
        } elseif ($this->id === null && strlen($this->password) < 8) {
            $this->addError('password', 'Şifre en az 8 karakter olmalıdır.');
        }

        if (!in_array($this->role, [self::ROLE_ADMIN, self::ROLE_COMPANY_ADMIN, self::ROLE_USER])) {
            $this->addError('role', 'Geçersiz rol.');
        }

        return $this->errors;
    }

    /**
     * Şifreyi hash'ler
     */
    public function hashPassword(): void
    {
        if (!empty($this->password) && !$this->isPasswordHashed()) {
            $this->password = Helpers::hashPassword($this->password);
        }
    }

    /**
     * Şifre zaten hash'lenmiş mi kontrol eder
     */
    private function isPasswordHashed(): bool
    {
        return !empty($this->password) && strlen($this->password) > 50;
    }

    /**
     * Şifre doğrulaması yapar
     */
    public function verifyPassword(string $password): bool
    {
        return Helpers::verifyPassword($password, $this->password);
    }

    /**
     * Admin mi kontrol eder
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Firma Admin mi kontrol eder
     */
    public function isCompanyAdmin(): bool
    {
        return $this->role === self::ROLE_COMPANY_ADMIN;
    }

    /**
     * Normal kullanıcı mı kontrol eder
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Bakiye yeterli mi kontrol eder
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }

    /**
     * Bakiyeden düşer
     */
    public function deductBalance(float $amount): void
    {
        if ($this->hasSufficientBalance($amount)) {
            $this->balance -= $amount;
        }
    }

    /**
     * Bakiye ekler
     */
    public function addBalance(float $amount): void
    {
        $this->balance += $amount;
    }

    // Getter metodları
    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    // Setter metodları
    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function setCompanyId(?string $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }
}
