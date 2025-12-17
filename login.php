<?php
/**
 * Giriş Sayfası
 * 
 * OOP Kullanımı:
 * - AuthController: Kimlik doğrulama işlemlerini yönetir
 * - AuthService: Giriş/çıkış iş mantığı (implements AuthServiceInterface)
 * - User Entity: Kullanıcı verilerini temsil eder
 * - Session: Singleton pattern ile oturum yönetimi
 */
require_once 'config.php';

use App\Controllers\AuthController;

// Controller'ı kullan
$authController = new AuthController();
$authController->login();

// Legacy değişkenler (view için)
$error = $GLOBALS['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - Bilet Satın Alma Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Giriş Yap</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?= escape($error) ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">E-posta</label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?= escape($_POST['email'] ?? '') ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p>Hesabınız yok mu? <a href="register.php">Kayıt Ol</a></p>
                        </div>
                        
                        <hr>
                        <div class="alert alert-info">
                            <strong>Test Hesapları:</strong><br>
                            <small>
                                Admin: admin@admin.com / admin123<br>
                                Firma: metro@admin.com / 123456<br>
                                Firma: pamukkale@admin.com / 123456<br>
                                Kullanıcı: user@test.com / 123456
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
