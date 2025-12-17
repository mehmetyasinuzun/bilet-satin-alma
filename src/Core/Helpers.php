<?php
namespace App\Core;

/**
 * Helper Functions Class
 * 
 * OOP Prensipleri:
 * - Static Utility Methods: Yardımcı fonksiyonların sınıf içinde organizasyonu
 * - Single Responsibility: Her metod tek bir işlevi yerine getirir
 */
class Helpers
{
    /**
     * XSS koruması için string escape
     */
    public static function escape(?string $str): string
    {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    /**
     * Tarihi formatlar
     */
    public static function formatDate(string $datetime): string
    {
        return date('d.m.Y H:i', strtotime($datetime));
    }

    /**
     * Şu anki tarih/saat
     */
    public static function getCurrentDateTime(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Fiyatı formatlar
     */
    public static function formatPrice(float $price): string
    {
        return number_format($price, 2, ',', '.') . ' ₺';
    }

    /**
     * Benzersiz ID oluşturur
     */
    public static function generateId(string $prefix = ''): string
    {
        return $prefix . ($prefix ? '-' : '') . uniqid();
    }

    /**
     * Şifre hash'ler
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Şifre doğrular
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Email validasyonu
     */
    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * String trim ve sanitize
     */
    public static function sanitize(string $str): string
    {
        return trim(strip_tags($str));
    }
}
