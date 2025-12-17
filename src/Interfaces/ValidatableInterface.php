<?php
namespace App\Interfaces;

/**
 * Validatable Interface
 * 
 * OOP Prensipleri:
 * - Interface: Validasyon yapılabilir nesneler için kontrat
 * - Polymorphism: Farklı entity'ler aynı validasyon yöntemini kullanır
 */
interface ValidatableInterface
{
    /**
     * Nesneyi validate eder
     * 
     * @return array Hata mesajları dizisi (boşsa geçerli)
     */
    public function validate(): array;

    /**
     * Nesne geçerli mi kontrol eder
     */
    public function isValid(): bool;
}
