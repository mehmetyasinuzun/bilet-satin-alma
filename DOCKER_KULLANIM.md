# Bilet Satın Alma Platformu - Docker

Bu proje Docker Container yapısı ile paketlenmiştir.

## Gereksinimler

- Docker
- Docker Compose

## Kurulum ve Çalıştırma

### 1. Docker Container'ı Oluşturun ve Başlatın

```bash
docker-compose up -d --build
```

### 2. Uygulamaya Erişin

Tarayıcınızda şu adresi açın:
```
http://localhost:8080
```

## Test Hesapları

Uygulama ilk çalıştığında otomatik olarak test hesapları oluşturulur:

- **Admin:** admin@admin.com / admin123
- **Metro Admin:** metro@admin.com / 123456  
- **Pamukkale Admin:** pamukkale@admin.com / 123456
- **Normal Kullanıcı:** user@test.com / 123456

## Docker Komutları

### Container'ı Durdurma
```bash
docker-compose down
```

### Container'ı Yeniden Başlatma
```bash
docker-compose restart
```

### Logları Görüntüleme
```bash
docker-compose logs -f
```

### Container İçine Girme
```bash
docker exec -it bilet-satin-alma bash
```

### Veritabanını Sıfırlama
```bash
# Container'ı durdurun
docker-compose down

# Data klasörünü temizleyin
rm -rf data/database.sqlite

# Container'ı tekrar başlatın (yeni database oluşturulacak)
docker-compose up -d
```

## Veri Kalıcılığı

- Veritabanı dosyası `./data` klasöründe saklanır
- Container silinse bile verileriniz korunur
- Verileri tamamen silmek için `data` klasörünü silin

## Port Yapılandırması

Varsayılan port 8080'dir. Değiştirmek için `docker-compose.yml` dosyasındaki ports ayarını düzenleyin:

```yaml
ports:
  - "8080:80"  # Sol taraf: host port, sağ taraf: container port
```

## Teknoloji Stack

- **Web Server:** Apache 2
- **PHP:** 8.2
- **Database:** SQLite
- **Container:** Docker
