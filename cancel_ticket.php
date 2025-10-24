<?php
require_once 'config.php';
requireRole('user');

$ticketId = $_GET['ticket_id'] ?? null;
$error = '';

if (!$ticketId) {
    header('Location: my_tickets.php');
    exit;
}

$db = getDB();

try {
    $db->beginTransaction();
    
    // Bilet bilgilerini al ve kontrol et
    $stmt = $db->prepare("SELECT t.*, tr.departure_time, tr.price
                          FROM Tickets t
                          JOIN Trips tr ON t.trip_id = tr.id
                          WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'");
    $stmt->execute([$ticketId, $_SESSION['user_id']]);
    $ticket = $stmt->fetch();
    
    if (!$ticket) {
        throw new Exception('Bilet bulunamadı veya zaten iptal edilmiş.');
    }
    
    // Kalkış saatine 1 saatten az kaldıysa iptal edilemez
    $departureTime = strtotime($ticket['departure_time']);
    $currentTime = time();
    $hoursDiff = ($departureTime - $currentTime) / 3600;
    
    if ($hoursDiff <= 1) {
        throw new Exception('Kalkışa 1 saatten az kaldığı için bilet iptal edilemez.');
    }
    
    // Bileti iptal et
    $stmt = $db->prepare("UPDATE Tickets SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$ticketId]);
    
    // Koltuk rezervasyonunu kaldır
    $stmt = $db->prepare("DELETE FROM Booked_Seats WHERE ticket_id = ?");
    $stmt->execute([$ticketId]);
    
    // İade yap
    $stmt = $db->prepare("UPDATE User SET balance = balance + ? WHERE id = ?");
    $stmt->execute([$ticket['total_price'], $_SESSION['user_id']]);
    
    // Session bakiyesini güncelle
    $stmt = $db->prepare("SELECT balance FROM User WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $_SESSION['balance'] = $stmt->fetch()['balance'];
    
    $db->commit();
    
    header('Location: my_tickets.php?cancelled=1');
    exit;
    
} catch (Exception $e) {
    $db->rollBack();
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilet İptali - Bilet Platformu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container my-5">
        <div class="alert alert-danger">
            <h4>Hata!</h4>
            <p><?= escape($error) ?></p>
            <a href="my_tickets.php" class="btn btn-primary">Biletlerime Dön</a>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
