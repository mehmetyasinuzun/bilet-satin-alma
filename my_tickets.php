<?php
require_once 'config.php';
requireRole('user');

$db = getDB();
$success = $_GET['success'] ?? null;

// Kullanıcının biletlerini al
$stmt = $db->prepare("SELECT t.*, 
                      tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time,
                      bc.name as company_name
                      FROM Tickets t
                      JOIN Trips tr ON t.trip_id = tr.id
                      JOIN Bus_Company bc ON tr.company_id = bc.id
                      WHERE t.user_id = ?
                      ORDER BY t.created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$tickets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biletlerim - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="bi bi-ticket-perforated"></i> Biletlerim</h2>
        
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle"></i> Bilet satın alma işleminiz başarıyla tamamlandı!
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (empty($tickets)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> Henüz hiç biletiniz yok.
                <a href="index.php" class="alert-link">Sefer aramak için tıklayın.</a>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($tickets as $ticket): ?>
                    <?php 
                        $isActive = $ticket['status'] === 'active';
                        $departureTime = strtotime($ticket['departure_time']);
                        $currentTime = time();
                        $hoursDiff = ($departureTime - $currentTime) / 3600;
                        $canCancel = $isActive && $hoursDiff > 1;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card <?= $isActive ? 'border-success' : 'border-danger' ?>">
                            <div class="card-header <?= $isActive ? 'bg-success' : 'bg-danger' ?> text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="bi bi-bus-front"></i> <?= escape($ticket['company_name']) ?>
                                    </h6>
                                    <span class="badge bg-light text-dark">
                                        Koltuk: <?= $ticket['seat_number'] ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <small class="text-muted">Kalkış</small>
                                        <div class="fw-bold"><?= escape($ticket['departure_city']) ?></div>
                                        <small class="text-primary"><?= formatDate($ticket['departure_time']) ?></small>
                                    </div>
                                    <div class="col-6">
                                        <small class="text-muted">Varış</small>
                                        <div class="fw-bold"><?= escape($ticket['destination_city']) ?></div>
                                        <small class="text-primary"><?= formatDate($ticket['arrival_time']) ?></small>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Ücret:</span>
                                    <strong class="text-success"><?= formatPrice($ticket['total_price']) ?></strong>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-3">
                                    <span>Durum:</span>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-success">Aktif</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">İptal Edildi</span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <?php if ($isActive): ?>
                                        <a href="generate_pdf.php?ticket_id=<?= $ticket['id'] ?>" 
                                           class="btn btn-primary btn-sm" target="_blank">
                                            <i class="bi bi-file-pdf"></i> PDF İndir
                                        </a>
                                        
                                        <?php if ($canCancel): ?>
                                            <button class="btn btn-danger btn-sm" 
                                                    onclick="confirmCancel('<?= $ticket['id'] ?>')">
                                                <i class="bi bi-x-circle"></i> Bileti İptal Et
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm" disabled>
                                                <i class="bi bi-info-circle"></i> İptal Süresi Geçti
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Satın Alma: <?= formatDate($ticket['created_at']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmCancel(ticketId) {
            if (confirm('Bileti iptal etmek istediğinizden emin misiniz? İade tutarı hesabınıza yansıtılacaktır.')) {
                window.location.href = 'cancel_ticket.php?ticket_id=' + ticketId;
            }
        }
    </script>
</body>
</html>
