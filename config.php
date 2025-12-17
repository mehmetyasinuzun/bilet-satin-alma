<?php
/**
 * Configuration File
 * 
 * Bu dosya artık bootstrap.php'yi içerir.
 * Tüm OOP yapılandırması ve legacy uyumluluk fonksiyonları
 * bootstrap.php dosyasında tanımlanmıştır.
 * 
 * OOP Mimarisi:
 * - Autoloader (PSR-4 uyumlu)
 * - Singleton Pattern (Database, Session)
 * - Geriye uyumluluk için legacy fonksiyonlar
 */

require_once __DIR__ . '/src/bootstrap.php';
?>
