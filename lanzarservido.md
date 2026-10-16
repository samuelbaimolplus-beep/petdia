# 🚀 Guía: Cómo Lanzar el Servidor Local y Exponerlo con Cloudflare

Esta guía explica paso a paso cómo iniciar el sistema **La Petdiatría** en tu máquina local y cómo exponerlo a internet de forma segura y gratuita con **Cloudflare Tunnel** (`cloudflared.exe`), permitiendo que cualquier persona o dispositivo (móvil, tablet, cliente externo) acceda a tu web mediante un enlace HTTPS seguro.

> Nota: Este archivo también está disponible en la carpeta de documentación del proyecto: [`TEXT/lanzarservido.md`](file:///d:/Universidad%20Carlos%20Villagra/2026-2/Ingenieria%20de%20requisitos%20y%20modelado%20-%20SC/mi-proyecto%20clone/TEXT/lanzarservido.md).

---

## 📋 Requisitos Previos

Antes de encender los servidores, asegúrate de tener:
1. **PostgreSQL** activo y corriendo en tu computadora.
2. Tu archivo `.env` configurado con la base de datos `veterinaria_dev` (o la que utilices).
3. Dependencias instaladas (`composer install` y `npm install`).

---

## 🖥️ Paso 1: Lanzar el Servidor Localmente

Para correr la aplicación necesitas **dos terminales** en la carpeta del proyecto:

### Terminal 1: Servidor Backend de Laravel
Abre una terminal (PowerShell o CMD) en la raíz del proyecto y ejecuta:

```powershell
php artisan serve --port=8000
```

> **¿Qué hace?** Inicia el servidor web PHP en el puerto 8000.  
> **Acceso local:** Abre tu navegador en [http://localhost:8000](http://localhost:8000) o [http://127.0.0.1:8000](http://127.0.0.1:8000).

---

### Terminal 2: Compilador de Estilos y Frontend (Vite)
Abre una segunda terminal en la raíz del proyecto y ejecuta:

```powershell
npm run dev
```

> **¿Qué hace?** Compila en tiempo real los estilos CSS (Tailwind v4), íconos Lucide y JavaScript (Alpine.js).  
> **Alternativa para producción (sin tener que dejar esta terminal abierta):**  
> Si prefieres compilar los archivos una sola vez y no tener una segunda terminal abierta, ejecuta:  
> ```powershell
> npm run build
> ```

---

## 🌐 Paso 2: Exponer el Servidor a Internet con Cloudflare Tunnel

El proyecto ya incluye el ejecutable oficial `cloudflared.exe` en la raíz. No necesitas instalar nada extra ni configurar routers ni abrir puertos en tu módem.

### Terminal 3: Iniciar el Túnel de Cloudflare
Abre una tercera terminal en la raíz del proyecto y ejecuta:

```powershell
.\cloudflared.exe tunnel --url http://localhost:8000
```

### 📌 ¿Cómo obtener tu enlace público?
En la terminal verás mensajes de conexión de Cloudflare. Busca unas líneas similares a esta:

```text
2026-09-27T... INF +--------------------------------------------------------------------------------------------+
2026-09-27T... INF |  Your quick Tunnel has been created! Visit it at (it may take some time to be reachable):  |
2026-09-27T... INF |  https://veterinaria-demo-abc123xyz.trycloudflare.com                                       |
2026-09-27T... INF +--------------------------------------------------------------------------------------------+
```

1. **Copia esa URL** que termina en `.trycloudflare.com`.
2. Pégala en el navegador de tu teléfono celular, compártela con clientes o pruébala desde cualquier red fuera de tu casa.
3. ¡Tu sistema ya estará disponible en internet con certificado SSL (HTTPS) automático y protección contra ataques DDoS!

---

## 🛑 Paso 3: Cómo Detener los Servidores

### Forma Estándar:
Ve a cada terminal donde tengas corriendo un proceso y presiona:
`Ctrl + C`

### Forma Rápida (Detener todo desde PowerShell):
Si cerraste las ventanas o quedaron procesos en segundo plano, abre PowerShell y ejecuta:

```powershell
Stop-Process -Name "php", "node", "cloudflared" -Force -ErrorAction SilentlyContinue
```

### Forma Rápida (Detener todo desde CMD):
```cmd
taskkill /F /IM php.exe /IM node.exe /IM cloudflared.exe
```

---

## ⚡ Automatización: Script de 1 Clic para Iniciar Todo

Para no tener que abrir 3 terminales manualmente cada vez, puedes crear un archivo llamado `iniciar.bat` en la raíz del proyecto con el siguiente contenido:

```bat
@echo off
title Lanzador La Petdiatria - Local + Cloudflare
echo ========================================================
echo   Iniciando La Petdiatria (Laravel + Vite + Cloudflare)
echo ========================================================

:: 1. Iniciar Laravel en segundo plano
start "Laravel Server" cmd /k "php artisan serve --port=8000"

:: 2. Iniciar Vite
start "Vite Dev" cmd /k "npm run dev"

:: 3. Esperar 3 segundos para que levante el servidor web
timeout /t 3 /nobreak >nul

:: 4. Iniciar Cloudflare Tunnel
start "Cloudflare Tunnel" cmd /k "cloudflared.exe tunnel --url http://localhost:8000"

echo.
echo Servidores iniciados en ventanas separadas.
echo Revisa la ventana de 'Cloudflare Tunnel' para ver tu enlace publico.
pause
```

---

## ❓ Preguntas Frecuentes y Solución de Problemas

### 1. `Failed to listen on 127.0.0.1:8000 (reason: ...)` (Puerto ocupado)
Significa que ya hay una instancia anterior de PHP corriendo en el puerto 8000.  
**Solución:** Cierra el proceso anterior con `Stop-Process -Name "php" -Force` o usa otro puerto:
```powershell
php artisan serve --port=8080
.\cloudflared.exe tunnel --url http://localhost:8080
```

### 2. Los estilos se ven rotos en el enlace de Cloudflare
Si estás usando `npm run dev`, Vite puede intentar cargar recursos desde `localhost:5173`.  
**Solución:** Compila los archivos para producción antes de abrir el túnel:
```powershell
npm run build
```
Luego recarga la página en tu enlace de Cloudflare.

### 3. Las imágenes o PDFs no cargan
Asegúrate de haber creado el enlace simbólico del storage ejecutando una sola vez:
```powershell
php artisan storage:link
```
