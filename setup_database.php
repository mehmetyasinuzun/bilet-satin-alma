<?php
require_once 'config.php';

try {
    $db = getDB();
    
    // Foreign key desteğini aktifleştir
    $db->exec("PRAGMA foreign_keys = ON");
    
    // User tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS User (
        id TEXT PRIMARY KEY,
        full_name TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        role TEXT NOT NULL DEFAULT 'user',
        password TEXT NOT NULL,
        company_id TEXT,
        balance REAL DEFAULT 0,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE SET NULL
    )");
    
    // Bus_Company tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS Bus_Company (
        id TEXT PRIMARY KEY,
        name TEXT UNIQUE NOT NULL,
        logo_path TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Trips tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS Trips (
        id TEXT PRIMARY KEY,
        company_id TEXT NOT NULL,
        destination_city TEXT NOT NULL,
        arrival_time TEXT NOT NULL,
        departure_time TEXT NOT NULL,
        departure_city TEXT NOT NULL,
        price INTEGER NOT NULL,
        capacity INTEGER NOT NULL,
        created_date TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE CASCADE
    )");
    
    // Tickets tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS Tickets (
        id TEXT PRIMARY KEY,
        trip_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        seat_number INTEGER NOT NULL,
        status TEXT DEFAULT 'active',
        total_price INTEGER NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES Trips(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
    )");
    
    // Booked_Seats tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS Booked_Seats (
        id TEXT PRIMARY KEY,
        trip_id TEXT NOT NULL,
        ticket_id TEXT NOT NULL,
        seat_number INTEGER NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trip_id) REFERENCES Trips(id) ON DELETE CASCADE,
        FOREIGN KEY (ticket_id) REFERENCES Tickets(id) ON DELETE CASCADE,
        UNIQUE(trip_id, seat_number)
    )");
    
    // Coupons tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS Coupons (
        id TEXT PRIMARY KEY,
        code TEXT UNIQUE NOT NULL,
        discount REAL NOT NULL,
        company_id TEXT,
        usage_limit INTEGER,
        expire_date TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (company_id) REFERENCES Bus_Company(id) ON DELETE SET NULL
    )");
    
    // User_Coupons tablosu
    $db->exec("CREATE TABLE IF NOT EXISTS User_Coupons (
        id TEXT PRIMARY KEY,
        coupon_id TEXT NOT NULL,
        user_id TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (coupon_id) REFERENCES Coupons(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
    )");
    
    // İndeksler
    $db->exec("CREATE INDEX IF NOT EXISTS idx_trips_company ON Trips(company_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tickets_trip ON Tickets(trip_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_tickets_user ON Tickets(user_id)");
    $db->exec("CREATE INDEX IF NOT EXISTS idx_booked_seats_trip ON Booked_Seats(trip_id)");
    
    // Admin kullanıcı ekle
    $adminId = 'admin-' . uniqid();
    $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO User (id, full_name, email, role, password, balance) 
                          VALUES (?, 'Admin', 'admin@admin.com', 'admin', ?, 10000)");
    $stmt->execute([$adminId, $adminPassword]);
    
    // Örnek firma ekle
    $company1Id = 'company-' . uniqid();
    $company2Id = 'company-' . uniqid();
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO Bus_Company (id, name) VALUES (?, ?)");
    $stmt->execute([$company1Id, 'Metro Turizm']);
    $stmt->execute([$company2Id, 'Pamukkale']);
    
    // Firma admin kullanıcıları ekle
    $companyAdmin1Id = 'user-' . uniqid();
    $companyAdmin2Id = 'user-' . uniqid();
    $password = password_hash('123456', PASSWORD_DEFAULT);
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO User (id, full_name, email, role, password, company_id, balance) 
                          VALUES (?, ?, ?, 'company.admin', ?, ?, 5000)");
    $stmt->execute([$companyAdmin1Id, 'Metro Admin', 'metro@admin.com', $password, $company1Id]);
    $stmt->execute([$companyAdmin2Id, 'Pamukkale Admin', 'pamukkale@admin.com', $password, $company2Id]);
    
    // Normal kullanıcı ekle
    $userId = 'user-' . uniqid();
    $stmt = $db->prepare("INSERT OR IGNORE INTO User (id, full_name, email, role, password, balance) 
                          VALUES (?, 'Test Kullanıcı', 'user@test.com', 'user', ?, 3000)");
    $stmt->execute([$userId, $password]);
    
    // Örnek seferler ekle
    $trips = [
        // Metro Turizm seferleri
        ['İstanbul', 'Ankara', '2025-10-25 10:00:00', '2025-10-25 15:30:00', 250, 40, $company1Id],
        ['İstanbul', 'Sivas', '2025-10-26 08:00:00', '2025-10-26 18:00:00', 350, 42, $company1Id],
        ['İstanbul', 'Mersin', '2025-10-27 09:00:00', '2025-10-27 19:30:00', 400, 45, $company1Id],
        ['İstanbul', 'Tekirdağ', '2025-10-28 07:00:00', '2025-10-28 09:30:00', 150, 38, $company1Id],
        ['Ankara', 'İzmir', '2025-10-29 09:00:00', '2025-10-29 17:00:00', 300, 45, $company1Id],
        // Pamukkale seferleri
        ['İstanbul', 'Antalya', '2025-10-25 20:00:00', '2025-10-26 08:00:00', 400, 50, $company2Id],
        ['İzmir', 'İstanbul', '2025-10-26 14:00:00', '2025-10-26 22:00:00', 280, 42, $company2Id],
        ['Ankara', 'Antalya', '2025-10-27 10:00:00', '2025-10-27 18:30:00', 320, 44, $company2Id],
        ['Mersin', 'İstanbul', '2025-10-28 08:00:00', '2025-10-28 18:00:00', 380, 46, $company2Id],
        ['Sivas', 'Ankara', '2025-10-29 06:00:00', '2025-10-29 16:00:00', 330, 40, $company2Id],
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO Trips (id, company_id, departure_city, destination_city, 
                          departure_time, arrival_time, price, capacity) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($trips as $trip) {
        $tripId = 'trip-' . uniqid();
        $stmt->execute([
            $tripId, 
            $trip[6],
            $trip[0], 
            $trip[1], 
            $trip[2], 
            $trip[3], 
            $trip[4], 
            $trip[5]
        ]);
    }
    
    // Örnek kuponlar ekle
    $coupons = [
        ['WELCOME10', 10, null, 999],
        ['METRO20', 20, $company1Id, 100],
        ['PAMUKKALE15', 15, $company2Id, 50],
    ];
    
    $stmt = $db->prepare("INSERT OR IGNORE INTO Coupons (id, code, discount, company_id, usage_limit, expire_date) 
                          VALUES (?, ?, ?, ?, ?, '2025-12-31 23:59:59')");
    
    foreach ($coupons as $coupon) {
        $couponId = 'coupon-' . uniqid();
        $stmt->execute([$couponId, $coupon[0], $coupon[1], $coupon[2], $coupon[3]]);
    }
    
    echo "<!DOCTYPE html>
    <html lang='tr'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Veritabanı Kurulumu</title>
        <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    </head>
    <body>
        <div class='container mt-5'>
            <div class='alert alert-success'>
                <h4 class='alert-heading'>✅ Veritabanı başarıyla kuruldu!</h4>
                <hr>
                <h5>Test Hesapları:</h5>
                <ul>
                    <li><strong>Admin:</strong> admin@admin.com / admin123</li>
                    <li><strong>Metro Admin:</strong> metro@admin.com / 123456</li>
                    <li><strong>Pamukkale Admin:</strong> pamukkale@admin.com / 123456</li>
                    <li><strong>Normal Kullanıcı:</strong> user@test.com / 123456</li>
                </ul>
                <a href='index.php' class='btn btn-primary'>Ana Sayfaya Git</a>
            </div>
        </div>
    </body>
    </html>";
    
} catch (PDOException $e) {
    die("Veritabanı kurulum hatası: " . $e->getMessage());
}
?>
