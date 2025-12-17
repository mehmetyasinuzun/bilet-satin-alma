<?php
/**
 * Kayıt Sayfası
 * 
 * OOP Kullanımı:
 * - AuthController: Kayıt işlemlerini yönetir
 * - AuthService: Kayıt iş mantığı
 * - User Entity: Validasyon kurallarını içerir (ValidatableInterface)
 * - UserRepository: Veritabanı işlemleri (extends BaseRepository)
 */
require_once 'config.php';

use App\Controllers\AuthController;

// Controller'ı kullan
$authController = new AuthController();
$authController->register();

// Legacy değişkenler (view için)
$error = $GLOBALS['error'] ?? '';
$success = $GLOBALS['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Bilet Satın Alma Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Kayıt Ol</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= escape($error) ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?= escape($success) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Ad Soyad</label>
                                <input type="text" name="full_name" class="form-control" required 
                                       value="<?= escape($_POST['full_name'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?= escape($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre (en az 8 karakter)</label>
                                <input type="password" name="password" class="form-control" required minlength="8">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre Tekrar</label>
                                <input type="password" name="confirm_password" class="form-control" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Zaten hesabınız var mı? <a href="login.php">Giriş Yap</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
