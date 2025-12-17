<?php
/**
 * Bilet İptali
 * 
 * OOP Kullanımı:
 * - TicketService: Bilet iptal işlemi (Facade Pattern)
 *   - Transaction yönetimi
 *   - Bakiye iadesi
 *   - Koltuk serbest bırakma
 * - TicketRepository: Veritabanı işlemleri
 * - UserRepository: Bakiye güncelleme
 * - BookedSeatRepository: Koltuk rezervasyonu iptali
 */
require_once 'config.php';
requireRole('user');

use App\Services\TicketService;
use App\Repositories\UserRepository;

$ticketId = $_GET['ticket_id'] ?? null;
$error = '';

if (!$ticketId) {
    header('Location: my_tickets.php');
    exit;
}

$ticketService = new TicketService();
$userRepository = new UserRepository();

$result = $ticketService->cancelTicket($ticketId, $_SESSION['user_id']);

if ($result) {
    // Session bakiyesini güncelle
    $_SESSION['balance'] = $userRepository->getBalance($_SESSION['user_id']);
    
    header('Location: my_tickets.php?cancelled=1');
    exit;
} else {
    $error = $ticketService->getFirstError();
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
