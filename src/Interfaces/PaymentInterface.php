<?php
namespace App\Interfaces;

/**
 * Payment Gateway Interface
 * 
 * OOP Prensipleri:
 * - Interface: Farklı ödeme yöntemleri için ortak kontrat
 * - Strategy Pattern desteği: Farklı ödeme stratejileri implement edilebilir
 * - Open/Closed Principle: Yeni ödeme yöntemleri eklenebilir
 */
interface PaymentInterface
{
    /**
     * Ödeme işlemini gerçekleştirir
     */
    public function processPayment(float $amount, string $userId): bool;

    /**
     * İade işlemini gerçekleştirir
     */
    public function processRefund(float $amount, string $userId): bool;

    /**
     * Bakiye kontrolü yapar
     */
    public function hasBalance(string $userId, float $amount): bool;

    /**
     * Mevcut bakiyeyi döndürür
     */
    public function getBalance(string $userId): float;
}
