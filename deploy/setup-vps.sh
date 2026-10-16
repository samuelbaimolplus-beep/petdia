#!/usr/bin/env bash
# ==============================================================================
# Script de Instalación Automatizada para La Petdiatría en Ubuntu 22.04 / 24.04
# Ejecutar como usuario root o con sudo: bash setup-vps.sh
# ==============================================================================

set -e

echo "🚀 Iniciando aprovisionamiento del servidor para La Petdiatría..."

# 1. Actualizar repositorios del sistema
echo "📦 Actualizando paquetes del sistema..."
apt-get update && apt-get upgrade -y
apt-get install -y curl git unzip zip software-properties-common ufw

# 2. Agregar repositorio PPA para PHP 8.2
echo "🐘 Instalando PHP 8.2 y extensiones requeridas..."
add-apt-repository -y ppa:ondrej/php
apt-get update
apt-get install -y \
    php8.2-fpm \
    php8.2-cli \
    php8.2-pgsql \
    php8.2-mbstring \
    php8.2-xml \
    php8.2-curl \
    php8.2-zip \
    php8.2-gd \
    php8.2-intl \
    php8.2-bcmath \
    php8.2-opcache

# 3. Instalar Nginx
echo "🌐 Instalando y configurando Nginx..."
apt-get install -y nginx

# 4. Instalar PostgreSQL
echo "🐘 Instalando PostgreSQL..."
apt-get install -y postgresql postgresql-contrib

# 5. Instalar Composer (Gestor de dependencias PHP)
if ! command -v composer &> /dev/null; then
    echo "🎼 Instalando Composer..."
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

# 6. Instalar Node.js y NPM (versión 20 LTS para Vite)
if ! command -v node &> /dev/null; then
    echo "⚡ Instalando Node.js 20 LTS..."
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

# 7. Configuración de Base de Datos PostgreSQL
DB_NAME="${DB_NAME:-veterinaria}"
DB_USER="${DB_USER:-veterinaria_user}"
DB_PASS="${DB_PASS:-}"

if [ -z "$DB_PASS" ]; then
    echo "⚠️ DB_PASS no fue proporcionada por variable de entorno. Generando contraseña aleatoria..."
    DB_PASS=$(openssl rand -base64 16 | tr -dc 'a-zA-Z0-9' | head -c 16)
    echo "🔑 Contraseña generada para PostgreSQL: $DB_PASS"
    echo "⚠️ Por favor guárdela y colóquela en su archivo .env (DB_PASSWORD=$DB_PASS)"
fi

echo "🗄️ Creando base de datos y usuario PostgreSQL..."
sudo -u postgres psql -c "CREATE USER $DB_USER WITH PASSWORD '$DB_PASS';" || true
sudo -u postgres psql -c "CREATE DATABASE $DB_NAME OWNER $DB_USER;" || true
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE $DB_NAME TO $DB_USER;" || true

# 8. Directorio de la aplicación
APP_DIR="/var/www/la-petdiatria"
echo "📂 Verificando directorio de la aplicación en $APP_DIR..."
mkdir -p "$APP_DIR"

# 9. Configuración de Nginx
echo "🔧 Configurando VirtualHost en Nginx..."
cat << 'EOF' > /etc/nginx/sites-available/la-petdiatria
server {
    listen 80;
    listen [::]:80;
    server_name _;

    root /var/www/la-petdiatria/public;
    index index.php index.html;

    client_max_body_size 25M;
    charset utf-8;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|webp|svg|css|js|woff|woff2)$ {
        expires 30d;
        access_log off;
        add_header Cache-Control "public, max-age=2592000, immutable";
        try_files $uri =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 180;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    access_log /var/log/nginx/la-petdiatria_access.log;
    error_log /var/log/nginx/la-petdiatria_error.log error;
}
EOF

# Habilitar el sitio y desactivar el default
ln -sf /etc/nginx/sites-available/la-petdiatria /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t
systemctl reload nginx

# 10. Configurar Firewall (UFW)
echo "🛡️ Configurando Firewall (SSH + HTTP + HTTPS)..."
ufw allow OpenSSH
ufw allow 'Nginx Full'
echo "y" | ufw enable || true

echo "✅ ¡Servidor aprovisionado con éxito!"
echo "➡️ Siguiente paso: Sube o clona tu código en $APP_DIR y ejecuta bash deploy.sh"
