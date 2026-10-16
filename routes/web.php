<?php

use App\Http\Controllers\Web\AtencionWebController;
use App\Http\Controllers\Web\AuditoriaWebController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\BuscadorWebController;
use App\Http\Controllers\Web\CajaWebController;
use App\Http\Controllers\Web\CirugiaWebController;
use App\Http\Controllers\Web\CitaWebController;
use App\Http\Controllers\Web\ClienteWebController;
use App\Http\Controllers\Web\ConfiguracionWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\DocumentoPdfController;
use App\Http\Controllers\Web\HistorialClinicoWebController;
use App\Http\Controllers\Web\NotificacionWebController;
use App\Http\Controllers\Web\PasswordResetController;
use App\Http\Controllers\Web\PerfilWebController;
use App\Http\Controllers\Web\ProfesionalWebController;
use App\Http\Controllers\Web\ReporteWebController;
use App\Http\Controllers\Web\UsuarioWebController;
use Illuminate\Support\Facades\Route;

// Redirección inicial según estado de autenticación
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // SEC: solo POST — evita logout CSRF via img/link

// Verificación y Activación de Cuenta mediante Código OTP (24h expirable)
Route::get('/activar-cuenta', [AuthController::class, 'showActivationForm'])->name('activacion.form');
Route::post('/activar-cuenta', [AuthController::class, 'verifyActivationOtp'])->name('activacion.verify');
Route::post('/reenviar-codigo-activacion', [AuthController::class, 'resendActivationOtp'])->name('activacion.resend');
Route::get('/verificar-email/{token}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
Route::post('/configurar-contrasena', [AuthController::class, 'setupPassword'])->name('verification.setup-password');
Route::post('/reenviar-verificacion', [AuthController::class, 'resendVerification'])->name('verification.resend');

// Autenticación de Dos Factores (2FA TOTP)
Route::get('/login/2fa', [AuthController::class, 'showTwoFactor'])->name('login.2fa');
Route::post('/login/2fa', [AuthController::class, 'verifyTwoFactor'])->name('login.2fa.post');

// Recuperación de Contraseña con Código OTP por Correo (15 min expirable)
Route::get('/recuperar-contrasena', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
Route::post('/recuperar-contrasena', [PasswordResetController::class, 'sendOtp'])->name('password.email');
Route::get('/restablecer-contrasena', [PasswordResetController::class, 'showOtpForm'])->name('password.otp');
Route::post('/restablecer-contrasena', [PasswordResetController::class, 'resetPasswordWithOtp'])->name('password.update');
Route::post('/reenviar-otp', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');

// Switcher de rol de prueba / demostración — SOLO disponible en entorno local o tests
if (app()->isLocal() || app()->runningUnitTests()) {
    Route::post('/demo/switch-role', [AuthController::class, 'switchDemoRole'])->name('demo.switch-role');
}

// Rutas protegidas del panel
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

    // Mi Cuenta & Seguridad (Cambio de contraseña, correo enlazado y configuración 2FA TOTP)
    Route::get('/perfil', [PerfilWebController::class, 'index'])->name('perfil.index');
    Route::put('/perfil', [PerfilWebController::class, 'update'])->name('perfil.update');
    Route::put('/perfil/password', [PerfilWebController::class, 'updatePassword'])->name('perfil.password');
    Route::post('/perfil/2fa/confirm', [PerfilWebController::class, 'confirmTwoFactor'])->name('perfil.2fa.confirm');
    Route::post('/perfil/2fa/disable', [PerfilWebController::class, 'disableTwoFactor'])->name('perfil.2fa.disable');

    // Buscador Global
    Route::get('/buscar', [BuscadorWebController::class, 'buscar'])->name('buscar');

    // Notificaciones en tiempo real
    Route::get('/notificaciones', [NotificacionWebController::class, 'index'])->name('notificaciones.index');
    Route::patch('/notificaciones/{id}/leer', [NotificacionWebController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/leer-todas', [NotificacionWebController::class, 'marcarTodas'])->name('notificaciones.leer-todas');

    // Módulo Paso 2: Usuarios, Roles y Permisos (Solo Super-Admin protegido en servidor)
    Route::middleware('module:usuarios')->group(function () {
        Route::get('/usuarios', [UsuarioWebController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioWebController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{id}', [UsuarioWebController::class, 'update'])->name('usuarios.update');
        Route::post('/usuarios/{id}/toggle-estado', [UsuarioWebController::class, 'toggleEstado'])->name('usuarios.toggle-estado');
        Route::post('/roles', [UsuarioWebController::class, 'storeRol'])->name('roles.store');
        Route::put('/roles/{id}', [UsuarioWebController::class, 'updateRol'])->name('roles.update');
        Route::post('/roles/permisos', [UsuarioWebController::class, 'updatePermisos'])->name('roles.update-permisos');
        Route::post('/roles/permisos/update', [UsuarioWebController::class, 'updatePermisos'])->name('roles.permisos.update');
        Route::post('/roles/{id}/toggle-estado', [UsuarioWebController::class, 'toggleRolEstado'])->name('roles.toggle-estado');
        Route::get('/usuarios/accesos', [UsuarioWebController::class, 'accesos'])->name('usuarios.accesos');
        Route::get('/usuarios/auditoria', [AuditoriaWebController::class, 'index'])->name('usuarios.auditoria');
    });

    // Módulo Paso 3: Clientes y Mascotas (CU9, CU10, CU11, CU12, CU14)
    Route::middleware('module:clientes')->group(function () {
        Route::get('/clientes', [ClienteWebController::class, 'index'])->name('clientes.index');
        Route::post('/clientes', [ClienteWebController::class, 'store'])->name('clientes.store');
        Route::get('/clientes/{id}', [ClienteWebController::class, 'show'])->name('clientes.show');
        Route::put('/clientes/{id}', [ClienteWebController::class, 'update'])->name('clientes.update');
        Route::post('/clientes/{id}/mascotas', [ClienteWebController::class, 'storeMascota'])->name('clientes.mascotas.store');
        Route::get('/mascotas/{id}', [ClienteWebController::class, 'showMascota'])->name('mascotas.show');
        Route::put('/mascotas/{id}', [ClienteWebController::class, 'updateMascota'])->name('mascotas.update');
        Route::get('/mascotas/{id}/carnet', [DocumentoPdfController::class, 'carnetVacunacionPdf'])->name('mascotas.carnet.pdf');

        // Catálogo Especies y Razas (Soporte para Mascotas)
        Route::get('/catalogo/especies-razas', [ClienteWebController::class, 'catalogoEspeciesRazas'])->name('catalogo.especies-razas');
        Route::post('/catalogo/especies', [ClienteWebController::class, 'storeEspecie'])->name('catalogo.especies.store');
        Route::put('/catalogo/especies/{id}', [ClienteWebController::class, 'updateEspecie'])->name('catalogo.especies.update');
        Route::post('/catalogo/razas', [ClienteWebController::class, 'storeRaza'])->name('catalogo.razas.store');
        Route::put('/catalogo/razas/{id}', [ClienteWebController::class, 'updateRaza'])->name('catalogo.razas.update');
        Route::get('/api/especies/{id}/razas', [ClienteWebController::class, 'getRazasByEspecie'])->name('api.especies.razas');
    });

    // Módulo Paso 7: Profesionales (CU53 - Registrar Profesionales, exclusivo Admin y Super-Admin)
    Route::middleware('module:profesionales')->prefix('profesionales')->name('profesionales.')->group(function () {
        Route::get('/', [ProfesionalWebController::class, 'index'])->name('index');
        Route::post('/', [ProfesionalWebController::class, 'store'])->name('store');
        Route::get('/{id}', [ProfesionalWebController::class, 'show'])->name('show');
        Route::put('/{id}', [ProfesionalWebController::class, 'update'])->name('update');
        Route::post('/{id}/toggle-estado', [ProfesionalWebController::class, 'toggleEstado'])->name('toggle-estado');
        Route::match(['put', 'post'], '/{id}/servicios', [ProfesionalWebController::class, 'updateServicios'])->name('update-servicios');
        Route::post('/{id}/horario', [ProfesionalWebController::class, 'storeHorario'])->name('store-horario');
        Route::delete('/horario/{id}', [ProfesionalWebController::class, 'deleteHorario'])->name('delete-horario');
        Route::post('/{id}/usuario', [ProfesionalWebController::class, 'storeUsuario'])->name('store-usuario');
    });

    // Módulo Paso 4: Historial Clínico (CU20 - Gestionar Acceso al Historial Clínico, protegido por backend y roles)
    Route::get('/historial-clinico', [HistorialClinicoWebController::class, 'index'])->name('historial.index');
    Route::get('/historial-clinico/{id}', [HistorialClinicoWebController::class, 'show'])->name('historial.show');
    Route::get('/historial-clinico/{id}/receta/pdf', [DocumentoPdfController::class, 'recetaPdf'])->name('historial.receta.pdf');
    Route::post('/historial-clinico/{id}/entradas', [HistorialClinicoWebController::class, 'storeEntradaClinica'])->name('historial.entradas.store');
    Route::post('/historial-clinico/{id}/estudios', [HistorialClinicoWebController::class, 'storeEstudio'])->name('historial.estudios.store');
    Route::delete('/historial-clinico/{id}/estudios/{idProcedimiento}', [HistorialClinicoWebController::class, 'destroyEstudio'])->name('historial.estudios.destroy');
    Route::delete('/historial-clinico/entradas/{id}', [HistorialClinicoWebController::class, 'anularEntrada'])->name('historial.entradas.anular');

    // =========================================================================
    // CICLO 2: Agenda, Citas, Disponibilidad y Bloqueos (CU13, CU14, CU29, CU30)
    // =========================================================================
    Route::middleware('module:citas')->prefix('citas')->name('citas.')->group(function () {
        Route::get('/', [CitaWebController::class, 'index'])->name('index');
        Route::post('/', [CitaWebController::class, 'store'])->name('store');
        Route::get('/disponibilidad', [CitaWebController::class, 'consultarDisponibilidad'])->name('disponibilidad');
        Route::post('/urgente', [CitaWebController::class, 'urgente'])->name('urgente');
        Route::match(['put', 'post'], '/{id}/reprogramar', [CitaWebController::class, 'reprogramar'])->name('reprogramar');
        Route::match(['put', 'post'], '/{id}/cambiar-estado', [CitaWebController::class, 'cambiarEstado'])->name('cambiar-estado');
        Route::match(['put', 'post'], '/{id}/cancelar', [CitaWebController::class, 'cancelar'])->name('cancelar');
        Route::post('/bloqueos', [CitaWebController::class, 'storeBloqueo'])->name('bloqueos.store');
        Route::delete('/bloqueos/{id}', [CitaWebController::class, 'deleteBloqueo'])->name('bloqueos.destroy');
    });

    // =========================================================================
    // CICLO 2: Atenciones Médicas, Diagnósticos, Tratamientos, Servicios y Observaciones (CU9, CU15, CU31, CU32, CU33, CU34)
    // =========================================================================
    Route::middleware('module:atenciones')->prefix('atenciones')->name('atenciones.')->group(function () {
        Route::get('/', [AtencionWebController::class, 'index'])->name('index');
        Route::get('/crear', [AtencionWebController::class, 'create'])->name('create');
        Route::post('/', [AtencionWebController::class, 'store'])->name('store');
        Route::get('/urgente', [AtencionWebController::class, 'createUrgente'])->name('create-urgente');
        Route::post('/urgente', [AtencionWebController::class, 'storeUrgente'])->name('store-urgente');
        Route::post('/urgente/store', [AtencionWebController::class, 'storeUrgente'])->name('urgente.store');
        Route::get('/{id}', [AtencionWebController::class, 'show'])->name('show');
        Route::post('/{id}/finalizar', [AtencionWebController::class, 'finalizar'])->name('finalizar');
        Route::post('/{id}/cancelar', [AtencionWebController::class, 'cancelar'])->name('cancelar');
        Route::post('/{id}/diagnosticos', [AtencionWebController::class, 'storeDiagnostico'])->name('diagnosticos.store');
        Route::match(['put', 'post'], '/{id}/diagnosticos/{idDiagnostico}', [AtencionWebController::class, 'updateDiagnostico'])->name('diagnosticos.update');
        Route::post('/{id}/tratamientos', [AtencionWebController::class, 'storeTratamiento'])->name('tratamientos.store');
        Route::match(['put', 'post'], '/{id}/tratamientos/{idTratamiento}', [AtencionWebController::class, 'updateTratamiento'])->name('tratamientos.update');
        Route::post('/{id}/servicios', [AtencionWebController::class, 'storeServicio'])->name('servicios.store');
        Route::delete('/{id}/servicios/{idServicio}', [AtencionWebController::class, 'deleteServicio'])->name('servicios.delete');
        Route::post('/{id}/profesionales', [AtencionWebController::class, 'storeParticipante'])->name('profesionales.store');
        Route::post('/{id}/participantes', [AtencionWebController::class, 'storeParticipante'])->name('participantes.store');
        Route::delete('/{id}/profesionales/{idParticipacion}', [AtencionWebController::class, 'deleteParticipante'])->name('profesionales.delete');
        Route::delete('/{id}/participantes/{idParticipacion}', [AtencionWebController::class, 'deleteParticipante'])->name('participantes.delete');
        Route::post('/{id}/observaciones', [AtencionWebController::class, 'storeObservacion'])->name('observaciones.store');
    });

    // =========================================================================
    // CICLO 2 / Quirófano: Cirugías, Consentimiento y Controles Postquirúrgicos (CU11)
    // =========================================================================
    Route::middleware('module:cirugias')->prefix('cirugias')->name('cirugias.')->group(function () {
        Route::get('/', [CirugiaWebController::class, 'index'])->name('index');
        Route::post('/', [CirugiaWebController::class, 'store'])->name('store');
        Route::get('/{id}', [CirugiaWebController::class, 'show'])->name('show');
        Route::post('/{id}/estado', [CirugiaWebController::class, 'cambiarEstado'])->name('cambiar-estado');
        Route::post('/{id}/consentimiento', [CirugiaWebController::class, 'confirmarConsentimiento'])->name('consentimiento');
        Route::get('/{id}/consentimiento/pdf', [DocumentoPdfController::class, 'consentimientoCirugiaPdf'])->name('consentimiento.pdf');
        Route::post('/{id}/controles', [CirugiaWebController::class, 'storeControl'])->name('controles.store');
        Route::delete('/{id}/controles/{idControl}', [CirugiaWebController::class, 'deleteControl'])->name('controles.destroy');
    });

    // =========================================================================
    // CICLO 2 / Reportes: Panel de Analítica, Vacunaciones y Pacientes Atendidos (CU38, CU39)
    // =========================================================================
    Route::middleware('module:reportes')->prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', [ReporteWebController::class, 'index'])->name('index');
        Route::get('/vacunaciones', [ReporteWebController::class, 'vacunaciones'])->name('vacunaciones');
        Route::get('/pacientes-atendidos', [ReporteWebController::class, 'pacientesAtendidos'])->name('pacientes-atendidos');
        Route::get('/exportar/{tipo}', [ReporteWebController::class, 'exportar'])->name('exportar');
    });

    // =========================================================================
    // Módulo de Caja y Cobros (Integración contable sin duplicar cobros)
    // =========================================================================
    Route::middleware('module:caja')->prefix('caja')->name('caja.')->group(function () {
        Route::get('/', [CajaWebController::class, 'index'])->name('index');
        Route::get('/cobro', [CajaWebController::class, 'cobroForm'])->name('cobro');
        Route::post('/cobro', [CajaWebController::class, 'procesarCobro'])->name('cobro.store');
        Route::get('/cierre', [CajaWebController::class, 'cierreForm'])->name('cierre');
        Route::post('/cierre', [CajaWebController::class, 'procesarCierre'])->name('cierre.store');
        Route::post('/pagos/{pago}/anular', [CajaWebController::class, 'anularPago'])->name('anular');
        Route::get('/deudas', [CajaWebController::class, 'deudas'])->name('deudas');
        Route::post('/egresos', [CajaWebController::class, 'storeEgreso'])->name('egresos.store');
    });

    // Módulo de Configuración General de la Clínica
    Route::middleware('module:configuracion')->prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/', [ConfiguracionWebController::class, 'index'])->name('index');
        Route::put('/', [ConfiguracionWebController::class, 'update'])->name('update');
    });
});
