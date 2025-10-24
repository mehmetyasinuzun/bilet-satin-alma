#!/bin/bash

# Data dizininin izinlerini ayarla
chown -R www-data:www-data /var/www/html/data
chmod -R 777 /var/www/html/data

# Eğer database yoksa oluştur
if [ ! -f /var/www/html/data/database.sqlite ]; then
    echo "Database bulunamadı, oluşturuluyor..."
    php /var/www/html/setup_database.php > /dev/null 2>&1
    echo "Database başarıyla oluşturuldu!"
fi

# Apache'yi başlat
apache2-foreground
