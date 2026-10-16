# 🐾 La Petdiatría — Sistema de Gestión para Clínica Veterinaria

## Documentación Técnica y Manual Maestro del Sistema

> **Proyecto:** La Petdiatría — Sistema de Información Hospitalario y Ambulatorio Veterinario  
> **Área:** Ingeniería de Requisitos y Modelado de Software  
> **Stack:** Laravel 12 (PHP 8.2+) · PostgreSQL 16 · Tailwind CSS v4 · Alpine.js v3 · Apple Liquid Glass UI · Chart.js · Vite 7  

---

## 📋 Índice General

1. [Descripción General y Objetivos](#1-descripción-general-y-objetivos)
2. [Requisitos del Sistema e Instalación](#2-requisitos-del-sistema-e-instalación)
   - [2.1 Requisitos de Hardware y Red](#21-requisitos-de-hardware-y-red)
   - [2.2 Modalidad A: Instalación Nativa (Laptop / PC)](#22-modalidad-a-instalación-nativa-laptop--pc)
   - [2.3 Modalidad B: Entorno Contenerizado con Docker](#23-modalidad-b-entorno-contenerizado-con-docker)
   - [2.4 Matriz Comparativa: Nativa vs. Docker](#24-matriz-comparativa-nativa-vs-docker)
3. [Requerimientos del Sistema (RF y RNF)](#3-requerimientos-del-sistema-rf-y-rnf)
   - [3.1 Requerimientos Funcionales (RF-01 a RF-16)](#31-requerimientos-funcionales-rf-01-a-rf-16)
   - [3.2 Requerimientos No Funcionales (RNF-01 a RNF-10)](#32-requerimientos-no-funcionales-rnf-01-a-rnf-10)
4. [Arquitectura del Software y Estructura](#4-arquitectura-del-software-y-estructura)
   - [4.1 Patrón MVC y Flujo de Peticiones](#41-patrón-mvc-y-flujo-de-peticiones)
   - [4.2 Árbol Completo de Carpetas](#42-árbol-completo-de-carpetas)
5. [Backend y Lógica de Negocio](#5-backend-y-lógica-de-negocio)
   - [5.1 Controladores Web](#51-controladores-web)
   - [5.2 Controladores API REST](#52-controladores-api-rest)
   - [5.3 Modelos Eloquent y Relaciones](#53-modelos-eloquent-y-relaciones)
   - [5.4 Middleware de Seguridad y Autorización](#54-middleware-de-seguridad-y-autorización)
   - [5.5 Mailables y Servicios](#55-mailables-y-servicios)
6. [Frontend, Experiencia de Usuario y Diseño](#6-frontend-experiencia-de-usuario-y-diseño)
   - [6.1 Sistema de Diseño Apple Liquid Glass](#61-sistema-de-diseño-apple-liquid-glass)
   - [6.2 Componentes Blade Reutilizables](#62-componentes-blade-reutilizables)
   - [6.3 Compilación con Vite y Tailwind v4](#63-compilación-con-vite-y-tailwind-v4)
7. [Base de Datos (PostgreSQL 16)](#7-base-de-datos-postgresql-16)
   - [7.1 Esquema Relacional y Migraciones](#71-esquema-relacional-y-migraciones)
   - [7.2 Seeders y Factories](#72-seeders-y-factories)
   - [7.3 Triggers y Procedimientos Almacenados](#73-triggers-y-procedimientos-almacenados)
8. [Sistema de Seguridad y Auditoría OWASP](#8-sistema-de-seguridad-y-auditoría-owasp)
   - [8.1 Autenticación, 2FA TOTP y Recuperación OTP](#81-autenticación-2fa-totp-y-recuperación-otp)
   - [8.2 Control de Acceso por Roles (RBAC en Servidor)](#82-control-de-acceso-por-roles-rbac-en-servidor)
   - [8.3 Cabeceras HTTP de Seguridad (CSP, HSTS, X-Frame)](#83-cabeceras-http-de-seguridad-csp-hsts-x-frame)
9. [Guía Exhaustiva de Despliegue en Producción (VPS Ubuntu)](#9-guía-exhaustiva-de-despliegue-en-producción-vps-ubuntu)
   - [9.1 Arquitectura del Servidor de Producción](#91-arquitectura-del-servidor-de-producción)
   - [9.2 Despliegue Automatizado con Scripts](#92-despliegue-automatizado-con-scripts)
   - [9.3 Configuración de Nginx, Certbot SSL y Systemd](#93-configuración-de-nginx-certbot-ssl-y-systemd)
   - [9.4 Manual de Resolución de Errores Comunes](#94-manual-de-resolución-de-errores-comunes)
10. [Testing y Control de Calidad](#10-testing-y-control-de-calidad)
11. [Credenciales Predeterminadas y Comandos Útiles](#11-credenciales-predeterminadas-y-comandos-útiles)

---

## 1. Descripción General y Objetivos

**La Petdiatría** es un sistema integral de gestión hospitalaria y ambulatoria para clínicas veterinarias desarrollado bajo el framework **Laravel 12** y el motor relacional **PostgreSQL 16**. Cubre el flujo de trabajo clínico-administrativo de punta a punta:

- 🐕 **Gestión de Pacientes:** Fichas de mascotas con soporte para foto, especie, raza, microchip y propietarios vinculados.
- 📅 **Agenda Médica Inteligente:** Citas por estados (*Agendada*, *En Sala*, *Atendida*, *Cancelada*), gestión de turnos y bloqueos.
- 🩺 **Flujo Clínico Digital:** Anamnesis, registro de constantes vitales, diagnósticos presuntivos/definitivos, recetas y tratamientos.
- 📋 **Historial Clínico Continuo:** Timeline interactivo con paginación dinámica y autoguardado en `sessionStorage`.
- 💊 **Inventario y Farmacia:** Control de stock crítico, vencimiento de lotes (< 30 días) y kardex de movimientos.
- 🛒 **Punto de Venta (Pet Shop):** Facturación de mostrador y cobro integrado de atenciones y servicios.
- 💰 **Caja y Tesorería:** Cobros multimoneda y multimétodo (Efectivo, Tarjeta, Transferencia, QR), apertura y cierre de turnos con arqueo.
- 📄 **Documentos PDF Oficiales:** Emisión de recetas médicas, carnets de vacunación y tickets térmicos de caja (80mm).
- 📧 **Automatización y Notificaciones:** Envío de recordatorios por email vía Brevo SMTP y tareas programadas del sistema.
- 🔐 **Seguridad Bancaria:** Doble factor de autenticación (2FA TOTP), recuperación por código OTP de un solo uso, rotación de sesiones cada 15 min, bloqueo anti-fuerza bruta y cabeceras CSP.

---

## 2. Requisitos del Sistema e Instalación

### 2.1 Requisitos de Hardware y Red

| Componente | Requisito Mínimo | Requisito Recomendado |
| :--- | :--- | :--- |
| **Procesador (CPU)** | 2 núcleos x86-64 / ARM (Intel Core i3 8va gen, AMD Ryzen 3, Apple M1) | 4 núcleos o superior (Intel Core i5/i7, AMD Ryzen 5/7, Apple Silicon) |
| **Memoria RAM** | 4 GB (Instalación nativa) / 8 GB (Docker Desktop WSL2) | 8 GB a 16 GB |
| **Almacenamiento** | 5 GB libres en disco SSD | 10 GB a 20 GB libres SSD |
| **Resolución Pantalla** | 1280 x 720 px | 1920 x 1080 px (Full HD) responsive |
| **Conexión Red** | Requerida durante instalación (Composer, NPM, paquetes) | Estable (10+ Mbps) |

**Puertos de red requeridos:**
- **8000 TCP:** Servidor Web / Aplicación (`php artisan serve` o Nginx).
- **5432 TCP:** Motor de Base de Datos PostgreSQL.
- **5173 TCP:** Servidor de desarrollo Vite (Hot Module Replacement, opcional en dev).

---

### 2.2 Modalidad A: Instalación Nativa (Laptop / PC)

#### Software Requerido:
1. **PHP 8.2 o superior:** Con las extensiones habilitadas en `php.ini`:
   `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `curl`, `fileinfo`, `gd`, `zip`.
2. **Composer 2.x:** Gestor de paquetes PHP.
3. **Node.js (>= 18.x) y NPM (>= 9.x):** Entorno de compilación JavaScript.
4. **PostgreSQL (>= 14.x):** Servidor de base de datos relacional.
5. **Git:** Control de versiones.

#### Paso a Paso de Instalación:
```powershell
# 1. Clonar el repositorio
git clone <URL_REPOSITORIO> "mi-proyecto"
cd "mi-proyecto"

# 2. Instalar dependencias backend y frontend
composer install
npm install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar la base de datos en el archivo .env:
# DB_CONNECTION=pgsql
# DB_HOST=127.0.0.1
# DB_PORT=5432
# DB_DATABASE=veterinaria
# DB_USERNAME=postgres
# DB_PASSWORD=tu_contraseña

# 5. Ejecutar migraciones y semillero de datos
php artisan migrate --seed

# 6. Compilar assets estáticos
npm run build

# 7. Iniciar el servidor local
php artisan serve
```
Acceso en el navegador: `http://127.0.0.1:8000`.

---

### 2.3 Modalidad B: Entorno Contenerizado con Docker

El proyecto incluye arquitectura modular con Docker Compose:
- **`petdiatria_app`:** Contenedor PHP 8.2-FPM con extensiones PostgreSQL y código fuente montado.
- **`petdiatria_web`:** Servidor Nginx Alpine enlazado por socket al contenedor PHP.
- **`petdiatria_db`:** PostgreSQL 16 con volumen persistente (`postgres_data`).

#### Despliegue con Docker:
```bash
# 1. Copiar archivo de entorno
cp .env.example .env

# 2. Levantar los tres contenedores
docker-compose up -d --build

# 3. Inicializar base de datos y llaves dentro del contenedor
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link
```

---

### 2.4 Matriz Comparativa: Nativa vs. Docker

| Criterio | Modalidad Nativa | Modalidad Docker |
| :--- | :--- | :--- |
| **Consumo de Memoria** | Muy bajo (~150 MB RAM) | Moderado (~1.5 GB RAM por WSL2/Docker) |
| **Facilidad de Configuración** | Requiere instalar PHP, Node y Postgres | Un solo comando (`docker-compose up`) |
| **Velocidad de Respuesta** | Rendimiento máximo nativo | Ligero overhead de virtualización |
| **Ideal para** | Desarrollo diario en laptop | Pruebas de integración y despliegue a servidor |

---

## 3. Requerimientos del Sistema (RF y RNF)

### 3.1 Requerimientos Funcionales (RF)

| Código | Módulo | Requisito Funcional |
| :---: | :--- | :--- |
| **RF-01** | **Autenticación y Seguridad** | El sistema debe autenticar usuarios mediante usuario y contraseña con bcrypt 12, soporte para 2FA TOTP, verificación de correo por token (24h) y recuperación de contraseña mediante código OTP de 6 dígitos (15 min). |
| **RF-02** | **Control de Acceso (RBAC)** | El sistema debe verificar permisos estrictamente en el servidor (`CheckModuleAccess`) restringiendo rutas y menús según el rol (`SUPER_ADMIN`, `ADMIN`, `VETERINARIO`, `RECEPCIONISTA`, `PELUQUERO`, `CAJERO`). |
| **RF-03** | **Gestión de Clientes** | El sistema debe permitir el registro, consulta y actualización de propietarios con validación de documento de identidad único y datos de contacto. |
| **RF-04** | **Gestión de Pacientes** | El sistema debe registrar mascotas vinculadas a propietarios, asociando especie, raza, fecha de nacimiento, sexo, peso, microchip y fotografía subida (`mimes:jpg,png,webp`, máx. 2MB). |
| **RF-05** | **Agenda y Citas** | El sistema debe gestionar la agenda médica permitiendo crear citas rápidas con validación de conflictos de horario por profesional y estados (*Agendada*, *En Sala*, *Atendida*, *Cancelada*). |
| **RF-06** | **Atenciones Médicas** | El sistema debe registrar atenciones clínicas capturando motivo, anamnesis, constantes vitales (peso, temperatura, frecuencia cardíaca/respiratoria), diagnóstico presuntivo y definitivo. |
| **RF-07** | **Historial Clínico** | El sistema debe consolidar la ficha clínica en un timeline interactivo continuo con autoguardado en `sessionStorage` para prevenir pérdida de datos durante la consulta. |
| **RF-08** | **Recetas y Prescripciones** | El sistema debe permitir formular medicamentos con posología, frecuencia y duración, generando el documento PDF con matrícula profesional. |
| **RF-09** | **Inventario y Farmacia** | El sistema debe gestionar el catálogo de productos y medicamentos por lotes, fecha de caducidad y alertas visuales automáticas cuando el stock esté por debajo del mínimo. |
| **RF-10** | **Ventas y Pet Shop** | El sistema debe permitir ventas directas de mostrador descontando automáticamente existencias del inventario. |
| **RF-11** | **Caja y Facturación** | El sistema debe gestionar pagos vinculados a atenciones o ventas, admitiendo múltiples métodos de pago (Efectivo, Tarjeta, Transferencia, QR) y cierres de turno con arqueo. |
| **RF-12** | **Documentos PDF** | El sistema debe generar en PDF recetas médicas oficiales, carnets de vacunación y comprobantes térmicos de caja (80mm) con membrete configurable. |
| **RF-13** | **Recordatorios** | El sistema debe programar y despachar recordatorios preventivos de citas y vacunas por correo electrónico automático. |
| **RF-14** | **Servicio a Domicilio** | El sistema debe coordinar visitas móviles veterinarias asignando paciente, dirección y profesional responsable. |
| **RF-15** | **Buscador Global** | El sistema debe proveer una búsqueda instantánea en el encabezado con debounce (300ms) que localice clientes, mascotas y citas de forma unificada. |
| **RF-16** | **Tareas Programadas** | El sistema debe ejecutar una rutina clínica diaria automatizada que expire citas abandonadas, genere alertas de stock y recordatorios del día. |

---

### 3.2 Requerimientos No Funcionales (RNF)

| Código | Categoría | Requisito No Funcional |
| :---: | :--- | :--- |
| **RNF-01** | **Seguridad OWASP** | El sistema debe mitigar las 10 vulnerabilidades de OWASP: escape automático contra XSS, consultas parametrizadas contra SQL Injection, tokens CSRF en formularios mutativos y mitigación de enumeración de usuarios (CWE-204). |
| **RNF-02** | **Cabeceras HTTP** | Todas las respuestas web deben incluir cabeceras de protección: `Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff` y `Permissions-Policy`. |
| **RNF-03** | **Gestión de Sesiones** | Las sesiones deben guardarse en base de datos, expirar tras 30 minutos de inactividad continua y rotar su identificador de sesión cada 15 minutos para evitar secuestro de sesión (Session Fixation). |
| **RNF-04** | **Rendimiento** | Las vistas principales deben responder en menos de 500 ms bajo condiciones locales normales mediante carga ansiosa (Eager Loading) de relaciones Eloquent para prevenir consultas N+1. |
| **RNF-05** | **Usabilidad y Estética** | La interfaz debe implementar el sistema **Apple Liquid Glass** con superficies translúcidas (`backdrop-filter: blur(32px)`), física de respuesta elástica Apple y soporte nativo para Modo Oscuro. |
| **RNF-06** | **Disponibilidad** | El sistema debe ofrecer páginas de error personalizadas y descriptivas (403, 404, 419, 500) manteniendo el branding clínico sin mostrar fallos internos de Laravel. |
| **RNF-07** | **Integridad de Datos** | Todas las operaciones financieras y clínicas que involucren múltiples tablas deben ejecutarse dentro de transacciones de base de datos (`DB::transaction`). |
| **RNF-08** | **Mantenibilidad** | El código PHP debe cumplir con las directrices oficiales de Laravel Pint (`vendor/bin/pint`), tipos estrictos en métodos y separación modular en controladores y servicios. |
| **RNF-09** | **Respaldo de Información** | El sistema debe incluir un comando de consola (`php artisan db:backup`) capaz de exportar dumps de PostgreSQL con rotación automática. |
| **RNF-10** | **Rate Limiting** | El sistema debe limitar intentos de acceso a 5 intentos fallidos antes de bloquear temporalmente al usuario, y proteger la API con un límite de 60 peticiones/minuto por IP. |

---

## 4. Arquitectura del Software y Estructura

### 4.1 Patrón MVC y Flujo de Peticiones

```
┌─────────────────────────────────────────────────────────────┐
│                    NAVEGADOR (Cliente)                       │
│  Blade Templates + Alpine.js + Tailwind CSS + Lucide Icons  │
└────────────────────────────┬────────────────────────────────┘
                             │ HTTP (GET/POST/PUT/DELETE)
                             ▼
┌─────────────────────────────────────────────────────────────┐
│                    SERVIDOR (Laravel 12)                     │
│  ┌──────────┐  ┌──────────────┐  ┌────────────────────┐    │
│  │  Routes   │→│  Middleware   │→│    Controllers      │    │
│  │ web.php   │  │ (Seguridad)  │  │  (Web/ + Api/)     │    │
│  │ api.php   │  │              │  │                    │    │
│  └──────────┘  └──────────────┘  └─────────┬──────────┘    │
│                                             │               │
│  ┌──────────────┐  ┌───────────┐  ┌────────▼──────────┐    │
│  │   Services    │  │ Resources │  │     Models        │    │
│  │  (TotpService)│  │ (API JSON)│  │  (65 modelos)     │    │
│  └──────────────┘  └───────────┘  └────────┬──────────┘    │
│                                             │ Eloquent ORM  │
└─────────────────────────────────────────────┼───────────────┘
                                              ▼
┌─────────────────────────────────────────────────────────────┐
│                  PostgreSQL 16 (veterinaria)                 │
│  20 migraciones · ~65 tablas · Procedimientos y Triggers SQL│
└─────────────────────────────────────────────────────────────┘
```

---

### 4.2 Árbol Completo de Carpetas

```
mi-proyecto/
├── app/                              # Lógica principal del Backend
│   ├── Console/Commands/             # Comandos Artisan (Backup, Rutina diaria)
│   ├── Http/
│   │   ├── Controllers/Web/          # 23 Controladores web para vistas Blade
│   │   ├── Controllers/Api/          # 61 Controladores REST JSON
│   │   ├── Middleware/               # Seguridad (CheckModuleAccess, Headers, Sessions)
│   │   └── Resources/                # 60 Transformadores JSON (API Resources)
│   ├── Mail/                         # Mailables (PasswordResetOtp, Recordatorio, VerifyEmail)
│   ├── Models/                       # 65 Modelos Eloquent
│   ├── Providers/                    # AppServiceProvider (Rate Limiting, HTTPS, Passwords)
│   └── Services/                     # TotpService (2FA RFC 6238)
├── bootstrap/app.php                 # Pipeline de middleware, rutas y excepciones
├── config/                           # Archivos de configuración (app, auth, database, mail, etc.)
├── database/
│   ├── factories/                    # 64 Factories para generación de datos en pruebas
│   ├── migrations/                   # 20 Migraciones que definen el esquema relacional
│   ├── seeders/                      # Semilleros con datos clínicos realistas
│   └── sql/                          # Triggers y procedimientos PostgreSQL
├── deploy/                           # Automatización VPS (deploy.sh, setup-vps.sh, nginx.conf)
├── public/                           # Raíz web pública expuesta por el servidor
│   └── build/                        # CSS y JS compilados por Vite
├── resources/
│   ├── css/app.css                   # Tailwind CSS v4 + Liquid Glass styles
│   ├── js/app.js                     # Alpine.js + Lucide Icons + interactividad
│   └── views/                        # Vistas Blade (layouts, components, auth, módulos)
├── routes/
│   ├── web.php                       # Definición de rutas web y middleware de módulo
│   ├── api.php                       # Rutas de la API v1 con throttle
│   └── console.php                   # Comandos programados del Scheduler
└── tests/Feature/                    # 32 Suites de pruebas de integración y seguridad
```

---

## 5. Backend y Lógica de Negocio

### 5.1 Controladores Web (`app/Http/Controllers/Web/`)

| Controlador | Módulo | Responsabilidad Principal |
| :--- | :--- | :--- |
| `AuthController` | Autenticación | Login seguro, 2FA TOTP, activación de cuenta, cierre de sesión. |
| `PasswordResetController` | Recuperación | Envío y validación de OTP numérico de 6 dígitos con anti-enumeración. |
| `DashboardWebController` | Dashboard | Cálculo de KPIs en vivo (citas hoy, sala de espera, ingresos, alertas). |
| `CitaWebController` | Citas | Creación rápida, calendario, cambios de estado y control de conflictos. |
| `ClienteWebController` | Clientes/Mascotas | Registro de propietarios, carga de fotos de pacientes y fichas médicas. |
| `AtencionWebController` | Atenciones | Consulta clínica, constantes vitales y asociación de diagnósticos. |
| `HistorialClinicoWebController` | Historial | Timeline cronológico continuo con autoguardado en sesión. |
| `InventarioWebController` | Inventario | Control de stock mínimo, gestión de lotes farmacéuticos y kardex. |
| `PetShopWebController` | Pet Shop | Terminal de venta rápida de productos de mostrador. |
| `CompraWebController` | Compras | Registro de ingresos de mercadería de proveedores y lotes. |
| `CajaWebController` | Caja | Cobro multiforma de pago, emisión de comprobantes y cierres de turno. |
| `DocumentoPdfController` | Documentos PDF | Renderizado con DomPDF de recetas, carnets y tickets térmicos. |
| `BuscadorWebController` | Buscador | Búsqueda global unificada con debouncing para el topbar. |
| `NotificacionWebController` | Notificaciones | Centro de alertas (citas de hoy, inventario crítico, vacunas). |
| `UsuarioWebController` | Usuarios/Roles | Administración de personal, contraseñas y asignación RBAC. |
| `PerfilWebController` | Perfil | Actualización de datos del usuario autenticado y gestión de 2FA. |

---

### 5.2 Controladores API REST (`app/Http/Controllers/Api/`)
Contiene 61 controladores que implementan la especificación RESTful completa (`index`, `show`, `store`, `update`, `destroy`) para cada entidad de la clínica. Están protegidos con el middleware `throttle:api` (60 solicitudes/minuto).

---

### 5.3 Modelos Eloquent Principales
- **`Usuario`:** Maneja credenciales (`password_hash`), estados (`ACTIVO`, `INACTIVO`), timestamps de verificación y métodos RBAC (`hasRole`, `canAccessModule`).
- **`Paciente`:** Mascota con relación a `Propietario`, `Raza`, `Especie`, `FichaMedica`, `Citas` y `Atenciones`.
- **`Cita`:** Fechas, profesional asignado, paciente y control de estados de sala.
- **`Atencion`:** Encuentro médico con relación a diagnósticos, recetas y pagos asociados.
- **`Producto` / `Medicamento`:** Control de existencias físicas enlazadas a `Lotes`.
- **`Pago` / `DetallePago`:** Movimientos de tesorería y arqueo de caja.

---

### 5.4 Middleware de Seguridad
1. **`CheckModuleAccess`:** Autorización estricta por módulo en el servidor. Si el usuario no tiene permiso para el módulo solicitado, aborta con `403`.
2. **`EnforceSessionSecurity`:** Cierra la sesión si supera 30 minutos de inactividad y renueva el ID de sesión cada 15 minutos para frustrar ataques de fijación de sesión.
3. **`SecurityHeadersMiddleware`:** Aplica encabezados HTTP recomendados por OWASP (`Content-Security-Policy`, `X-Frame-Options`, `nosniff`, `Permissions-Policy`).

---

## 6. Frontend, Experiencia de Usuario y Diseño

### 6.1 Sistema de Diseño Apple Liquid Glass
Inspirado en los principios visuales de **macOS** y **visionOS**:
- **Superficies Translúcidas:** Desenfoque cáustico mediante `backdrop-filter: blur(32px) saturate(200%)`.
- **Lente Especular de Cristal:** Pseudoelementos que simulan refracción de luz ambiental.
- **Física de Deslizamiento y Haptics:** Curvas de animación elásticas oficiales de Apple (`cubic-bezier(0.32, 0.72, 0, 1)`) y compresión táctil suave (`scale(0.96)`) al interactuar.
- **Modo Oscuro Integrado:** Cristal templado ahumado (*Smoked Crystal Glass*) que adapta automáticamente la paleta de colores.

---

### 6.2 Componentes Blade Reutilizables (`resources/views/components/`)
El sistema cuenta con 18 componentes modulares:
- Botones con estado de carga (`<x-button>`)
- Modales de confirmación con teclado accesible (`<x-confirm-modal>`)
- Tarjetas de estadísticas para KPIs (`<x-stat-card>`)
- Badges de estado semánticos (`<x-badge>`, `<x-state-badge>`)
- Alertas y notificaciones emergentes (`<x-toast>`)
- Tablas de datos estilizadas (`<x-table>`)

---

## 7. Base de Datos (PostgreSQL 16)

### 7.1 Esquema Relacional
La base de datos `veterinaria` integra cerca de 65 tablas estructuradas mediante 20 migraciones secuenciales:
- **Catálogos Base:** `especies`, `razas`, `servicios`, `medicamentos`, `metodos_pago`.
- **Personas y Usuarios:** `personas`, `propietarios`, `profesionales`, `usuarios`, `roles`, `permisos`.
- **Clínica y Agenda:** `pacientes`, `citas`, `atenciones`, `fichas_medicas`, `diagnosticos`, `recetas`.
- **Comercial y Logística:** `productos`, `lotes`, `compras`, `ventas`, `pagos`, `cierres_caja`.

---

## 8. Sistema de Seguridad y Auditoría OWASP

### 8.1 Matriz de Mitigaciones de Seguridad

| Vulnerabilidad OWASP | Implementación en La Petdiatría |
| :--- | :--- |
| **A01: Broken Access Control** | Middleware estricto en el servidor (`CheckModuleAccess`), sin comprobaciones vulnerables del lado del cliente. |
| **A02: Cryptographic Failures** | Contraseñas con Bcrypt costo 12, tokens de sesión criptográficos y TOTP RFC 6238. |
| **A03: Injection (SQL / XSS)** | Consultas 100% parametrizadas con Eloquent ORM y escape HTML automático de Blade (`{{ }}`). |
| **A04: Insecure Design** | Anti-fuerza bruta en login (máx. 5 intentos, bloqueo exponencial) y desafío matemático. |
| **A05: Security Misconfiguration** | Encabezados CSP y protección contra framing / clickjacking en todas las respuestas. |
| **A07: Identification Failures** | Expiración de inactividad a los 30 min y rotación de identificador cada 15 min. |
| **CWE-204: Account Enumeration** | Respuestas unificadas y neutrales en recuperación de contraseña para no delatar correos registrados. |

---

## 9. Guía Exhaustiva de Despliegue en Producción (VPS Ubuntu)

### 9.1 Arquitectura del Servidor de Producción

```
[ Navegador del Usuario ]
           │ (HTTPS :443 / SSL Let's Encrypt)
           ▼
     [ Nginx ]  ─────────────────────────┐
     (Web Server)                        │ Archivos estáticos
           │ (.php requests)             │ (CSS, JS, imágenes, Vite build)
           ▼ (Unix Socket)               ▼
     [ PHP 8.2-FPM ]              [ /public ]
     (Laravel Framework)
           │ (TCP :5432)
           ▼
    [ PostgreSQL 16 ]
   (Base de Datos)
```

---

### 9.2 Despliegue Automatizado con Scripts

El proyecto incluye dos scripts en la carpeta `deploy/`:

1. **`deploy/setup-vps.sh`:** Prepara un servidor Ubuntu 22.04/24.04 virgen desde cero instalando PHP 8.2-FPM, extensiones, Nginx, PostgreSQL, Node.js y Composer.
2. **`deploy/deploy.sh`:** Automatiza el paso a producción descargando cambios de Git, compilando Vite, migrando la base de datos y optimizando cachés de Laravel.

#### Procedimiento de Puesta en Marcha:
```bash
# 1. Conectarse al VPS vía SSH
ssh root@IP_DEL_SERVIDOR

# 2. Clonar el repositorio en /var/www/
cd /var/www
git clone <URL_REPOSITORIO> la-petdiatria
cd /var/www/la-petdiatria

# 3. Ejecutar el aprovisionador inicial
bash deploy/setup-vps.sh

# 4. Configurar variables en .env (producción)
cp .env.example .env
nano .env

# Ajustar:
# APP_ENV=production
# APP_DEBUG=false
# APP_URL=https://tudominio.com
# DB_PASSWORD=ContraseñaGenerada

# 5. Ejecutar el despliegue
bash deploy/deploy.sh
```

---

### 9.3 Configuración de Nginx, Certbot SSL y Systemd

#### Configuración de Nginx (`/etc/nginx/sites-available/la-petdiatria`):
```nginx
server {
    listen 80;
    server_name tudominio.com www.tudominio.com;
    root /var/www/la-petdiatria/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Activar SSL gratuito con Certbot:
```bash
certbot --nginx -d tudominio.com -d www.tudominio.com
```

#### Configurar Tareas Programadas (Cron):
Añadir en `crontab -e`:
```bash
* * * * * cd /var/www/la-petdiatria && php artisan schedule:run >> /dev/null 2>&1
```

---

### 9.4 Manual de Resolución de Errores Comunes

| Error / Síntoma | Causa Frecuente | Solución Inmediata |
| :--- | :--- | :--- |
| **502 Bad Gateway** | PHP 8.2-FPM está detenido o el socket no coincide. | `sudo systemctl restart php8.2-fpm` y verificar la ruta del socket en la config de Nginx. |
| **500 Internal Server Error** | Falta la clave de cifrado o permisos en carpetas. | Ejecutar `php artisan key:generate` y dar permisos: `sudo chown -R www-data:www-data storage bootstrap/cache`. |
| **419 Page Expired** | Token CSRF caducado o permisos de sesión en base de datos. | Limpiar caché de sesión y verificar que la tabla `sessions` exista (`php artisan migrate`). |
| **Vite Manifest Not Found** | No se compilaron los assets frontend para producción. | Ejecutar `npm run build` en el servidor. |
| **Connection Refused (PostgreSQL)** | Servicio detenido o credenciales de `.env` erróneas. | `sudo systemctl status postgresql` y probar conexión con `psql -U usuario -d veterinaria`. |

---

## 10. Testing y Control de Calidad

El proyecto dispone de un conjunto exhaustivo de pruebas en `tests/Feature/` ejecutadas mediante **PHPUnit 11.5**:

```bash
# Ejecutar todas las pruebas del sistema
php artisan test --compact

# Ejecutar auditoría de seguridad y autenticación
php artisan test --filter=SecurityAuthenticationAuditTest

# Ejecutar verificación de cabeceras de protección HTTP
php artisan test --filter=SecurityHeadersTest

# Ejecutar flujo de recuperación y reseteo por código OTP
php artisan test --filter=PasswordResetOtpTest
```

---

## 11. Credenciales Predeterminadas y Comandos Útiles

### Cuentas Iniciales del Semillero (`VeterinariaSeeder`):
*(Contraseña común inicial en entorno de pruebas: `Password123!` o la configurada por el administrador)*

- **Super Administrador:** `superadmin` / `admin@lapetdiatria.com` (Control absoluto)
- **Administrador:** `admin` (Gestión clínica, inventario y finanzas)
- **Veterinario Principal:** `veterinario` (Consultas, atenciones y recetas)
- **Recepcionista:** `recepcion` (Agenda de citas y registro de clientes)
- **Cajero:** `cajero` (Módulo de cobros y cierres de caja)

### Comandos de Mantenimiento Frecuente:
```bash
# Respaldo manual de base de datos
php artisan db:backup

# Ejecución manual de rutina clínica diaria
php artisan rutina:diaria

# Limpieza profunda de cachés
php artisan optimize:clear

# Formateo de código según estándares oficiales de Laravel
vendor/bin/pint --format agent
```
