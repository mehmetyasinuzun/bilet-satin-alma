<?php
/**
 * Bilet Satın Alma Sayfası
 * 
 * OOP Kullanımı:
 * - TicketService: Bilet satın alma işlemi (Facade Pattern)
 *   - Birden fazla Repository'yi koordine eder
 *   - Transaction yönetimi yapar
 * - TripService: Sefer bilgilerini getirir
 * - CouponService: Kupon doğrulama ve uygulama
 * - Trip Entity: Sefer verilerini temsil eder
 * - Ticket Entity: Bilet verilerini temsil eder
 * - WalletPaymentService: Ödeme işlemleri (implements PaymentInterface)
 */
require_once 'config.php';
requireRole('user');

use App\Services\TicketService;
use App\Services\TripService;
use App\Repositories\UserRepository;

$tripId = $_GET['trip_id'] ?? null;
$error = '';
$success = '';

if (!$tripId) {
    header('Location: index.php');
    exit;
}

// Service'leri kullan
$tripService = new TripService();
$ticketService = new TicketService();

// Sefer bilgilerini al
$trip = $tripService->getTripDetails($tripId);

if (!$trip) {
    header('Location: index.php');
    exit;
}

$availableSeats = $trip->getAvailableSeats();

// Dolu koltukları al
$bookedSeatsData = $ticketService->getBookedSeats($tripId);

// Bilet satın alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seatNumber = (int)($_POST['seat_number'] ?? 0);
    $couponCode = trim($_POST['coupon_code'] ?? '');
    
    $ticket = $ticketService->purchaseTicket(
        $_SESSION['user_id'],
        $tripId,
        $seatNumber,
        $couponCode ?: null
    );
    
    if ($ticket) {
        // Session bakiyesini güncelle
        $userRepo = new UserRepository();
        $_SESSION['balance'] = $userRepo->getBalance($_SESSION['user_id']);
        
        header('Location: my_tickets.php?success=1');
        exit;
    } else {
        $error = $ticketService->getFirstError();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al - <?= escape($trip->getCompanyName()) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .seat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            max-width: 400px;
            margin: 0 auto;
        }
        .seat {
            aspect-ratio: 1;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .seat:hover:not(.booked):not(.selected) {
            border-color: #0d6efd;
            background: #e7f1ff;
        }
        .seat.selected {
            background: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }
        .seat.booked {
            background: #dc3545;
            color: white;
            cursor: not-allowed;
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <h2 class="mb-4"><i class="bi bi-ticket-perforated"></i> Bilet Satın Al</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= escape($error) ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Sefer Bilgileri</h5>
                    </div>
                    <div class="card-body">
                        <h6><?= escape($trip->getCompanyName()) ?></h6>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-muted small">Kalkış</div>
                                <div class="fw-bold"><?= escape($trip->getDepartureCity()) ?></div>
                                <div class="text-primary"><?= formatDate($trip->getDepartureTime()) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Varış</div>
                                <div class="fw-bold"><?= escape($trip->getDestinationCity()) ?></div>
                                <div class="text-primary"><?= formatDate($trip->getArrivalTime()) ?></div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Bilet Fiyatı:</span>
                            <strong class="text-success"><?= formatPrice($trip->getPrice()) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Mevcut Bakiyeniz:</span>
                            <strong><?= formatPrice($_SESSION['balance']) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Koltuk Seçimi</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center">
                            <small>
                                <span class="badge bg-success">Boş</span>
                                <span class="badge bg-danger">Dolu</span>
                                <span class="badge bg-primary">Seçili</span>
                            </small>
                        </div>
                        
                        <form method="POST" id="ticketForm">
                            <input type="hidden" name="seat_number" id="seatNumberInput">
                            
                            <div class="seat-grid mb-4">
                                <?php for ($i = 1; $i <= $trip->getCapacity(); $i++): ?>
                                    <?php $isBooked = in_array($i, $bookedSeatsData); ?>
                                    <div class="seat <?= $isBooked ? 'booked' : '' ?>" 
                                         data-seat="<?= $i ?>"
                                         <?= $isBooked ? 'title="Dolu"' : '' ?>>
                                        <?= $i ?>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Kupon Kodu (Opsiyonel)</label>
                                <input type="text" name="coupon_code" class="form-control" 
                                       placeholder="İndirim kuponunuz varsa girin">
                                <small class="text-muted">Örn: WELCOME10, METRO20</small>
                            </div>
                            
                            <button type="submit" class="btn btn-success w-100" id="purchaseBtn" disabled>
                                <i class="bi bi-credit-card"></i> Bilet Satın Al
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const seats = document.querySelectorAll('.seat:not(.booked)');
        const seatNumberInput = document.getElementById('seatNumberInput');
        const purchaseBtn = document.getElementById('purchaseBtn');
        
        seats.forEach(seat => {
            seat.addEventListener('click', function() {
                // Önceki seçimi temizle
                seats.forEach(s => s.classList.remove('selected'));
                
                // Yeni seçimi işaretle
                this.classList.add('selected');
                const seatNum = this.dataset.seat;
                seatNumberInput.value = seatNum;
                purchaseBtn.disabled = false;
            });
        });
    </script>
</body>
</html>
