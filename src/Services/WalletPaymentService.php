<?php
namespace App\Services;

use App\Repositories\UserRepository;
use App\Interfaces\PaymentInterface;

/**
 * Wallet Payment Service Class
 * 
 * OOP Prensipleri:
 * - Interface Implementation: PaymentInterface'i implement eder
 * - Inheritance: BaseService'den türer
 * - Strategy Pattern: Farklı ödeme yöntemleri için değiştirilebilir
 * - Open/Closed Principle: Yeni ödeme yöntemleri eklenebilir
 */
class WalletPaymentService extends BaseService implements PaymentInterface
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    /**
     * Ödeme işlemini gerçekleştirir
     */
    public function processPayment(float $amount, string $userId): bool
    {
        $this->clearErrors();

        if (!$this->hasBalance($userId, $amount)) {
            $this->addError('Yetersiz bakiye.');
            return false;
        }

        return $this->userRepository->deductBalance($userId, $amount);
    }

    /**
     * İade işlemini gerçekleştirir
     */
    public function processRefund(float $amount, string $userId): bool
    {
        $this->clearErrors();

        return $this->userRepository->addBalance($userId, $amount);
    }

    /**
     * Bakiye kontrolü yapar
     */
    public function hasBalance(string $userId, float $amount): bool
    {
        return $this->getBalance($userId) >= $amount;
    }

    /**
     * Mevcut bakiyeyi döndürür
     */
    public function getBalance(string $userId): float
    {
        return $this->userRepository->getBalance($userId);
    }

    /**
     * Bakiye ekler (şarj işlemi)
     */
    public function addFunds(string $userId, float $amount): bool
    {
        $this->clearErrors();

        if ($amount <= 0) {
            $this->addError('Tutar pozitif olmalıdır.');
            return false;
        }

        return $this->userRepository->addBalance($userId, $amount);
    }
}
