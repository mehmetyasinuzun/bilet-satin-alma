# 🎯 OOP (Nesne Yönelimli Programlama) Dönüşüm Raporu

## 📋 İçindekiler
1. [Genel Bakış](#genel-bakış)
2. [OOP Temel Prensipleri](#oop-temel-prensipleri)
3. [Tasarım Kalıpları (Design Patterns)](#tasarım-kalıpları)
4. [Mimari Yapı](#mimari-yapı)
5. [Sınıf Diyagramları](#sınıf-diyagramları)
6. [Dosya Yapısı](#dosya-yapısı)
7. [Kod Örnekleri](#kod-örnekleri)

---

## 🎯 Genel Bakış

Bu proje, prosedürel PHP kodundan **Nesne Yönelimli Programlama (OOP)** mimarisine dönüştürülmüştür. Dönüşüm sırasında **SOLID prensipleri**, **tasarım kalıpları** ve **clean code** pratikleri uygulanmıştır.

### Dönüşüm Özeti
| Önceki Durum | Sonraki Durum |
|--------------|---------------|
| Prosedürel PHP | OOP PHP 8+ |
| Global fonksiyonlar | Sınıflar ve Namespace'ler |
| Doğrudan SQL sorguları | Repository Pattern |
| Karışık iş mantığı | Service Layer |
| Dağınık kod | MVC benzeri mimari |

---

## 🏗️ OOP Temel Prensipleri

### 1. Kapsülleme (Encapsulation)

**Tanım:** Verileri ve bu veriler üzerinde çalışan metodları bir arada tutma, dış erişimi kontrol etme prensibidir.

**Uygulama:** Entity sınıflarında `private` özellikler ve `public` getter/setter metodları kullanılmıştır.

```php
// src/Entities/User.php
class User extends BaseEntity
{
    private ?int $id = null;
    private string $email = '';
    private string $passwordHash = '';
    private float $balance = 0.0;
    
    // Dışarıdan erişim sadece getter ile
    public function getBalance(): float
    {
        return $this->balance;
    }
    
    // Kontrollü değişiklik setter ile
    public function setBalance(float $balance): self
    {
        if ($balance < 0) {
            throw new InvalidArgumentException('Bakiye negatif olamaz');
        }
        $this->balance = $balance;
        return $this;
    }
}
```

### 2. Kalıtım (Inheritance)

**Tanım:** Bir sınıfın başka bir sınıftan özellik ve metodları miras almasıdır.

**Uygulama:** 
- Tüm Entity'ler `BaseEntity`'den türetilmiştir
- Tüm Repository'ler `BaseRepository`'den türetilmiştir
- Tüm Service'ler `BaseService`'den türetilmiştir
- Tüm Controller'lar `BaseController`'dan türetilmiştir

```php
// Temel sınıf
abstract class BaseEntity
{
    abstract public function toArray(): array;
    abstract public function validate(): array;
    
    public function fill(array $data): self
    {
        // Ortak doldurma mantığı
    }
}

// Türetilmiş sınıf
class Ticket extends BaseEntity
{
    // BaseEntity'nin soyut metodlarını implement eder
    public function toArray(): array { /* ... */ }
    public function validate(): array { /* ... */ }
    
    // Kendi özel metodlarını ekler
    public function canBeCancelled(): bool { /* ... */ }
}
```

### 3. Çok Biçimlilik (Polymorphism)

**Tanım:** Aynı arayüzün farklı türler tarafından farklı şekillerde uygulanmasıdır.

**Uygulama:**
- `RepositoryInterface` farklı repository'ler tarafından uygulanır
- `PaymentInterface` farklı ödeme yöntemleri için kullanılabilir
- `ValidatableInterface` tüm doğrulanabilir entity'ler için

```php
// Interface tanımı
interface PaymentInterface
{
    public function pay(int $userId, float $amount): bool;
    public function refund(int $userId, float $amount): bool;
}

// Farklı implementasyonlar
class WalletPaymentService implements PaymentInterface
{
    public function pay(int $userId, float $amount): bool
    {
        // Cüzdan bakiyesinden ödeme
    }
}

// Gelecekte eklenebilecek
class CreditCardPaymentService implements PaymentInterface
{
    public function pay(int $userId, float $amount): bool
    {
        // Kredi kartı ile ödeme
    }
}
```

### 4. Soyutlama (Abstraction)

**Tanım:** Karmaşık sistemleri basit arayüzlerle temsil etme, gereksiz detayları gizleme prensibidir.

**Uygulama:**
- Interface'ler ile sözleşme tanımlama
- Abstract sınıflar ile ortak davranış belirleme
- Service layer ile iş mantığını soyutlama

```php
// Soyut sınıf
abstract class BaseRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $tableName;
    protected string $entityClass;
    
    // Alt sınıfların override etmesi gereken soyut metod
    abstract protected function mapToEntity(array $data): BaseEntity;
    
    // Ortak implementasyon
    public function findById(int $id): ?BaseEntity
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tableName} WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch();
        return $data ? $this->mapToEntity($data) : null;
    }
}
```

---

## 🎨 Tasarım Kalıpları

### 1. Singleton Pattern (Tekil Kalıp)

**Amaç:** Bir sınıftan yalnızca bir örnek oluşturulmasını ve bu örneğe global erişim sağlanmasını garanti eder.

**Kullanım Yerleri:** `Database`, `Session`

```php
// src/Core/Database.php
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    
    // Private constructor - dışarıdan new ile oluşturulamaz
    private function __construct()
    {
        $this->connection = new PDO('sqlite:' . __DIR__ . '/../../data/tickets.db');
    }
    
    // Global erişim noktası
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Clone engelleme
    private function __clone() {}
}
```

**Faydaları:**
- Tek bir veritabanı bağlantısı garantisi
- Kaynak tasarrufu
- Global erişim kolaylığı

---

### 2. Repository Pattern (Depo Kalıbı)

**Amaç:** Veri erişim mantığını iş mantığından ayırır, veritabanı işlemlerini soyutlar.

**Kullanım Yerleri:** `UserRepository`, `TripRepository`, `TicketRepository`, vb.

```php
// src/Repositories/TicketRepository.php
class TicketRepository extends BaseRepository
{
    protected string $tableName = 'Tickets';
    protected string $entityClass = Ticket::class;
    
    public function getUserTickets(int $userId): array
    {
        $sql = "SELECT t.*, tr.departure_city, tr.destination_city,
                       bc.name as company_name
                FROM {$this->tableName} t
                JOIN Trips tr ON t.trip_id = tr.id
                JOIN Bus_Company bc ON tr.company_id = bc.id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        
        return array_map([$this, 'mapToEntity'], $stmt->fetchAll());
    }
}
```

**Faydaları:**
- Veri erişim mantığı merkezi bir yerde
- Test edilebilirlik (Mock yapılabilir)
- Veritabanı değişikliklerinden izolasyon

---

### 3. Service Layer Pattern (Servis Katmanı)

**Amaç:** İş mantığını controller'lardan ve repository'lerden ayırır, uygulamanın ana iş kurallarını içerir.

**Kullanım Yerleri:** `AuthService`, `TicketService`, `TripService`, vb.

```php
// src/Services/TicketService.php
class TicketService extends BaseService
{
    public function purchaseTicket(
        int $userId,
        int $tripId,
        int $seatNumber,
        ?string $couponCode = null
    ): ?Ticket {
        $this->db->beginTransaction();
        
        try {
            // 1. Sefer kontrolü
            $trip = $this->tripRepository->findById($tripId);
            if (!$trip || $trip->getAvailableSeats() <= 0) {
                throw new RuntimeException('Sefer müsait değil');
            }
            
            // 2. Koltuk kontrolü
            if ($this->bookedSeatRepository->isSeatBooked($tripId, $seatNumber)) {
                throw new RuntimeException('Koltuk zaten dolu');
            }
            
            // 3. Kupon uygula
            $discount = 0;
            if ($couponCode) {
                $coupon = $this->couponService->applyCoupon($couponCode);
                $discount = $coupon->getDiscount();
            }
            
            // 4. Ödeme al
            $totalPrice = $trip->getPrice() * (1 - $discount / 100);
            if (!$this->paymentService->pay($userId, $totalPrice)) {
                throw new RuntimeException('Yetersiz bakiye');
            }
            
            // 5. Bilet oluştur
            $ticket = $this->ticketRepository->create([...]);
            
            $this->db->commit();
            return $ticket;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->addError($e->getMessage());
            return null;
        }
    }
}
```

**Faydaları:**
- İş mantığı tek bir yerde
- Transaction yönetimi
- Yeniden kullanılabilirlik

---

### 4. Facade Pattern (Cephe Kalıbı)

**Amaç:** Karmaşık alt sistemlere basit bir arayüz sağlar.

**Kullanım:** `TicketService` birden fazla repository ve servisi koordine eder.

```php
class TicketService extends BaseService
{
    private TripRepository $tripRepository;
    private TicketRepository $ticketRepository;
    private BookedSeatRepository $bookedSeatRepository;
    private CouponService $couponService;
    private PaymentInterface $paymentService;
    
    // Dışarıdan sadece basit metodlar görülür
    public function purchaseTicket(...): ?Ticket { /* ... */ }
    public function cancelTicket(int $ticketId, int $userId): bool { /* ... */ }
}
```

---

### 5. Template Method Pattern (Şablon Metod Kalıbı)

**Amaç:** Bir algoritmanın iskeletini tanımlar, bazı adımları alt sınıflara bırakır.

**Kullanım:** `BaseEntity`, `BaseRepository`, `BaseController`

```php
abstract class BaseEntity
{
    // Şablon metod - genel akış
    public function fill(array $data): self
    {
        foreach ($data as $key => $value) {
            $setter = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
        return $this;
    }
    
    // Alt sınıfların uygulaması gereken soyut metodlar
    abstract public function toArray(): array;
    abstract public function validate(): array;
}
```

---

### 6. Strategy Pattern (Strateji Kalıbı)

**Amaç:** Algoritma ailesini tanımlar, her birini kapsüller ve birbirinin yerine kullanılabilir hale getirir.

**Kullanım:** `PaymentInterface` ve implementasyonları

```php
// Strateji interface'i
interface PaymentInterface
{
    public function pay(int $userId, float $amount): bool;
    public function refund(int $userId, float $amount): bool;
    public function getBalance(int $userId): float;
}

// Somut strateji 1
class WalletPaymentService implements PaymentInterface
{
    public function pay(int $userId, float $amount): bool
    {
        // Cüzdan ile ödeme
    }
}

// Somut strateji 2 (gelecekte eklenebilir)
class CreditCardPaymentService implements PaymentInterface
{
    public function pay(int $userId, float $amount): bool
    {
        // Kredi kartı ile ödeme
    }
}

// Kullanım - bağımlılık enjeksiyonu ile
class TicketService
{
    public function __construct(PaymentInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }
}
```

---

## 🏛️ Mimari Yapı

### Katmanlı Mimari

```
┌─────────────────────────────────────────────────────────────────┐
│                        PRESENTATION LAYER                        │
│   (Views: index.php, login.php, user_dashboard.php, vb.)        │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                       CONTROLLER LAYER                          │
│     AuthController, TripController, TicketController, vb.       │
│  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐                │
│  │   render()  │ │ redirect()  │ │requireAuth()│                │
│  └─────────────┘ └─────────────┘ └─────────────┘                │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        SERVICE LAYER                            │
│      AuthService, TicketService, TripService, CouponService     │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │  İş Mantığı  │  Validasyon  │  Transaction  │  Facade   │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                      REPOSITORY LAYER                           │
│    UserRepository, TripRepository, TicketRepository, vb.        │
│  ┌─────────────────────────────────────────────────────────┐    │
│  │   findById() │  findAll()  │  create()  │   delete()   │    │
│  └─────────────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                         CORE LAYER                              │
│              Database (Singleton), Session, Helpers              │
└─────────────────────────────────────────────────────────────────┘
                                │
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                               │
│                    SQLite Database (PDO)                        │
└─────────────────────────────────────────────────────────────────┘
```

### Veri Akışı

```
HTTP İsteği → Controller → Service → Repository → Database
                  │            │           │
                  │            │           └── SQL Sorguları
                  │            └── İş Mantığı, Validasyon
                  └── HTTP Yanıtı, Render
```

---

## 📊 Sınıf Diyagramları

### Entity İlişkileri

```
┌─────────────────────┐
│    BaseEntity       │ (Abstract)
├─────────────────────┤
│ # data: array       │
├─────────────────────┤
│ + fill()            │
│ + toArray()         │
│ + validate()        │
└─────────────────────┘
          △
          │
    ┌─────┴─────┬─────────┬──────────┬─────────┐
    │           │         │          │         │
┌───┴───┐  ┌────┴────┐ ┌──┴──┐  ┌────┴────┐ ┌──┴───┐
│ User  │  │BusCompany│ │Trip │  │ Ticket  │ │Coupon│
└───────┘  └─────────┘ └─────┘  └─────────┘ └──────┘
```

### Repository Kalıtımı

```
┌─────────────────────────────┐
│    RepositoryInterface      │ (Interface)
├─────────────────────────────┤
│ + findById(int): ?Entity    │
│ + findAll(): array          │
│ + create(array): ?Entity    │
│ + update(int, array): bool  │
│ + delete(int): bool         │
└─────────────────────────────┘
              △
              │
┌─────────────┴───────────────┐
│      BaseRepository         │ (Abstract)
├─────────────────────────────┤
│ # db: Database              │
│ # tableName: string         │
│ # entityClass: string       │
├─────────────────────────────┤
│ # mapToEntity(): Entity     │ (Abstract)
│ + findById()                │
│ + findAll()                 │
│ + create()                  │
└─────────────────────────────┘
              △
              │
    ┌─────────┼─────────┬──────────┐
    │         │         │          │
┌───┴────┐ ┌──┴──┐ ┌────┴────┐ ┌───┴──┐
│UserRepo│ │Trip │ │TicketRepo│ │Others│
│        │ │Repo │ │          │ │      │
└────────┘ └─────┘ └──────────┘ └──────┘
```

---

## 📁 Dosya Yapısı

```
src/
├── Core/                          # Çekirdek sınıflar
│   ├── Database.php               # Singleton veritabanı bağlantısı
│   ├── Session.php                # Singleton oturum yönetimi
│   └── Helpers.php                # Statik yardımcı metodlar
│
├── Interfaces/                    # Sözleşmeler (Contracts)
│   ├── RepositoryInterface.php    # CRUD operasyonları
│   ├── AuthServiceInterface.php   # Kimlik doğrulama
│   ├── ValidatableInterface.php   # Doğrulama
│   └── PaymentInterface.php       # Ödeme işlemleri
│
├── Entities/                      # Veri modelleri
│   ├── BaseEntity.php             # Soyut temel entity
│   ├── User.php                   # Kullanıcı entity
│   ├── BusCompany.php             # Otobüs firması entity
│   ├── Trip.php                   # Sefer entity
│   ├── Ticket.php                 # Bilet entity
│   └── Coupon.php                 # Kupon entity
│
├── Repositories/                  # Veri erişim katmanı
│   ├── BaseRepository.php         # Soyut temel repository
│   ├── UserRepository.php         # Kullanıcı CRUD
│   ├── TripRepository.php         # Sefer CRUD
│   ├── TicketRepository.php       # Bilet CRUD
│   ├── BusCompanyRepository.php   # Firma CRUD
│   ├── CouponRepository.php       # Kupon CRUD
│   └── BookedSeatRepository.php   # Koltuk rezervasyon
│
├── Services/                      # İş mantığı katmanı
│   ├── BaseService.php            # Soyut temel servis
│   ├── AuthService.php            # Kimlik doğrulama
│   ├── TripService.php            # Sefer işlemleri
│   ├── TicketService.php          # Bilet işlemleri (Facade)
│   ├── CouponService.php          # Kupon işlemleri
│   ├── BusCompanyService.php      # Firma işlemleri
│   └── WalletPaymentService.php   # Cüzdan ödemeleri
│
├── Controllers/                   # HTTP kontrol katmanı
│   ├── BaseController.php         # Soyut temel controller
│   ├── AuthController.php         # Giriş/Kayıt
│   ├── TripController.php         # Sefer yönetimi
│   ├── TicketController.php       # Bilet yönetimi
│   ├── AdminController.php        # Admin paneli
│   ├── CompanyAdminController.php # Firma paneli
│   └── UserController.php         # Kullanıcı paneli
│
├── autoload.php                   # PSR-4 autoloader
└── bootstrap.php                  # Uygulama başlatıcı
```

---

## 💻 Kod Örnekleri

### Örnek 1: Bilet Satın Alma İşlemi

```php
// ticket_purchase.php

// 1. Servisi başlat
$ticketService = new TicketService();

// 2. Bilet satın al
$ticket = $ticketService->purchaseTicket(
    userId: $_SESSION['user_id'],
    tripId: (int)$_POST['trip_id'],
    seatNumber: (int)$_POST['seat_number'],
    couponCode: $_POST['coupon_code'] ?: null
);

// 3. Sonucu kontrol et
if ($ticket) {
    Session::getInstance()->setFlash('success', 'Bilet satın alındı!');
    redirect('my_tickets.php');
} else {
    $errors = $ticketService->getErrors();
    Session::getInstance()->setFlash('error', implode(', ', $errors));
}
```

### Örnek 2: Kullanıcı Girişi

```php
// login.php

$authController = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $authController->login($_POST['email'], $_POST['password']);
    
    if ($result['success']) {
        redirect($result['redirect']);
    } else {
        $error = $result['message'];
    }
}
```

### Örnek 3: Entity Kullanımı

```php
// Yeni kullanıcı oluşturma
$user = new User();
$user->setEmail('test@example.com')
     ->setFullName('Test Kullanıcı')
     ->setPassword('sifre123')  // Otomatik hash'lenir
     ->setRole(User::ROLE_USER)
     ->setBalance(100.0);

// Validasyon
$errors = $user->validate();
if (empty($errors)) {
    $userRepository->create($user->toArray());
}

// Diziye dönüştürme
$data = $user->toArray();
```

---

## ✅ SOLID Prensipleri Uyumu

### S - Single Responsibility (Tek Sorumluluk)
- Her sınıf tek bir sorumluluğa sahip
- `UserRepository`: Sadece kullanıcı CRUD işlemleri
- `AuthService`: Sadece kimlik doğrulama

### O - Open/Closed (Açık/Kapalı)
- Yeni ödeme yöntemi eklemek için `PaymentInterface` implement edilir
- Mevcut kod değiştirilmez

### L - Liskov Substitution (Liskov Yerine Geçme)
- `BaseRepository` türündeki değişken, herhangi bir alt sınıfla çalışabilir

### I - Interface Segregation (Arayüz Ayrımı)
- Küçük, odaklı interface'ler
- `PaymentInterface`, `ValidatableInterface` ayrı

### D - Dependency Inversion (Bağımlılık Tersine Çevirme)
- Servisler interface'lere bağımlı, somut sınıflara değil
- `TicketService` → `PaymentInterface` (WalletPaymentService değil)

---

## 🎓 Sonuç

Bu OOP dönüşümü ile:

1. **Bakım Kolaylığı:** Kodun anlaşılması ve değiştirilmesi kolaylaştı
2. **Test Edilebilirlik:** Mock nesnelerle birim testleri yazılabilir
3. **Yeniden Kullanım:** Servisler ve repository'ler başka projelerde kullanılabilir
4. **Genişletilebilirlik:** Yeni özellikler mevcut kodu değiştirmeden eklenebilir
5. **Güvenlik:** Kapsülleme ile veri güvenliği arttı

---

## 📚 Kaynaklar

- [PHP: The Right Way](https://phptherightway.com/)
- [Design Patterns](https://refactoring.guru/design-patterns)
- [SOLID Principles](https://en.wikipedia.org/wiki/SOLID)
- [PSR Standards](https://www.php-fig.org/psr/)
