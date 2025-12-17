<?php
namespace App\Services;

/**
 * Abstract Base Service Class
 * 
 * OOP Prensipleri:
 * - Abstract Class: Tüm service'ler için ortak davranışları tanımlar
 * - Template Method Pattern: validateOrFail gibi ortak metodlar
 * - Inheritance: Child service'ler bu sınıftan türer
 */
abstract class BaseService
{
    protected array $errors = [];

    /**
     * Hata mesajlarını döndürür
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * İlk hata mesajını döndürür
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * Hata var mı kontrol eder
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Hata ekler
     */
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    /**
     * Hataları temizler
     */
    protected function clearErrors(): void
    {
        $this->errors = [];
    }

    /**
     * İşlem başarılı mı (hata yoksa)
     */
    protected function succeed(): bool
    {
        return empty($this->errors);
    }
}
