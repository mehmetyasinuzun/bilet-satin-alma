<?php
/**
 * PDF Bilet Oluşturma
 * 
 * OOP Kullanımı:
 * - TicketService: Bilet bilgilerini getirir
 * - Ticket Entity: Bilet verilerini temsil eder
 */
require_once 'config.php';
requireRole('user');

use App\Services\TicketService;

$ticketId = $_GET['ticket_id'] ?? null;

if (!$ticketId) {
    die('Geçersiz bilet.');
}

$ticketService = new TicketService();
$ticket = $ticketService->getTicketDetails($ticketId, $_SESSION['user_id']);

if (!$ticket) {
    die('Bilet bulunamadı.');
}

// Ticket entity'den veri al
$data = [
    'id' => $ticket->getId(),
    'company_name' => $ticket->getCompanyName(),
    'departure_city' => $ticket->getDepartureCity(),
    'destination_city' => $ticket->getDestinationCity(),
    'departure_time' => $ticket->getDepartureTime(),
    'arrival_time' => $ticket->getArrivalTime(),
    'seat_number' => $ticket->getSeatNumber(),
    'total_price' => $ticket->getTotalPrice(),
    'status' => $ticket->getStatus(),
    'full_name' => $_SESSION['full_name'],
    'email' => $_SESSION['email'],
    'created_at' => $ticket->getCreatedAt()
];

// HTML içeriğini hazırla - tarayıcının PDF olarak kaydetmesini sağla
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Bilet - <?= $ticketId ?></title>
    <style>
        @page {
            margin: 1cm;
            size: A4 portrait;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            padding: 10px;
        }
        .ticket-container {
            border: 2px solid #0d6efd;
            border-radius: 8px;
            padding: 15px;
            max-width: 650px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 18px;
            margin-bottom: 3px;
        }
        .header p {
            color: #6c757d;
            font-size: 10px;
            margin: 3px 0;
        }
        .status-badge {
            display: inline-block;
            background: #198754;
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px 15px;
            margin: 10px 0;
        }
        .info-item {
            display: flex;
            font-size: 10px;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            min-width: 100px;
            color: #333;
        }
        .info-value {
            color: #666;
        }
        .route-section {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 6px;
            margin: 10px 0;
        }
        .route-section h3 {
            color: #0d6efd;
            font-size: 12px;
            margin-bottom: 8px;
        }
        .route-grid {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            gap: 10px;
            align-items: center;
        }
        .route-col {
            text-align: center;
        }
        .route-col strong {
            display: block;
            font-size: 9px;
            color: #666;
            margin-bottom: 3px;
        }
        .route-col .city {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin: 3px 0;
        }
        .route-col .time {
            color: #0d6efd;
            font-size: 10px;
            font-weight: bold;
        }
        .route-arrow {
            font-size: 20px;
            color: #0d6efd;
        }
        .price-section {
            background: #d1e7dd;
            padding: 8px;
            border-radius: 6px;
            text-align: center;
            margin: 10px 0;
        }
        .price-section p {
            font-size: 9px;
            margin-bottom: 3px;
        }
        .price-section h2 {
            color: #198754;
            font-size: 20px;
            margin: 0;
        }
        .footer {
            margin-top: 12px;
            padding-top: 8px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 8px;
            color: #6c757d;
        }
        .footer p {
            margin: 3px 0;
        }
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #0d6efd;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            z-index: 1000;
            transition: all 0.3s;
        }
        .print-btn:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        🖨️ PDF Olarak Kaydet / Yazdır
    </button>
    
    <div class="ticket-container">
        <div class="header">
            <h1>🚌 OTOBÜS BİLETİ</h1>
            <p>Bilet Platformu</p>
            <span class="status-badge"><?= $data['status'] === 'active' ? 'AKTİF' : 'İPTAL' ?></span>
        </div>
        
        <div class="route-section">
            <h3>Güzergah</h3>
            <div class="route-grid">
                <div class="route-col">
                    <strong>Kalkış</strong>
                    <div class="city"><?= escape($data['departure_city']) ?></div>
                    <div class="time"><?= formatDate($data['departure_time']) ?></div>
                </div>
                <div class="route-arrow">→</div>
                <div class="route-col">
                    <strong>Varış</strong>
                    <div class="city"><?= escape($data['destination_city']) ?></div>
                    <div class="time"><?= formatDate($data['arrival_time']) ?></div>
                </div>
            </div>
        </div>
        
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Firma:</span>
                <span class="info-value"><?= escape($data['company_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Koltuk:</span>
                <span class="info-value"><strong style="font-size: 14px; color: #0d6efd;"><?= $data['seat_number'] ?></strong></span>
            </div>
            <div class="info-item">
                <span class="info-label">Yolcu:</span>
                <span class="info-value"><?= escape($data['full_name']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">E-posta:</span>
                <span class="info-value"><?= escape($data['email']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Satın Alma:</span>
                <span class="info-value"><?= formatDate($data['created_at']) ?></span>
            </div>
            <div class="info-item">
                <span class="info-label">Bilet No:</span>
                <span class="info-value" style="font-size: 9px;"><?= escape($data['id']) ?></span>
            </div>
        </div>
        
        <div class="price-section">
            <p>Ödenen Tutar</p>
            <h2><?= formatPrice($data['total_price']) ?></h2>
        </div>
        
        <div class="footer">
            <p><strong>ℹ️ Bilgi:</strong> Kalkıştan 1 saat öncesine kadar iptal edilebilir • Biletinizi yanınızda bulundurunuz • İyi yolculuklar!</p>
            <p>© <?= date('Y') ?> Bilet Platformu</p>
        </div>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
