# 🚌 Bilet Satın Alma Platformu

Modern **OOP (Object-Oriented Programming)** mimarisi ile geliştirilmiş, dinamik ve kullanıcı dostu **otobüs bileti satış platformu**. Docker container yapısı ile her ortamda (Windows, Linux, macOS) kolayca çalıştırılabilir.

[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker)](https://www.docker.com/)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php)](https://www.php.net/)
[![SQLite](https://img.shields.io/badge/SQLite-Database-003B57?style=flat&logo=sqlite)](https://www.sqlite.org/)
[![OOP](https://img.shields.io/badge/Architecture-OOP-green?style=flat)](./OOP_RAPORU.md)

---

## 📋 Özellikler

### 👥 Kullanıcı Rolleri

| Rol | Yetkiler |
|-----|----------|
| **🔍 Ziyaretçi** | Sefer arama ve listeleme |
| **👤 User (Yolcu)** | Bilet satın alma, kupon kullanma, bilet iptal |
| **🏢 Firma Admin** | Kendi firmasına ait sefer yönetimi (CRUD) |
| **⚙️ Admin** | Firma, Firma Admin ve kupon yönetimi |

### ✨ Temel İşlevler

- ✅ Sefer arama ve listeleme
- ✅ Kullanıcı kayıt, giriş, çıkış
- ✅ Rol bazlı yetkilendirme
- ✅ Firma Admin paneli (sefer CRUD)
- ✅ Admin paneli (firma, Firma Admin, kupon yönetimi)
- ✅ Bilet satın alma (dolu koltuk kontrolü, kupon kodu)
- ✅ Bilet iptal (son 1 saat kuralı)
- ✅ Hesabım/Biletlerim sayfası
- ✅ Bilet PDF oluşturma

---

## 🏗️ OOP Mimarisi

Bu proje **SOLID prensipleri** ve **tasarım kalıpları** kullanılarak geliştirilmiştir.

### Kullanılan Tasarım Kalıpları

| Kalıp | Açıklama | Kullanım |
|-------|----------|----------|
| **Singleton** | Tek örnek garantisi | Database, Session |
| **Repository** | Veri erişim soyutlama | Tüm Repository sınıfları |
| **Service Layer** | İş mantığı katmanı | Auth, Ticket, Trip servisleri |
| **Facade** | Karmaşık sistemlere basit arayüz | TicketService |
| **Template Method** | Algoritma iskeleti tanımlama | BaseEntity, BaseRepository |
| **Strategy** | Değiştirilebilir algoritmalar | PaymentInterface |

### Proje Yapısı

```
src/
├── Core/                    # Çekirdek Sınıflar
│   ├── Database.php         # Singleton - PDO bağlantısı
│   ├── Session.php          # Singleton - Oturum yönetimi
│   └── Helpers.php          # Statik yardımcı metodlar
│
├── Interfaces/              # Sözleşmeler (Contracts)
│   ├── RepositoryInterface.php
│   ├── AuthServiceInterface.php
│   ├── ValidatableInterface.php
│   └── PaymentInterface.php
│
├── Entities/                # Veri Modelleri
│   ├── BaseEntity.php       # Abstract base class
│   ├── User.php
│   ├── BusCompany.php
│   ├── Trip.php
│   ├── Ticket.php
│   └── Coupon.php
│
├── Repositories/            # Veri Erişim Katmanı
│   ├── BaseRepository.php
│   ├── UserRepository.php
│   ├── TripRepository.php
│   ├── TicketRepository.php
│   ├── BusCompanyRepository.php
│   ├── CouponRepository.php
│   └── BookedSeatRepository.php
│
├── Services/                # İş Mantığı Katmanı
│   ├── BaseService.php
│   ├── AuthService.php
│   ├── TripService.php
│   ├── TicketService.php
│   ├── CouponService.php
│   ├── BusCompanyService.php
│   └── WalletPaymentService.php
│
├── Controllers/             # HTTP Kontrol Katmanı
│   ├── BaseController.php
│   ├── AuthController.php
│   ├── TripController.php
│   ├── TicketController.php
│   ├── AdminController.php
│   ├── CompanyAdminController.php
│   └── UserController.php
│
├── autoload.php             # PSR-4 Autoloader
└── bootstrap.php            # Uygulama başlatıcı
```

📖 **Detaylı OOP dokümantasyonu için:** [OOP_RAPORU.md](OOP_RAPORU.md)

---

## 🛠️ Teknoloji Stack

| Teknoloji | Versiyon | Açıklama |
|-----------|----------|----------|
| **PHP** | 8.2+ | Backend (OOP, Namespace, PDO) |
| **Apache** | 2.4 | Web server |
| **SQLite** | 3.x | Veritabanı (portable, lightweight) |
| **Bootstrap** | 5.3 | Frontend framework |
| **Docker** | Latest | Containerization |
| **Docker Compose** | Latest | Multi-container orchestration |

---

## 🚀 Hızlı Başlangıç

### Gereksinimler

- ✅ [Docker Desktop](https://www.docker.com/products/docker-desktop/) (Windows, macOS)
- ✅ Docker + Docker Compose (Linux)

> **Not:** Başka hiçbir şey kurmanıza gerek yok! Docker her şeyi halledecek.

### Kurulum (3 Adım)

#### 1️⃣ Depoyu Klonlayın

```bash
git clone https://github.com/mehmetyasinuzun/bilet-satin-alma.git
cd bilet-satin-alma
```

#### 2️⃣ Docker Container'ı Başlatın

```bash
docker-compose up -d --build
```

#### 3️⃣ Uygulamaya Erişin

Tarayıcınızda açın:
```
http://localhost:8080
```

**🎉 Hepsi bu kadar!** Veritabanı otomatik oluşturulacak ve test hesapları yüklenecek.

---

## 👥 Test Hesapları

Uygulama ilk çalıştırıldığında otomatik olarak test hesapları oluşturulur:

| Rol | Email | Şifre | Bakiye |
|-----|-------|-------|--------|
| **Admin** | admin@admin.com | admin123 | 10,000 ₺ |
| **Metro Admin** | metro@admin.com | 123456 | 5,000 ₺ |
| **Pamukkale Admin** | pamukkale@admin.com | 123456 | 5,000 ₺ |
| **Normal Kullanıcı** | user@test.com | 123456 | 3,000 ₺ |

---

## 🐳 Docker Komutları

### Container Yönetimi

```bash
# Container'ı başlat (build ile)
docker-compose up -d --build

# Container'ı başlat (build olmadan)
docker-compose up -d

# Container'ı durdur
docker-compose down

# Container'ı yeniden başlat
docker-compose restart

# Container'ı tamamen sil (veriler korunur)
docker-compose down --volumes
```

### Makefile Komutları

```bash
# Yardım
make help

# Başlat
make up

# Durdur
make down

# Logları göster
make logs

# Container'a bağlan
make shell
```

### Monitoring & Debug

```bash
# Canlı logları görüntüle
docker-compose logs -f

# Container'ın durumunu kontrol et
docker-compose ps

# Container içine gir (bash)
docker exec -it bilet-satin-alma bash

# Resource kullanımını gör
docker stats bilet-satin-alma
```

### Veritabanı Yönetimi

```bash
# Veritabanını yedekle
docker exec bilet-satin-alma sqlite3 /var/www/html/data/database.sqlite .dump > backup.sql

# Veritabanını sıfırla (tüm veriyi sil)
rm -rf data/database.sqlite
docker-compose restart

# SQLite shell'e bağlan
docker exec -it bilet-satin-alma sqlite3 /var/www/html/data/database.sqlite
```

---

## 🎯 Kullanım Senaryoları

### 👤 Yolcu İşlemleri

1. ✅ Sisteme kayıt ol / giriş yap
2. 🔍 Sefer ara (kalkış, varış, tarih)
3. 🚌 Uygun seferi seç
4. 💺 Koltuk seç ve kupon kodu uygula
5. 💳 Bilet satın al
6. 📄 Bileti PDF olarak indir
7. ❌ Gerekirse bileti iptal et (1 saat kuralı)

### 🏢 Firma Admin İşlemleri

1. 👁️ Kendi firmasına ait seferleri görüntüle
2. ➕ Yeni sefer ekle
3. ✏️ Mevcut seferleri düzenle
4. 🗑️ Seferleri sil

### ⚙️ Admin İşlemleri

1. 🏢 Yeni otobüs firması oluştur
2. 👤 Firma Admin hesapları oluştur/ata
3. 🎫 İndirim kuponları oluştur/yönet
4. 📊 Tüm firma ve kupon yönetimi

---

## 📁 Dosya Yapısı

```
bilet-satin-alma/
├── 📂 src/                        # OOP Kaynak Kodları
│   ├── Core/                      # Çekirdek sınıflar
│   ├── Interfaces/                # Interface tanımları
│   ├── Entities/                  # Entity sınıfları
│   ├── Repositories/              # Repository sınıfları
│   ├── Services/                  # Service sınıfları
│   ├── Controllers/               # Controller sınıfları
│   ├── autoload.php               # PSR-4 Autoloader
│   └── bootstrap.php              # Uygulama başlatıcı
├── 📂 assets/                     # Frontend assets
│   ├── css/
│   │   └── style.css
│   └── images/
├── 📂 includes/                   # PHP includes
│   ├── header.php
│   └── footer.php
├── 📂 data/                       # Database (Docker volume)
│   └── database.sqlite            # SQLite (auto-generated)
├── 🐳 Dockerfile                  # Docker image
├── 🐳 docker-compose.yml          # Docker orchestration
├── 🐳 docker-compose.prod.yml     # Production config
├── 🚀 docker-entrypoint.sh        # Container init script
├── 📄 Makefile                    # Docker shortcuts
├── 📄 config.php                  # Configuration
├── 📄 setup_database.php          # Database setup
├── 📄 index.php                   # Homepage
├── 📄 login.php                   # Login page
├── 📄 register.php                # Registration
├── 📄 user_dashboard.php          # User panel
├── 📄 admin_dashboard.php         # Admin panel
├── 📄 company_admin_dashboard.php # Company admin panel
├── 📄 ticket_purchase.php         # Ticket purchase
├── 📄 my_tickets.php              # My tickets
├── 📄 cancel_ticket.php           # Cancel ticket
├── 📄 generate_pdf.php            # PDF generation
├── 📄 logout.php                  # Logout
├── 📖 OOP_RAPORU.md               # OOP Documentation
└── 📖 README.md                   # This file
```

---

## 📊 Veritabanı Şeması

```sql
User                    # Kullanıcılar
├── id (PK)
├── full_name
├── email (UNIQUE)
├── role (user/company.admin/admin)
├── password (hashed)
├── company_id (FK)
└── balance

Bus_Company             # Otobüs Firmaları
├── id (PK)
├── name (UNIQUE)
└── logo_path

Trips                   # Seferler
├── id (PK)
├── company_id (FK)
├── departure_city
├── destination_city
├── departure_time
├── arrival_time
├── price
└── capacity

Tickets                 # Biletler
├── id (PK)
├── trip_id (FK)
├── user_id (FK)
├── seat_number
├── status (active/cancelled)
└── total_price

Booked_Seats           # Rezerve Koltuklar
├── id (PK)
├── trip_id (FK)
├── ticket_id (FK)
└── seat_number (UNIQUE per trip)

Coupons                # Kuponlar
├── id (PK)
├── code (UNIQUE)
├── discount (%)
├── company_id (FK, nullable)
├── usage_limit
└── expire_date

User_Coupons           # Kullanılan Kuponlar
├── id (PK)
├── coupon_id (FK)
└── user_id (FK)
```

---

## 🔒 Güvenlik Özellikleri

- ✅ **Password Hashing:** bcrypt algoritması
- ✅ **Session Management:** Singleton pattern ile güvenli oturum
- ✅ **XSS Protection:** Helpers::escape() fonksiyonu
- ✅ **SQL Injection Protection:** PDO prepared statements
- ✅ **Role-based Access Control:** Service layer ile yetki kontrolü
- ✅ **Encapsulation:** Private özellikler, public getter/setter
- ✅ **HTTP Only Cookies:** Session cookie güvenliği

---

## 🌍 Cross-Platform Uyumluluk

| Platform | Durum | Test |
|----------|-------|------|
| 🪟 **Windows 10/11** | ✅ | Docker Desktop |
| 🐧 **Linux** | ✅ | Ubuntu 20.04+, Debian |
| 🍎 **macOS** | ✅ | Docker Desktop |

### Platform Özel Komutlar

```powershell
# Windows (PowerShell)
docker-compose up -d --build
```

```bash
# Linux / macOS
sudo docker-compose up -d --build
```

---

## 🔧 Yapılandırma

### Port Değiştirme

`docker-compose.yml` dosyasında:

```yaml
ports:
  - "8080:80"  # Sol: Host port, Sağ: Container port
```

### Environment Variables

```yaml
environment:
  - TZ=Europe/Istanbul
  - PHP_MEMORY_LIMIT=256M
  - PHP_UPLOAD_MAX_FILESIZE=10M
```

---

## 🐛 Sorun Giderme

### Container başlamıyor

```bash
# Logları kontrol edin
docker-compose logs

# Port çakışması var mı?
netstat -an | findstr 8080  # Windows
netstat -an | grep 8080     # Linux/Mac
```

### Veritabanı oluşmadı

```bash
# Container'ı yeniden başlatın
docker-compose restart

# Manuel kurulum
docker exec -it bilet-satin-alma php /var/www/html/setup_database.php
```

### Permission hataları

```bash
docker exec -it bilet-satin-alma chmod -R 777 /var/www/html/data
```

---

## 📚 OOP Öğrenme Kaynakları

Proje içindeki OOP kullanımını anlamak için:

1. 📖 [OOP_RAPORU.md](OOP_RAPORU.md) - Detaylı Türkçe OOP dokümantasyonu
2. 📁 `src/` klasörü - Tüm OOP sınıfları ve açıklamaları
3. Her dosyanın başındaki PHPDoc yorumları

### Öğrenilecek Konular

- ✅ Class ve Object kavramları
- ✅ Encapsulation (Kapsülleme)
- ✅ Inheritance (Kalıtım)
- ✅ Polymorphism (Çok biçimlilik)
- ✅ Abstraction (Soyutlama)
- ✅ Interface ve Abstract Class
- ✅ SOLID Prensipleri
- ✅ Design Patterns (Tasarım Kalıpları)

---

## 🤝 Katkıda Bulunma

1. 🍴 Fork yapın
2. 🌿 Feature branch oluşturun (`git checkout -b feature/yeniOzellik`)
3. 💾 Commit edin (`git commit -m 'Yeni özellik eklendi'`)
4. 📤 Push edin (`git push origin feature/yeniOzellik`)
5. 🔃 Pull Request oluşturun

---

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir.

---

## 👨‍💻 Geliştirici

**Mehmet Yasin Uzun**
- 🌐 GitHub: [@mehmetyasinuzun](https://github.com/mehmetyasinuzun)
- 📧 Repository: [bilet-satin-alma](https://github.com/mehmetyasinuzun/bilet-satin-alma)

---

## 🌟 Özellikler Roadmap

- [ ] Email bildirimleri
- [ ] SMS entegrasyonu
- [ ] Online ödeme sistemi (Kredi kartı - Strategy Pattern ile)
- [ ] Koltuk haritası görselleştirme
- [ ] Multi-language desteği
- [ ] RESTful API
- [ ] Unit Tests (PHPUnit)
- [ ] Admin analytics dashboard

---

**⭐ Projeyi beğendiyseniz yıldız atmayı unutmayın!**

---

<div align="center">

Made with ❤️ for Turkish Bus Ticket Platform

🚌 **Happy Coding!** 🚌

</div>
