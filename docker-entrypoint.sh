#!/bin/bash
set -e

echo "================================================"
echo "🚀 Bilet Satın Alma Platformu Başlatılıyor..."
echo "================================================"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Set proper timezone
print_info "Timezone ayarlanıyor: ${TZ:-Europe/Istanbul}"
if [ -n "$TZ" ]; then
    ln -snf /usr/share/zoneinfo/$TZ /etc/localtime
    echo $TZ > /etc/timezone
fi

# Create data directory if it doesn't exist
print_info "Data dizini kontrol ediliyor..."
if [ ! -d "/var/www/html/data" ]; then
    print_warning "Data dizini bulunamadı, oluşturuluyor..."
    mkdir -p /var/www/html/data
fi

# Set proper permissions (cross-platform compatible)
print_info "Dosya izinleri ayarlanıyor..."
chown -R www-data:www-data /var/www/html/data
chmod -R 777 /var/www/html/data

# Check if database exists
DB_PATH="/var/www/html/data/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    print_warning "Veritabanı bulunamadı!"
    print_info "Yeni veritabanı oluşturuluyor..."
    
    # Run database setup script
    php /var/www/html/setup_database.php > /dev/null 2>&1
    
    if [ -f "$DB_PATH" ]; then
        print_info "✅ Veritabanı başarıyla oluşturuldu!"
        print_info "📊 Test hesapları yüklendi"
    else
        print_error "❌ Veritabanı oluşturulamadı!"
        exit 1
    fi
else
    print_info "✅ Veritabanı mevcut"
fi

# Set database permissions
if [ -f "$DB_PATH" ]; then
    chmod 666 "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

# Display container information
print_info "================================================"
print_info "📦 Container Bilgileri:"
print_info "   • PHP Version: $(php -r 'echo PHP_VERSION;')"
print_info "   • SQLite Version: $(sqlite3 --version | cut -d' ' -f1)"
print_info "   • Apache Version: $(apache2 -v | head -n1 | cut -d' ' -f3)"
print_info "   • Timezone: ${TZ:-Europe/Istanbul}"
print_info "================================================"

# Test accounts information
print_info "👥 Test Hesapları:"
print_info "   • Admin: admin@admin.com / admin123"
print_info "   • Metro Admin: metro@admin.com / 123456"
print_info "   • Pamukkale Admin: pamukkale@admin.com / 123456"
print_info "   • Kullanıcı: user@test.com / 123456"
print_info "================================================"

print_info "🌐 Uygulama Erişim Bilgileri:"
print_info "   • URL: http://localhost:8080"
print_info "   • Health Check: http://localhost:8080/"
print_info "================================================"

print_info "✅ Sistem hazır! Apache başlatılıyor..."
echo ""

# Execute the main command (Apache)
exec "$@"
