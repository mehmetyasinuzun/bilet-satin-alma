<?php
require_once 'config.php';
requireRole('admin');

$db = getDB();
$error = '';
$success = '';

// Firma ekleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_company':
            $companyName = trim($_POST['company_name'] ?? '');
            if (empty($companyName)) {
                $error = 'Firma adı gereklidir.';
            } else {
                try {
                    $companyId = 'company-' . uniqid();
                    $stmt = $db->prepare("INSERT INTO Bus_Company (id, name) VALUES (?, ?)");
                    $stmt->execute([$companyId, $companyName]);
                    $_SESSION['success_message'] = 'Firma başarıyla eklendi.';
                    header('Location: admin_dashboard.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Firma eklenirken hata: ' . $e->getMessage();
                }
            }
            break;
            
        case 'delete_company':
            $companyId = $_POST['company_id'] ?? '';
            try {
                $stmt = $db->prepare("DELETE FROM Bus_Company WHERE id = ?");
                $stmt->execute([$companyId]);
                $_SESSION['success_message'] = 'Firma silindi.';
                header('Location: admin_dashboard.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Firma silinemedi: ' . $e->getMessage();
            }
            break;
            
        case 'add_company_admin':
            $fullName = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $companyId = $_POST['company_id'] ?? '';
            
            if (empty($fullName) || empty($email) || empty($password) || empty($companyId)) {
                $error = 'Tüm alanları doldurun.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Geçerli e-posta girin.';
            } elseif (strlen($password) < 8) {
                $error = 'Şifre en az 8 karakter olmalıdır.';
            } else {
                try {
                    $userId = 'user-' . uniqid();
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $currentDateTime = getCurrentDateTime();
                    $stmt = $db->prepare("INSERT INTO User (id, full_name, email, password, role, company_id, balance, created_at) 
                                          VALUES (?, ?, ?, ?, 'company.admin', ?, 0, ?)");
                    $stmt->execute([$userId, $fullName, $email, $hashedPassword, $companyId, $currentDateTime]);
                    $_SESSION['success_message'] = 'Firma Admin oluşturuldu.';
                    header('Location: admin_dashboard.php');
                    exit;
                } catch (PDOException $e) {
                    $error = 'Kullanıcı oluşturulamadı: ' . $e->getMessage();
                }
            }
            break;
            
        case 'add_coupon':
            $code = strtoupper(trim($_POST['coupon_code'] ?? ''));
            $discount = (float)($_POST['discount'] ?? 0);
            $companyId = $_POST['company_id'] ?? null;
            $usageLimit = (int)($_POST['usage_limit'] ?? 0);
            $expireDate = $_POST['expire_date'] ?? '';
            
            if (empty($code) || $discount <= 0 || $discount > 100) {
                $error = 'Kupon kodu ve geçerli indirim oranı (1-100) gereklidir.';
            } else {
                try {
                    $couponId = 'coupon-' . uniqid();
                    $currentDateTime = getCurrentDateTime();
                    $stmt = $db->prepare("INSERT INTO Coupons (id, code, discount, company_id, usage_limit, expire_date, created_at) 
                                          VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $couponId, 
                        $code, 
                        $discount, 
                        $companyId ?: null, 
                        $usageLimit > 0 ? $usageLimit : null, 
                        $expireDate ?: null,
                        $currentDateTime
                    ]);
                    $_SESSION['success_message'] = 'Kupon oluşturuldu.';
                    header('Location: admin_dashboard.php');
                    exit;
                } catch (PDOException $e) {
                    if (strpos($e->getMessage(), 'UNIQUE constraint failed') !== false) {
                        $error = 'Bu kupon kodu zaten kullanılıyor. Farklı bir kod deneyin.';
                    } else {
                        $error = 'Kupon oluşturulamadı: ' . $e->getMessage();
                    }
                }
            }
            break;
            
        case 'delete_coupon':
            $couponId = $_POST['coupon_id'] ?? '';
            try {
                $stmt = $db->prepare("DELETE FROM Coupons WHERE id = ?");
                $stmt->execute([$couponId]);
                $_SESSION['success_message'] = 'Kupon silindi.';
                header('Location: admin_dashboard.php');
                exit;
            } catch (PDOException $e) {
                $error = 'Kupon silinemedi: ' . $e->getMessage();
            }
            break;
    }
}

// Session'dan mesajları al ve temizle
if (isset($_SESSION['success_message'])) {
    $success = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Firmalar
$companies = $db->query("SELECT c.*, 
                         (SELECT COUNT(*) FROM User u WHERE u.company_id = c.id AND u.role = 'company.admin') as admin_count,
                         (SELECT COUNT(*) FROM Trips t WHERE t.company_id = c.id) as trip_count
                         FROM Bus_Company c
                         ORDER BY c.name")->fetchAll();

// Firma Adminleri
$companyAdmins = $db->query("SELECT u.*, bc.name as company_name
                             FROM User u
                             LEFT JOIN Bus_Company bc ON u.company_id = bc.id
                             WHERE u.role = 'company.admin'
                             ORDER BY u.full_name")->fetchAll();

// Kuponlar
$coupons = $db->query("SELECT c.*, bc.name as company_name,
                       (SELECT COUNT(*) FROM User_Coupons uc WHERE uc.coupon_id = c.id) as usage_count
                       FROM Coupons c
                       LEFT JOIN Bus_Company bc ON c.company_id = bc.id
                       ORDER BY c.created_at DESC")->fetchAll();

// İstatistikler
$stats = [
    'total_users' => $db->query("SELECT COUNT(*) FROM User WHERE role = 'user'")->fetchColumn(),
    'total_companies' => count($companies),
    'total_trips' => $db->query("SELECT COUNT(*) FROM Trips")->fetchColumn(),
    'total_tickets' => $db->query("SELECT COUNT(*) FROM Tickets WHERE status = 'active'")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="bi bi-speedometer2"></i> Admin Panel</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= escape($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= escape($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- İstatistikler -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_companies'] ?></h3>
                        <p class="mb-0">Firma</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_users'] ?></h3>
                        <p class="mb-0">Kullanıcı</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_trips'] ?></h3>
                        <p class="mb-0">Sefer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_tickets'] ?></h3>
                        <p class="mb-0">Aktif Bilet</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tabs -->
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#companies">
                    <i class="bi bi-building"></i> Firmalar
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#admins">
                    <i class="bi bi-people"></i> Firma Adminleri
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#coupons">
                    <i class="bi bi-tag"></i> Kuponlar
                </button>
            </li>
        </ul>
        
        <div class="tab-content">
            <!-- Firmalar Tab -->
            <div class="tab-pane fade show active" id="companies">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Firmalar</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCompanyModal">
                        <i class="bi bi-plus-circle"></i> Yeni Firma
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Firma Adı</th>
                                <th>Admin Sayısı</th>
                                <th>Sefer Sayısı</th>
                                <th>Kayıt Tarihi</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companies as $company): ?>
                                <tr>
                                    <td><?= escape($company['name']) ?></td>
                                    <td><?= $company['admin_count'] ?></td>
                                    <td><?= $company['trip_count'] ?></td>
                                    <td><?= formatDate($company['created_at']) ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Firmayı silmek istediğinizden emin misiniz?')">
                                            <input type="hidden" name="action" value="delete_company">
                                            <input type="hidden" name="company_id" value="<?= $company['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Firma Adminleri Tab -->
            <div class="tab-pane fade" id="admins">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Firma Adminleri</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                        <i class="bi bi-plus-circle"></i> Yeni Admin
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Ad Soyad</th>
                                <th>E-posta</th>
                                <th>Firma</th>
                                <th>Kayıt Tarihi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($companyAdmins as $admin): ?>
                                <tr>
                                    <td><?= escape($admin['full_name']) ?></td>
                                    <td><?= escape($admin['email']) ?></td>
                                    <td><?= escape($admin['company_name']) ?></td>
                                    <td><?= formatDate($admin['created_at']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Kuponlar Tab -->
            <div class="tab-pane fade" id="coupons">
                <div class="d-flex justify-content-between mb-3">
                    <h4>Kuponlar</h4>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCouponModal">
                        <i class="bi bi-plus-circle"></i> Yeni Kupon
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Kod</th>
                                <th>İndirim</th>
                                <th>Firma</th>
                                <th>Kullanım</th>
                                <th>Limit</th>
                                <th>Son Kullanma</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($coupons as $coupon): ?>
                                <tr>
                                    <td><code><?= escape($coupon['code']) ?></code></td>
                                    <td>%<?= $coupon['discount'] ?></td>
                                    <td><?= $coupon['company_name'] ? escape($coupon['company_name']) : '<span class="badge bg-info">Tüm Firmalar</span>' ?></td>
                                    <td><?= $coupon['usage_count'] ?></td>
                                    <td><?= $coupon['usage_limit'] ?: '∞' ?></td>
                                    <td><?= $coupon['expire_date'] ? formatDate($coupon['expire_date']) : '∞' ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Kuponu silmek istediğinizden emin misiniz?')">
                                            <input type="hidden" name="action" value="delete_coupon">
                                            <input type="hidden" name="coupon_id" value="<?= $coupon['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">Sil</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modals -->
    <!-- Add Company Modal -->
    <div class="modal fade" id="addCompanyModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_company">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Firma Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Firma Adı</label>
                            <input type="text" name="company_name" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_company_admin">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Firma Admin Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Ad Soyad</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-posta</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şifre (en az 8 karakter)</label>
                            <input type="password" name="password" class="form-control" required minlength="8">
                            <small class="text-muted">Şifre en az 8 karakter uzunluğunda olmalıdır.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firma</label>
                            <select name="company_id" class="form-control" required>
                                <option value="">Seçin...</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= escape($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Add Coupon Modal -->
    <div class="modal fade" id="addCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add_coupon">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Kupon Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kupon Kodu</label>
                            <input type="text" name="coupon_code" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">İndirim Oranı (%)</label>
                            <input type="number" name="discount" class="form-control" min="1" max="100" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Firma (Opsiyonel - Boş bırakılırsa tüm firmalar için geçerli)</label>
                            <select name="company_id" class="form-control">
                                <option value="">Tüm Firmalar</option>
                                <?php foreach ($companies as $company): ?>
                                    <option value="<?= $company['id'] ?>"><?= escape($company['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kullanım Limiti (Boş = Sınırsız)</label>
                            <input type="number" name="usage_limit" class="form-control" min="1">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Son Kullanma Tarihi (Opsiyonel)</label>
                            <input type="datetime-local" name="expire_date" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
