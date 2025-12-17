<?php
namespace App\Interfaces;

use App\Entities\User;

/**
 * Authentication Service Interface
 * 
 * OOP Prensipleri:
 * - Interface: Kimlik doğrulama kontratı
 * - Dependency Inversion: Üst seviye modüller bu interface'e bağımlıdır
 */
interface AuthServiceInterface
{
    public function login(string $email, string $password): ?User;
    public function logout(): void;
    public function register(array $data): User;
    public function getCurrentUser(): ?User;
    public function isLoggedIn(): bool;
}
