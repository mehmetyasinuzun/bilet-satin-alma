<?php
require_once 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'E-posta ve şifre gereklidir.';
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT id, full_name, email, role, password, company_id, balance FROM User WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Session başlat
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['company_id'] = $user['company_id'];
                $_SESSION['balance'] = $user['balance'];
                
                // Rol bazlı yönlendirme
                switch ($user['role']) {
                    case 'admin':
                        header('Location: admin_dashboard.php');
                        break;
                    case 'company.admin':
                        header('Location: company_admin_dashboard.php');
                        break;
                    default:
                        header('Location: user_dashboard.php');
                }
                exit;
            } else {
                $error = 'E-posta veya şifre hatalı.';
            }
        } catch (PDOException $e) {
            $error = 'Giriş sırasında hata oluştu: ' . $e->getMessage();
        }
    }
}
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
