<?php
/**
 * Çıkış İşlemi
 * 
 * OOP Kullanımı:
 * - AuthController: Çıkış işlemini yönetir
 * - Session: Singleton pattern ile oturum yönetimi
 */
require_once 'config.php';

use App\Controllers\AuthController;

$authController = new AuthController();
$authController->logout();
?>
