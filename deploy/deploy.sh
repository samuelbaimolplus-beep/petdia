#!/usr/bin/env bash
# ==============================================================================
# Script de Despliegue y Actualización Continua de La Petdiatría
# Ejecutar dentro de /var/www/la-petdiatria
# ==============================================================================

set -e

APP_DIR="/var/www/la-petdiatria"
cd "$APP_DIR"

echo "🚀 Iniciando despliegue de La Petdiatría..."

# 1. Poner aplicación en modo mantenimiento temporal si existe artisan
if [ -f artisan ]; then
    php artisan down || true
fi

# 2. Instalar / actualizar dependencias de Composer sin dev y optimizado
echo "📦 Instalando dependencias de PHP..."
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# 3. Instalar dependencias de Node.js y compilar frontend para producción
echo "🎨 Compilando frontend con Vite..."
npm install --no-audit
npm run build

# 4. Asegurar archivo .env
if [ ! -f .env ]; then
    echo "⚠️ Archivo .env no encontrado. Creando a partir de .env.example..."
    cp .env.example .env
    php artisan key:generate --force
fi

# 5. Ejecutar migraciones de base de datos
echo "🗄️ Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 6. Crear enlace simbólico a archivos subidos (fotos mascotas, etc.)
echo "🔗 Verificando enlace de almacenamiento..."
php artisan storage:link || true

# 7. Limpiar y reconstruir caché para máximo rendimiento
echo "⚡ Optimizando caché de Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Corregir permisos de directorios de almacenamiento y caché
echo "🔒 Ajustando permisos del servidor web (www-data)..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# 9. Reactivar aplicación
echo "🟢 Reactivando aplicación..."
php artisan up

# 10. Reiniciar PHP-FPM para vaciar OPcache
systemctl restart php8.2-fpm

echo "🎉 ¡Despliegue completado con éxito y la aplicación está en línea!"
