<?php
/**
 * Ana Sayfa - Sefer Listesi
 * 
 * OOP Kullanımı:
 * - TripController: Sefer işlemlerini yönetir (Controller Pattern)
 * - TripService: İş mantığını içerir (Service Layer)
 * - TripRepository: Veritabanı erişimi (Repository Pattern)
 */
require_once 'config.php';

use App\Controllers\TripController;

// Controller'ı kullan
$tripController = new TripController();
$data = $tripController->index();

// Değişkenleri çıkar
$trips = $data['trips'];
$cities = $data['cities'];
$departureCity = $data['departureCity'];
$destinationCity = $data['destinationCity'];
$departureDate = $data['departureDate'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Alma Platformu - Otobüs Bileti</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <div class="hero-section bg-primary text-white py-5">
        <div class="container">
            <h1 class="display-4 text-center mb-4">Otobüs Bileti Al</h1>
            
            <!-- Arama Formu -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kalkış Yeri</label>
                            <input type="text" name="departure_city" class="form-control" 
                                   list="departure-cities" value="<?= escape($departureCity) ?>" 
                                   placeholder="Örn: İstanbul">
                            <datalist id="departure-cities">
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= escape($city) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Varış Yeri</label>
                            <input type="text" name="destination_city" class="form-control" 
                                   list="destination-cities" value="<?= escape($destinationCity) ?>" 
                                   placeholder="Örn: Ankara">
                            <datalist id="destination-cities">
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= escape($city) ?>">
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tarih</label>
                            <input type="date" name="departure_date" class="form-control" 
                                   value="<?= escape($departureDate) ?>" 
                                   min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-search"></i> Sefer Ara
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sefer Listesi -->
    <div class="container my-5">
        <h2 class="mb-4">
            <?php if ($departureCity || $destinationCity || $departureDate): ?>
                Arama Sonuçları (<?= count($trips) ?> sefer bulundu)
            <?php else: ?>
                Tüm Seferler
            <?php endif; ?>
        </h2>
        
        <?php if (empty($trips)): ?>
            <div class="alert alert-warning">
                <i class="bi bi-info-circle"></i> Aradığınız kriterlere uygun sefer bulunamadı.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($trips as $trip): ?>
                    <?php 
                        $availableSeats = $trip['capacity'] - $trip['booked_seats'];
                        $isFull = $availableSeats <= 0;
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 <?= $isFull ? 'border-danger' : '' ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-bus-front"></i> <?= escape($trip['company_name']) ?>
                                    </h5>
                                    <span class="badge bg-primary"><?= formatPrice($trip['price']) ?></span>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-5">
                                        <div class="text-muted small">Kalkış</div>
                                        <div class="fw-bold"><?= escape($trip['departure_city']) ?></div>
                                        <div class="text-primary"><?= formatDate($trip['departure_time']) ?></div>
                                    </div>
                                    <div class="col-2 text-center">
                                        <i class="bi bi-arrow-right fs-4"></i>
                                    </div>
                                    <div class="col-5">
                                        <div class="text-muted small">Varış</div>
                                        <div class="fw-bold"><?= escape($trip['destination_city']) ?></div>
                                        <div class="text-primary"><?= formatDate($trip['arrival_time']) ?></div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="<?= $availableSeats < 10 ? 'text-danger' : 'text-success' ?>">
                                        <i class="bi bi-people"></i> 
                                        <?= $availableSeats ?> boş koltuk
                                    </span>
                                    
                                    <?php if ($isFull): ?>
                                        <button class="btn btn-secondary" disabled>Dolu</button>
                                    <?php elseif (isUser()): ?>
                                        <a href="ticket_purchase.php?trip_id=<?= $trip['id'] ?>" 
                                           class="btn btn-success">
                                            <i class="bi bi-ticket"></i> Bilet Al
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-primary">
                                            Giriş Yapın
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
