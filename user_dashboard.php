<?php
/**
 * Kullanıcı Dashboard
 * 
 * OOP Kullanımı:
 * - UserController: Dashboard işlemlerini yönetir (extends BaseController)
 * - TicketService: Bilet istatistiklerini hesaplar (Composition)
 * - UserRepository: Bakiye bilgisini çeker (Repository Pattern)
 * - Session: Kullanıcı bilgilerini yönetir (Singleton)
 */
require_once 'config.php';

use App\Controllers\UserController;

// Controller'ı kullan
$userController = new UserController();
$data = $userController->dashboard();

// Legacy değişkenler
$activeTickets = $data['activeTickets'];
$cancelledTickets = $data['cancelledTickets'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hesabım - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="bi bi-person-circle"></i> Hesabım</h2>
        
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-wallet2 fs-1 text-success"></i>
                        <h3 class="mt-3"><?= formatPrice($_SESSION['balance']) ?></h3>
                        <p class="text-muted">Hesap Bakiyesi</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-ticket-perforated fs-1 text-primary"></i>
                        <h3 class="mt-3"><?= $activeTickets ?></h3>
                        <p class="text-muted">Aktif Bilet</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle fs-1 text-danger"></i>
                        <h3 class="mt-3"><?= $cancelledTickets ?></h3>
                        <p class="text-muted">İptal Edilen</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Profil Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Ad Soyad:</strong> <?= escape($_SESSION['full_name']) ?></p>
                        <p><strong>E-posta:</strong> <?= escape($_SESSION['email']) ?></p>
                        <p><strong>Kayıt Tarihi:</strong> <?= date('d.m.Y') ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Hızlı Erişim</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Sefer Ara
                            </a>
                            <a href="my_tickets.php" class="btn btn-outline-success">
                                <i class="bi bi-ticket-perforated"></i> Biletlerim
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
