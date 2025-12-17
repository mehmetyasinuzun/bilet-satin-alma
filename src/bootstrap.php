<?php
/**
 * Bootstrap File
 * 
 * Uygulamanın başlatılması için gerekli tüm ayarları yapılandırır.
 * 
 * OOP Prensipleri:
 * - Centralized configuration
 * - Singleton pattern kullanımı (Database, Session)
 */

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone ayarı (Türkiye saati)
date_default_timezone_set('Europe/Istanbul');

// Autoloader
require_once __DIR__ . '/autoload.php';

// Core sınıfları başlat
use App\Core\Database;
use App\Core\Session;
use App\Core\Helpers;

// Session başlat
$session = Session::getInstance();

// Database bağlantısı (lazy load - ilk kullanımda bağlanır)
// Database::getInstance();

// Legacy uyumluluk fonksiyonları
// Eski kodun çalışmaya devam etmesi için geriye uyumlu fonksiyonlar

/**
 * Legacy: Veritabanı bağlantısı döndürür
 */
function getDB(): PDO {
    return Database::getInstance()->getConnection();
}

/**
 * Legacy: Kullanıcı giriş yapmış mı
 */
function isLoggedIn(): bool {
    return Session::getInstance()->isLoggedIn();
}

/**
 * Legacy: Kullanıcı rolünü döndürür
 */
function getRole(): ?string {
    return Session::getInstance()->getRole();
}

/**
 * Legacy: Admin mi kontrol eder
 */
function isAdmin(): bool {
    return Session::getInstance()->hasRole('admin');
}

/**
 * Legacy: Firma Admin mi kontrol eder
 */
function isCompanyAdmin(): bool {
    return Session::getInstance()->hasRole('company.admin');
}

/**
 * Legacy: Normal kullanıcı mı kontrol eder
 */
function isUser(): bool {
    return Session::getInstance()->hasRole('user');
}

/**
 * Legacy: Giriş gerektirir
 */
function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Legacy: Rol gerektirir
 */
function requireRole(string $role): void {
    requireLogin();
    if (getRole() !== $role) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Legacy: XSS koruması
 */
function escape(?string $str): string {
    return Helpers::escape($str);
}

/**
 * Legacy: Tarih formatı
 */
function formatDate(string $datetime): string {
    return Helpers::formatDate($datetime);
}

/**
 * Legacy: Şu anki tarih/saat
 */
function getCurrentDateTime(): string {
    return Helpers::getCurrentDateTime();
}

/**
 * Legacy: Fiyat formatı
 */
function formatPrice(float $price): string {
    return Helpers::formatPrice($price);
}

// Veritabanı yolu sabiti (legacy uyumluluk)
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
define('DB_PATH', $dataDir . '/database.sqlite');
