<?php
require_once 'config.php';
requireRole('user');

$tripId = $_GET['trip_id'] ?? null;
$error = '';
$success = '';

if (!$tripId) {
    header('Location: index.php');
    exit;
}

$db = getDB();

// Sefer bilgilerini al
$stmt = $db->prepare("SELECT t.*, bc.name as company_name,
                      (SELECT COUNT(*) FROM Booked_Seats bs WHERE bs.trip_id = t.id) as booked_seats
                      FROM Trips t
                      JOIN Bus_Company bc ON t.company_id = bc.id
                      WHERE t.id = ?");
$stmt->execute([$tripId]);
$trip = $stmt->fetch();

if (!$trip) {
    header('Location: index.php');
    exit;
}

$availableSeats = $trip['capacity'] - $trip['booked_seats'];

// Dolu koltukları al
$stmt = $db->prepare("SELECT seat_number FROM Booked_Seats WHERE trip_id = ?");
$stmt->execute([$tripId]);
$bookedSeatsData = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Bilet satın alma işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seatNumber = (int)($_POST['seat_number'] ?? 0);
    $couponCode = trim($_POST['coupon_code'] ?? '');
    
    if ($seatNumber < 1 || $seatNumber > $trip['capacity']) {
        $error = 'Geçersiz koltuk numarası.';
    } elseif (in_array($seatNumber, $bookedSeatsData)) {
        $error = 'Bu koltuk zaten dolu.';
    } else {
        $db->beginTransaction();
        
        try {
            // Kullanıcı bakiyesini kontrol et
            $stmt = $db->prepare("SELECT balance FROM User WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $currentBalance = $stmt->fetch()['balance'];
            
            $totalPrice = $trip['price'];
            $discount = 0;
            $couponId = null;
            
            // Kupon kontrolü
            if (!empty($couponCode)) {
                $stmt = $db->prepare("SELECT c.id, c.discount, c.company_id, c.usage_limit, c.expire_date,
                                      (SELECT COUNT(*) FROM User_Coupons uc WHERE uc.coupon_id = c.id) as usage_count
                                      FROM Coupons c
                                      WHERE c.code = ?");
                $stmt->execute([$couponCode]);
                $coupon = $stmt->fetch();
                
                if ($coupon) {
                    // Kupon geçerlilik kontrolleri
                    $isExpired = strtotime($coupon['expire_date']) < time();
                    $isLimitReached = $coupon['usage_limit'] && $coupon['usage_count'] >= $coupon['usage_limit'];
                    $isCompanyMismatch = $coupon['company_id'] && $coupon['company_id'] !== $trip['company_id'];
                    
                    // Kullanıcı daha önce bu kuponu kullanmış mı?
                    $stmt = $db->prepare("SELECT id FROM User_Coupons WHERE user_id = ? AND coupon_id = ?");
                    $stmt->execute([$_SESSION['user_id'], $coupon['id']]);
                    $alreadyUsed = $stmt->fetch();
                    
                    if ($isExpired) {
                        $error = 'Kupon süresi dolmuş.';
                    } elseif ($isLimitReached) {
                        $error = 'Kupon kullanım limiti doldu.';
                    } elseif ($isCompanyMismatch) {
                        $error = 'Bu kupon sadece ' . $trip['company_name'] . ' için geçerlidir.';
                    } elseif ($alreadyUsed) {
                        $error = 'Bu kuponu daha önce kullandınız.';
                    } else {
                        $discount = $coupon['discount'];
                        $couponId = $coupon['id'];
                        $totalPrice = $trip['price'] * (1 - $discount / 100);
                    }
                } else {
                    $error = 'Geçersiz kupon kodu.';
                }
            }
            
            if (!$error) {
                if ($currentBalance < $totalPrice) {
                    $error = 'Yetersiz bakiye. Bakiyeniz: ' . formatPrice($currentBalance);
                } else {
                    // Bilet oluştur
                    $ticketId = 'ticket-' . uniqid();
                    $stmt = $db->prepare("INSERT INTO Tickets (id, trip_id, user_id, seat_number, total_price, status) 
                                          VALUES (?, ?, ?, ?, ?, 'active')");
                    $stmt->execute([$ticketId, $tripId, $_SESSION['user_id'], $seatNumber, $totalPrice]);
                    
                    // Koltuk rezervasyonu
                    $bookedSeatId = 'bs-' . uniqid();
                    $stmt = $db->prepare("INSERT INTO Booked_Seats (id, trip_id, ticket_id, seat_number) 
                                          VALUES (?, ?, ?, ?)");
                    $stmt->execute([$bookedSeatId, $tripId, $ticketId, $seatNumber]);
                    
                    // Bakiye güncelle
                    $newBalance = $currentBalance - $totalPrice;
                    $stmt = $db->prepare("UPDATE User SET balance = ? WHERE id = ?");
                    $stmt->execute([$newBalance, $_SESSION['user_id']]);
                    $_SESSION['balance'] = $newBalance;
                    
                    // Kupon kullanımını kaydet
                    if ($couponId) {
                        $userCouponId = 'uc-' . uniqid();
                        $stmt = $db->prepare("INSERT INTO User_Coupons (id, coupon_id, user_id) 
                                              VALUES (?, ?, ?)");
                        $stmt->execute([$userCouponId, $couponId, $_SESSION['user_id']]);
                    }
                    
                    $db->commit();
                    
                    header('Location: my_tickets.php?success=1');
                    exit;
                }
            }
            
            if ($error) {
                $db->rollBack();
            }
            
        } catch (PDOException $e) {
            $db->rollBack();
            $error = 'Bilet alımı sırasında hata oluştu: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet Satın Al - <?= escape($trip['company_name']) ?></title>
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
                        <h6><?= escape($trip['company_name']) ?></h6>
                        <div class="row mt-3">
                            <div class="col-6">
                                <div class="text-muted small">Kalkış</div>
                                <div class="fw-bold"><?= escape($trip['departure_city']) ?></div>
                                <div class="text-primary"><?= formatDate($trip['departure_time']) ?></div>
                            </div>
                            <div class="col-6">
                                <div class="text-muted small">Varış</div>
                                <div class="fw-bold"><?= escape($trip['destination_city']) ?></div>
                                <div class="text-primary"><?= formatDate($trip['arrival_time']) ?></div>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span>Bilet Fiyatı:</span>
                            <strong class="text-success"><?= formatPrice($trip['price']) ?></strong>
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
                                <?php for ($i = 1; $i <= $trip['capacity']; $i++): ?>
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
