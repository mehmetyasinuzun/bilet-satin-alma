<?php
// Timezone ayarı (Türkiye saati)
date_default_timezone_set('Europe/Istanbul');

// Veritabanı yapılandırması
// Docker ortamında data klasörünü kullan, yoksa mevcut dizini kullan
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
define('DB_PATH', $dataDir . '/database.sqlite');

// Güvenlik ayarları
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // localhost için 0, production'da 1 yapın

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı bağlantısı
function getDB() {
    try {
        $db = new PDO('sqlite:' . DB_PATH);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db;
    } catch (PDOException $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}

// Kullanıcı rolü kontrol fonksiyonları
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getRole() {
    return $_SESSION['role'] ?? null;
}

function isAdmin() {
    return isLoggedIn() && getRole() === 'admin';
}

function isCompanyAdmin() {
    return isLoggedIn() && getRole() === 'company.admin';
}

function isUser() {
    return isLoggedIn() && getRole() === 'user';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireRole($role) {
    requireLogin();
    if (getRole() !== $role) {
        header('Location: index.php');
        exit;
    }
}

// XSS koruması
function escape($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Tarih formatı
function formatDate($datetime) {
    return date('d.m.Y H:i', strtotime($datetime));
}

// Şu anki Türkiye saatini al (yeni kayıtlar için)
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

function formatPrice($price) {
    return number_format($price, 2, ',', '.') . ' ₺';
}
?>
