# 🛠️ Requerimientos y Comandos de Instalación Local

Guía rápida de comandos de terminal para instalar todas las dependencias y ejecutar **La Petdiatría** localmente en una computadora nueva.

---

## 1. Instalación de Requisitos en el Sistema Operativo

### En Windows (PowerShell como Administrador)

Puedes instalar todas las herramientas necesarias con **winget**:

```powershell
# 1. Instalar Git
winget install --id Git.Git -e --source winget

# 2. Instalar PHP 8.2 o superior
winget install --id PHP.PHP.8.2 -e --source winget

# 3. Instalar Composer (Gestor de dependencias de PHP)
winget install --id Composer.Composer -e --source winget

# 4. Instalar Node.js LTS (incluye NPM)
winget install --id OpenJS.NodeJS.LTS -e --source winget

# 5. Instalar PostgreSQL 16
winget install --id PostgreSQL.PostgreSQL -e --source winget
```

> **Habilitar extensiones en `php.ini` de Windows:**  
> Asegúrate de descomentar (quitar el punto y coma `;`) en tu archivo `php.ini`:
> ```ini
> extension=pdo_pgsql
> extension=pgsql
> extension=mbstring
> extension=openssl
> extension=curl
> extension=fileinfo
> extension=gd
> extension=zip
> ```

---

### En Linux (Ubuntu / Debian / WSL)

```bash
# 1. Actualizar repositorios del sistema
sudo apt update && sudo apt upgrade -y

# 2. Instalar Git, Curl y utilidades
sudo apt install -y git curl unzip zip software-properties-common

# 3. Agregar repositorio de PHP e instalar PHP 8.2 con sus extensiones
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2 php8.2-cli php8.2-fpm php8.2-pgsql php8.2-mbstring \
                    php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath

# 4. Instalar Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# 5. Instalar Node.js (v20 LTS) y NPM
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# 6. Instalar PostgreSQL 16
sudo apt install -y postgresql postgresql-contrib
sudo systemctl enable postgresql
sudo systemctl start postgresql
```

---

## 2. Verificar Requisitos en la Terminal

Abre una terminal nueva y comprueba que todos los comandos respondan su versión:

```powershell
git --version
php -v
composer -V
node -v
npm -v
psql --version
```

---

## 3. Preparar la Base de Datos PostgreSQL

Entra a la consola de PostgreSQL y crea la base de datos `veterinaria`:

### En Windows (desde terminal o SQL Shell / pgAdmin):
```powershell
psql -U postgres
```
```sql
CREATE DATABASE veterinaria;
\q
```

### En Linux:
```bash
sudo -u postgres psql -c "CREATE DATABASE veterinaria;"
```

---

## 4. Instalación de Dependencias del Proyecto

Abre la terminal en la carpeta raíz del proyecto (`mi-proyecto clone`):

```powershell
# 1. Instalar librerías de PHP (Backend)
composer install

# 2. Instalar paquetes de JavaScript y CSS (Frontend)
npm install
```

---

## 5. Configuración del Entorno (.env)

```powershell
# 1. Copiar archivo de configuración
cp .env.example .env

# 2. Generar clave criptográfica única de la aplicación
php artisan key:generate
```

> **Ajustar credenciales de PostgreSQL en `.env`:**
> ```ini
> DB_CONNECTION=pgsql
> DB_HOST=127.0.0.1
> DB_PORT=5432
> DB_DATABASE=veterinaria
> DB_USERNAME=postgres
> DB_PASSWORD=tu_contraseña_aqui
> ```

---

## 6. Inicializar la Base de Datos

Ejecuta las migraciones y carga los datos de prueba iniciales:

```powershell
php artisan migrate --seed
```

---

## 7. Compilar Frontend y Ejecutar el Servidor Local

### Opción A (Recomendada: Modo Desarrollo con cambios en vivo)

Abre **dos terminales**:

* **Terminal 1 (Backend):**
  ```powershell
  php artisan serve
  ```
  *(Disponible en: `http://127.0.0.1:8000`)*

* **Terminal 2 (Frontend / Vite):**
  ```powershell
  npm run dev
  ```

---

### Opción B (Modo Producción Local: Una sola terminal)

```powershell
# 1. Compilar los archivos estáticos de CSS y JS
npm run build

# 2. Iniciar el servidor Laravel
php artisan serve
```

---

## 8. Resumen de Comandos en una sola vista (Cheatsheet)

```powershell
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```
