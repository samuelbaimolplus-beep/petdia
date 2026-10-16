# 🚀 Plan de Implementación y Prompts de Mejoras — La Petdiatría

Este documento contiene la **hoja de ruta completa, el diseño técnico paso a paso y los prompts de desarrollo listos para usar** para implementar las funcionalidades faltantes identificadas en el sistema clínico veterinario **"La Petdiatría"**.

---

## 📋 Índice del Plan

1. [Visión General y Fases de Implementación](#1-visión-general-y-fases-de-implementación)
2. [Fase 1: Comunicación y Operativa Rápida (Impacto Inmediato)](#2-fase-1-comunicación-y-operativa-rápida-impacto-inmediato)
   - [1.1 Botones de WhatsApp Directo con Plantillas Inteligentes](#11-botones-de-whatsapp-directo-con-plantillas-inteligentes)
   - [1.2 Egresos y Salidas de Caja Chica (Arqueo Neto)](#12-egresos-y-salidas-de-caja-chica-arqueo-neto)
   - [1.3 Entrada Rápida con Lector de Código de Barras (Pet Shop)](#13-entrada-rápida-con-lector-de-código-de-barras-pet-shop)
3. [Fase 2: Módulos Clínicos Avanzados (Aprovechamiento de la BD)](#3-fase-2-módulos-clínicos-avanzados-aprovechamiento-de-la-bd)
   - [2.1 Módulo Web de Hospitalización e Internación](#21-módulo-web-de-hospitalización-e-internación)
   - [2.2 Adjuntos de Laboratorio e Imágenes en Historial Clínico](#22-adjuntos-de-laboratorio-e-imágenes-en-historial-clínico)
   - [2.3 Programación de Cirugías y Consentimiento Informado](#23-programación-de-cirugías-y-consentimiento-informado)
4. [Fase 3: Auditoría, Trazabilidad y Optimización](#4-fase-3-auditoría-trazabilidad-y-optimización)
   - [3.1 Bitácora de Auditoría de Acciones Sensibles (Activity Log)](#31-bitácora-de-auditoría-de-acciones-sensibles-activity-log)
   - [3.2 Compresión y Redimensionamiento Automático de Fotos a WebP](#32-compresión-y-redimensionamiento-automático-de-fotos-a-webp)
5. [Fase 4: Portal Web del Propietario (Cliente Final)](#5-fase-4-portal-web-del-propietario-cliente-final)
   - [4.1 Portal de Autoservicio para Dueños de Mascotas](#41-portal-de-autoservicio-para-dueños-de-mascotas)
6. [🤖 Prompts de Desarrollo para IA / Agente](#6--prompts-de-desarrollo-para-ia--agente)

---

## 1. Visión General y Fases de Implementación

| Fase | Áreas Involucradas | Complejidad | Impacto | Estado de Implementación |
| :---: | :--- | :---: | :---: | :---: |
| **Fase 1** | WhatsApp Click-to-Chat, Egresos de Caja Chica, Código de Barras | Baja | 🔴 Inmediato en recepción y cobros | ✅ **100% Completado y Verificado** |
| **Fase 2** | Hospitalización/Caniles, Adjuntos de Laboratorio, Quirófano | Media-Alta | 🔴 Clínico y diferenciador comercial | ✅ **100% Completado y Verificado** |
| **Fase 3** | Registro de Auditoría (Activity Log), Optimización WebP | Media | 🟡 Cumplimiento y trazabilidad | ✅ **100% Completado y Verificado** |
| **Fase 4** | Portal de Autoservicio para Propietarios de Mascotas | Alta | 🟢 Fidelización y experiencia de usuario | ✅ **100% Completado y Verificado** |

---

## 2. Fase 1: Comunicación y Operativa Rápida (Impacto Inmediato)

### 1.1 Botones de WhatsApp Directo con Plantillas Inteligentes
* **Objetivo:** Permitir al personal abrir un chat de WhatsApp con un solo clic con mensajes personalizados pre-redactados.
* **Ubicaciones:**
  - En la lista de **Citas**: Botón *"Recordar Cita"* (`"Hola {propietario}, te recordamos que mañana {hora} {mascota} tiene su cita de {servicio} en La Petdiatría."`).
  - En **Recordatorios**: Botón *"Avisar Vacuna"* (`"Hola {propietario}, la vacuna {vacuna} de {mascota} vence el {fecha}."`).
  - En **Clientes / Ficha**: Botón *"Contactar"* con enlace directo al WhatsApp del dueño.
* **Archivos a modificar:**
  - `resources/views/citas/index.blade.php`
  - `resources/views/recordatorios/index.blade.php`
  - `resources/views/clientes/show.blade.php`

### 1.2 Egresos y Salidas de Caja Chica (Arqueo Neto)
* **Objetivo:** Registrar gastos operativos diarios menores (compra de insumos, pago de delivery, taxis) para que el balance de caja al cerrar turno sea exacto.
* **Cambios en Backend:**
  - Migración para tabla `egresos_caja` (`id_egreso`, `id_cierre_caja`, `id_usuario`, `monto`, `concepto`, `categoria`, `comprobante`, `fecha_hora`).
  - Extender `CajaWebController`: métodos `registrarEgreso()`, calcular `Total Cobros - Total Egresos = Saldo Final Esperado`.
* **Archivos a modificar:**
  - `app/Http/Controllers/Web/CajaWebController.php`
  - `resources/views/caja/index.blade.php` y `resources/views/caja/cierres.blade.php`

### 1.3 Entrada Rápida con Lector de Código de Barras (Pet Shop)
* **Objetivo:** Al usar una pistola lectora en el Pet Shop o presionar `Enter` en el input de código de barras, añadir el producto instantáneamente a la venta.
* **Componente Alpine.js:** Escucha de eventos de teclado en campo `codigo_barra` con auto-foco y búsqueda por código EAN en la tabla `productos`.
* **Archivos a modificar:**
  - `resources/views/petshop/index.blade.php`
  - `app/Http/Controllers/Web/PetShopWebController.php`

---

## 3. Fase 2: Módulos Clínicos Avanzados (Aprovechamiento de la BD)

### 2.1 Módulo Web de Hospitalización e Internación
* **Objetivo:** Interfaz gráfica para controlar pacientes hospitalizados (aprovechando las tablas existentes `internaciones` y `seguimiento_internaciones`).
* **Funcionalidades:**
  - Tablero visual de jaulas / caniles (Estado: *Libre*, *Ocupado*, *En Desinfección*).
  - Bitácora de evolución por turnos: registro de temperatura, pulso, sueroterapia, alimentación, defecación y comentarios médicos.
  - Generación de cargo automático de internación por día.
  - Botón de Alta Médica con resumen clínico.
* **Archivos a crear/modificar:**
  - `app/Http/Controllers/Web/InternacionWebController.php`
  - `resources/views/internacion/index.blade.php` y `show.blade.php`
  - Registrar ruta `Route::middleware('module:clinico')->resource('internaciones', ...)` en `routes/web.php`.

### 2.2 Adjuntos de Laboratorio e Imágenes en Historial Clínico
* **Objetivo:** Subir y visualizar estudios complementarios (hemogramas en PDF, radiografías y ecografías en JPG/PNG/DICOM) vinculados a la atención.
* **Cambios:**
  - Reutilizar la tabla existente `estudio_laboratorios`.
  - Subida de archivos con validación (`mimes:pdf,jpg,png,webp`, máx. 10MB).
  - Visor modal en el timeline del historial clínico con descarga directa del informe.
* **Archivos a modificar:**
  - `app/Http/Controllers/Web/HistorialClinicoWebController.php`
  - `resources/views/historial/show.blade.php`

### 2.3 Programación de Cirugías y Consentimiento Informado
* **Objetivo:** Gestión del quirófano con evaluación pre-quirúrgica y firma de autorización de anestesia.
* **Funcionalidades:**
  - Calendario de quirófano por profesional y tipo de cirugía.
  - Evaluación ASA (I al V) y chequeo de ayuno.
  - Emisión e impresión de **Consentimiento Informado Prequirúrgico** en PDF con DomPDF.

---

## 4. Fase 3: Auditoría, Trazabilidad y Optimización

### 4.1 Bitácora de Auditoría de Acciones Sensibles (Activity Log)
* **Objetivo:** Registrar quién hizo qué en acciones críticas (anulación de cobros, borrado de citas, cambios de precios, modificación de diagnósticos).
* **Diseño:**
  - Tabla `auditoria_logs` (`id`, `id_usuario`, `accion`, `entidad`, `id_entidad`, `datos_antes`, `datos_despues`, `ip`, `created_at`).
  - Observer de Eloquent o Middleware que capture automáticamente eventos clave.
  - Vista accesible solo para `SUPER_ADMIN` en `/usuarios/auditoria`.

### 4.2 Compresión y Redimensionamiento Automático de Fotos a WebP
* **Objetivo:** Procesar en el servidor las fotos de mascotas para redimensionarlas a máx. 800x800 px y convertirlas a `.webp` (ahorro del 80% de espacio en disco).

---

## 5. Fase 4: Portal Web del Propietario (Cliente Final)

### 4.1 Portal de Autoservicio para Dueños de Mascotas
* **Objetivo:** Permitir a los clientes consultar sus mascotas desde su celular sin ver datos internos de la clínica.
* **Funcionalidades:**
  - Acceso con email y contraseña o enlace mágico OTP.
  - Ver carnet de vacunas y próximas fechas.
  - Descargar recetas médicas emitidas en PDF.
  - Solicitar reserva de cita online con pre-aprobación de recepción.

---

## 6. 🤖 Prompts de Desarrollo para IA / Agente

Copia y pega cualquiera de los siguientes bloques según la funcionalidad que desees implementar:

---

### 🔹 PROMPT 1: Implementar WhatsApp Click-to-Chat y Egresos de Caja

```markdown
Actúa como desarrollador senior de Laravel y frontend Blade.
Trabajaremos sobre el proyecto "La Petdiatría" (Laravel 12, Tailwind v4, Alpine.js, PostgreSQL).

TAREAS A REALIZAR:

1. WhatsApp Directo (Click-to-Chat):
   - En `resources/views/citas/index.blade.php`, agrega un botón con ícono de WhatsApp en cada fila de cita que abra `https://wa.me/{telefono}?text={mensaje}` con target="_blank".
   - El mensaje debe estar codificado con urlencode e incluir: nombre del propietario, nombre de la mascota, fecha y hora de la cita, y nombre de la clínica.
   - Si el teléfono no tiene código de país, normalízalo (asume el código de tu región si faltan dígitos).
   - Replica botones similares en `resources/views/recordatorios/index.blade.php` (para vacunas y controles pendientes) y en `resources/views/clientes/show.blade.php`.

2. Módulo de Egresos de Caja Chica:
   - Crea una migración `create_egresos_caja_table` con: id_egreso, id_cierre_caja (nullable), id_usuario, monto (decimal 12,2), concepto (string), comprobante (string nullable), fecha_hora (timestamp).
   - En `CajaWebController`, crea los métodos `storeEgreso(Request $request)` para registrar una salida de dinero y valida monto y concepto.
   - En la vista de Caja (`resources/views/caja/index.blade.php`), agrega un botón modal "Registrar Egreso / Salida" y muestra en el resumen: Total Cobros, Total Egresos y Saldo Neto en Caja.
   - En el cierre de caja (`cierres.blade.php`), descuenta automáticamente los egresos del arqueo final.

REGLAS OBLIGATORIAS:
- Usa diseño coherente con el sistema Apple Liquid Glass y variantes `dark:`.
- Ejecuta `vendor/bin/pint --dirty --format agent` al terminar.
- Ejecuta los tests de caja para asegurar que sigan pasando.
```

---

### 🔹 PROMPT 2: Implementar Módulo Web de Hospitalización e Internación

```markdown
Actúa como desarrollador senior de Laravel 12.
Vas a construir la interfaz web completa para el módulo de Hospitalización e Internación de pacientes veterinarios, consumiendo las tablas ya existentes en la base de datos (`internaciones` y `seguimiento_internaciones`).

REQUERIMIENTOS:

1. Controlador Web y Rutas:
   - Crea `app/Http/Controllers/Web/InternacionWebController.php` con métodos: `index`, `create`, `store`, `show`, `storeSeguimiento`, `darAlta`.
   - Registra las rutas web en `routes/web.php` bajo el middleware de autenticación y módulo correspondiente: `Route::middleware(['auth', 'module:clinico'])->group(...)`.

2. Vistas Blade:
   - `resources/views/internacion/index.blade.php`: Tablero visual con selector de pestañas (Activas / Historial). Muestra tarjetas tipo "Canil / Jaula" con foto de la mascota, dueño, diagnóstico de ingreso, fecha de internación, veterinario a cargo y badge de estado.
   - `resources/views/internacion/show.blade.php`: Ficha detallada con:
     a) Datos del paciente y motivo de hospitalización.
     b) Formulario modal rápido para registrar evolución médica (constantes vitales, sueroterapia, medicación aplicada, evolución clínica).
     c) Timeline de seguimientos médicos ordenados cronológicamente.
     d) Botón de acción para "Dar de Alta Médica" que actualice la fecha/hora de salida y calcule los días internados.

3. Integración en el Menú de Navegación:
   - Añade el enlace "Internación" con ícono clínico en `resources/views/layouts/app.blade.php` en la sección clínica, visible para veterinarios y administradores.

REGLAS:
- Mantén el diseño Apple Liquid Glass con soporte para modo oscuro.
- Valida campos obligatorios en cada FormRequest/Request.
- Ejecuta `vendor/bin/pint --dirty --format agent` y ejecuta pruebas para validar.
```

---

### 🔹 PROMPT 3: Adjuntos de Laboratorio e Imágenes en Historial Clínico

```markdown
Actúa como desarrollador fullstack en Laravel 12.
Implementaremos la gestión de estudios de laboratorio e imágenes médicas (ecografías, radiografías, análisis de sangre) dentro del Historial Clínico Digital de "La Petdiatría".

TAREAS:

1. Base de Datos y Modelo:
   - Verifica el modelo `EstudioLaboratorio` y la tabla `estudio_laboratorios`. Asegúrate de que admita campos: `id_atencion`, `tipo_estudio`, `archivo_adjunto`, `observaciones`, `fecha_realizacion`.
   
2. Lógica en Controlador:
   - En `HistorialClinicoWebController` o un controlador dedicado, crea el endpoint para subir un estudio médico (`POST /historial/{id}/estudios`).
   - Valida el archivo: `mimes:pdf,jpg,jpeg,png,webp`, tamaño máximo de 10 MB.
   - Almacena el archivo en el disco público `storage/app/public/estudios/{id_paciente}/` con nombre único aleatorio.

3. Interfaz de Usuario:
   - En `resources/views/historial/show.blade.php`, crea una sección/tab "Estudios y Laboratorios".
   - Botón "+ Adjuntar Estudio" con modal Alpine.js y previsualización.
   - Visualizador de archivos: Miniatura si es imagen con modal para ver en pantalla completa; si es PDF, botón con icono de documento para abrir/descargar en nueva pestaña.

REGLAS:
- No rompas las pruebas existentes de `HistorialClinicoWebTest`.
- Ejecuta `vendor/bin/pint` al finalizar.
```

---

### 🔹 PROMPT 4: Registro de Auditoría de Acciones Sensibles (Activity Log)

```markdown
Actúa como arquitecto de software y experto en seguridad Laravel.
Necesitamos implementar una bitácora de auditoría inmutable en "La Petdiatría" para registrar operaciones sensibles realizadas por el personal.

REQUERIMIENTOS:

1. Migración y Modelo:
   - Crea la migración `create_auditoria_logs_table` con:
     - `id` (bigIncrements)
     - `id_usuario` (foreign key a `usuarios`, nullable en acciones de sistema)
     - `accion` (string: LOGIN, CREAR, ACTUALIZAR, ELIMINAR, ANULAR_COBRO, CAMBIO_PRECIO)
     - `modulo` (string: CAJA, CITAS, HISTORIAL, USUARIOS, INVENTARIO)
     - `entidad_tipo` (string nullable: modelo afectado)
     - `entidad_id` (string/int nullable)
     - `ip_address` (string 45)
     - `user_agent` (text nullable)
     - `detalles` (json: valores anteriores y valores nuevos)
     - `created_at` (timestamp)

2. Servicio de Auditoría:
   - Crea `app/Services/AuditService.php` con método estático `log($accion, $modulo, $detalles = [], $entidad = null)`.

3. Intercepción de Acciones Clave:
   - Registra eventos al: anular un cobro en caja, cambiar el rol de un usuario, dar de baja un producto del inventario o cancelar una cita.

4. Panel de Consulta (Solo SUPER_ADMIN):
   - Controlador `AuditoriaWebController@index` con filtros por fecha, usuario, módulo y acción.
   - Vista `resources/views/usuarios/auditoria.blade.php` con tabla paginada y modal para inspeccionar el JSON de cambios.
```
