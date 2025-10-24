# 🚌 Bilet Satın Alma Platformu

Modern web teknolojileri kullanılarak geliştirilmiş, dinamik ve kullanıcı dostu otobüs bileti satış platformu.

## 📋 Özellikler

### Kullanıcı Rolleri

- **👤 Ziyaretçi (Giriş Yapmamış):** Sefer arama ve listeleme
- **👨‍💼 User (Yolcu):** Bilet satın alma, kupon kullanma, bilet iptal
- **🏢 Firma Admin (Firma Yetkilisi):** Sefer yönetimi (CRUD)
- **⚙️ Admin:** Firma, Firma Admin ve kupon yönetimi

### Temel İşlevler

- ✅ Sefer arama ve listeleme
- ✅ Kullanıcı kayıt olma, giriş yapma ve çıkış yapma
- ✅ Ana sayfa ve yetki listeleme formu
- ✅ Rol yönetimi
- ✅ Firma Admin paneli (sefer CRUD)
- ✅ Admin paneli (firma, Firma Admin, kupon yönetimi)
- ✅ Bilet satın alma (dolu koltuklar disabled, kupon kodu)
- ✅ Bilet iptal etme (son 1 saat kuralı)
- ✅ Hesabım/Biletler (profil bilgileri, kredisi, geçmiş biletler, PDF indirme)
- ✅ Bilet PDF oluşturma

## 🛠️ Teknoloji Stack

- **Backend:** PHP 8.2
- **Frontend:** HTML, CSS, Bootstrap 5
- **Veritabanı:** SQLite
- **Containerization:** Docker & Docker Compose
- **Web Server:** Apache 2

## 🚀 Kurulum

### Docker ile Kurulum (Önerilen)

1. **Depoyu klonlayın:**
```bash
git clone https://github.com/mehmetyasinuzun/bilet-satin-alma.git
cd bilet-satin-alma
```

2. **Docker Container'ı başlatın:**
```bash
docker-compose up -d --build
```

3. **Uygulamaya erişin:**
```
http://localhost:8080
```

### Manuel Kurulum (XAMPP/WAMP)

1. **Depoyu klonlayın:**
```bash
git clone https://github.com/mehmetyasinuzun/bilet-satin-alma.git
```

2. **Dosyaları web server dizinine kopyalayın:**
   - XAMPP: `C:\xampp\htdocs\`
   - WAMP: `C:\wamp64\www\`

3. **Web server'ı başlatın ve tarayıcıda açın:**
```
http://localhost/bilet-satin-alma
```

4. **Veritabanını kurun:**
```
http://localhost/bilet-satin-alma/setup_database.php
```

## 👥 Test Hesapları

Uygulama ilk kurulumda otomatik test hesapları oluşturur:

| Rol | Email | Şifre |
|-----|-------|-------|
| Admin | admin@admin.com | admin123 |
| Metro Admin | metro@admin.com | 123456 |
| Pamukkale Admin | pamukkale@admin.com | 123456 |
| Normal Kullanıcı | user@test.com | 123456 |

## 📊 Veritabanı Şeması

- **User:** Kullanıcı bilgileri ve roller
- **Bus_Company:** Otobüs firmaları
- **Trips:** Seferler
- **Tickets:** Satın alınan biletler
- **Booked_Seats:** Rezerve edilen koltuklar
- **Coupons:** İndirim kuponları
- **User_Coupons:** Kullanılan kuponlar

## 🎯 Kullanıcı Senaryoları

### Yolcu İşlemleri
1. Sisteme kayıt olma / giriş yapma
2. Sefer arama (kalkış, varış, tarih)
3. Uygun seferi seçme
4. Koltuk seçimi ve kupon kodu uygulama
5. Bilet satın alma
6. Bileti PDF olarak indirme
7. Gerekirse bileti iptal etme (1 saat kuralı)

### Firma Admin İşlemleri
1. Kendi firmasına ait seferleri görüntüleme
2. Yeni sefer ekleme
3. Mevcut seferleri düzenleme/silme

### Admin İşlemleri
1. Yeni otobüs firması oluşturma
2. Firma Admin hesapları oluşturma/atama
3. İndirim kuponları oluşturma/yönetme
4. Tüm firma ve kupon yönetimi

## 🐳 Docker Komutları

```bash
# Container'ı başlatma
docker-compose up -d

# Container'ı durdurma
docker-compose down

# Logları görüntüleme
docker-compose logs -f

# Container içine girme
docker exec -it bilet-satin-alma bash

# Yeniden başlatma
docker-compose restart
```

## 📁 Proje Yapısı

```
bilet-satin-alma/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── images/
├── includes/
│   ├── header.php
│   └── footer.php
├── data/                      # Database dosyası (gitignore'da)
├── admin_dashboard.php        # Admin paneli
├── company_admin_dashboard.php # Firma admin paneli
├── user_dashboard.php         # Kullanıcı paneli
├── index.php                  # Ana sayfa
├── login.php                  # Giriş sayfası
├── register.php               # Kayıt sayfası
├── ticket_purchase.php        # Bilet satın alma
├── my_tickets.php             # Biletlerim
├── cancel_ticket.php          # Bilet iptal
├── generate_pdf.php           # PDF oluşturma
├── config.php                 # Veritabanı ve genel ayarlar
├── setup_database.php         # Veritabanı kurulum
├── Dockerfile                 # Docker imaj tanımı
├── docker-compose.yml         # Docker orchestration
└── docker-entrypoint.sh       # Container başlangıç scripti
```

## 🔒 Güvenlik Özellikleri

- ✅ Password hashing (bcrypt)
- ✅ Session yönetimi
- ✅ XSS koruması
- ✅ SQL Injection koruması (PDO prepared statements)
- ✅ Rol bazlı erişim kontrolü

## 📝 Geliştirme Notları

- **Timezone:** Europe/Istanbul (Türkiye saati)
- **Session:** HTTP only cookies
- **Database:** SQLite (portable, lightweight)
- **PDF Library:** TCPDF veya FPDF önerilir

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/yeniOzellik`)
3. Değişikliklerinizi commit edin (`git commit -m 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/yeniOzellik`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje eğitim amaçlı geliştirilmiştir.

## 👨‍💻 Geliştirici

**Mehmet Yasin Uzun**
- GitHub: [@mehmetyasinuzun](https://github.com/mehmetyasinuzun)

## 📞 İletişim

Sorularınız için issue açabilir veya pull request gönderebilirsiniz.

---

**Not:** Bu proje bir otobus bileti satış platformu görev dökümanına göre geliştirilmiştir. Tüm özellikler ve kullanıcı rolleri dökümanda belirtildiği şekilde implement edilmiştir.
