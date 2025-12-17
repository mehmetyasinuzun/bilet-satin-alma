<?php
/**
 * Firma Admin Dashboard
 * 
 * OOP Kullanımı:
 * - CompanyAdminController: Firma admin işlemlerini yönetir (extends BaseController)
 * - TripService: Sefer CRUD işlemleri (Service Layer)
 * - BusCompanyService: Firma bilgileri
 * - Trip Entity: Sefer verilerini temsil eder
 * - BusCompany Entity: Firma verilerini temsil eder
 */
require_once 'config.php';

use App\Controllers\CompanyAdminController;
use App\Core\Helpers;

// Controller'ı kullan
$companyAdminController = new CompanyAdminController();
$data = $companyAdminController->dashboard();

// Legacy değişkenler
$error = $data['error'];
$success = $data['success'];
$company = $data['company'];
$trips = $data['trips'];

// İstatistikler
$stats = $data['stats'];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Firma Admin Panel - <?= escape($company->getName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-building"></i> Firma Admin Panel - <?= escape($company->getName()) ?></h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTripModal">
                <i class="bi bi-plus-circle"></i> Yeni Sefer Ekle
            </button>
        </div>
        
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
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_trips'] ?></h3>
                        <p class="mb-0">Toplam Sefer</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3><?= $stats['total_tickets'] ?></h3>
                        <p class="mb-0">Satılan Bilet</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3><?= Helpers::formatPrice($stats['total_revenue']) ?></h3>
                        <p class="mb-0">Toplam Gelir</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sefer Listesi -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Seferler</h5>
            </div>
            <div class="card-body">
                <?php if (empty($trips)): ?>
                    <div class="alert alert-info">Henüz sefer eklenmemiş.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kalkış</th>
                                    <th>Varış</th>
                                    <th>Tarih/Saat</th>
                                    <th>Fiyat</th>
                                    <th>Kapasite</th>
                                    <th>Dolu</th>
                                    <th>İşlemler</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($trips as $trip): ?>
                                    <tr>
                                        <td><?= escape($trip['departure_city']) ?></td>
                                        <td><?= escape($trip['destination_city']) ?></td>
                                        <td>
                                            <small>Kalkış: <?= formatDate($trip['departure_time']) ?></small><br>
                                            <small>Varış: <?= formatDate($trip['arrival_time']) ?></small>
                                        </td>
                                        <td><?= formatPrice($trip['price']) ?></td>
                                        <td><?= $trip['capacity'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $trip['booked_seats'] >= $trip['capacity'] ? 'danger' : 'success' ?>">
                                                <?= $trip['booked_seats'] ?> / <?= $trip['capacity'] ?>
                                            </span>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;" 
                                                  onsubmit="return confirm('Bu seferi silmek istediğinizden emin misiniz?')">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="trip_id" value="<?= $trip['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="bi bi-trash"></i> Sil
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Yeni Sefer Ekleme Modal -->
    <div class="modal fade" id="addTripModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Yeni Sefer Ekle</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Kalkış Şehri</label>
                            <input type="text" name="departure_city" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Varış Şehri</label>
                            <input type="text" name="destination_city" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kalkış Zamanı</label>
                            <input type="datetime-local" name="departure_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Varış Zamanı</label>
                            <input type="datetime-local" name="arrival_time" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fiyat (₺)</label>
                            <input type="number" name="price" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kapasite (Koltuk Sayısı)</label>
                            <input type="number" name="capacity" class="form-control" min="1" max="60" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Sefer Ekle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
