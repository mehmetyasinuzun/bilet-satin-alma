<?php
namespace App\Interfaces;

/**
 * Repository Interface
 * 
 * OOP Prensipleri:
 * - Interface Segregation: CRUD operasyonları için temel kontrat
 * - Polymorphism: Farklı entity'ler için aynı interface'i implement eder
 * - Dependency Inversion: Üst seviye modüller bu interface'e bağımlıdır
 */
interface RepositoryInterface
{
    /**
     * ID ile kayıt bulur
     */
    public function findById(string $id): ?array;

    /**
     * Tüm kayıtları döndürür
     */
    public function findAll(): array;

    /**
     * Yeni kayıt oluşturur
     */
    public function create(array $data): string;

    /**
     * Kayıt günceller
     */
    public function update(string $id, array $data): bool;

    /**
     * Kayıt siler
     */
    public function delete(string $id): bool;
}
