<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Usuario;
use App\Services\TotpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PerfilWebController extends Controller
{
    /**
     * Muestra la pantalla de perfil del usuario autenticado.
     */
    public function index(Request $request, TotpService $totpService): View
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        $currentUser->loadMissing(['persona.profesional', 'roles']);

        $pending2FaSecret = null;
        $qrCodeUrl = null;

        // Si el usuario solicitó configurar 2FA y no lo tiene activo aún
        if (! $currentUser->hasTwoFactorEnabled()) {
            $pending2FaSecret = session('two_factor_pending_secret');
            if (! $pending2FaSecret) {
                $pending2FaSecret = $totpService->generateSecret();
                session(['two_factor_pending_secret' => $pending2FaSecret]);
            }

            $clinicaNombre = Configuracion::get('clinica_nombre', 'La Petdiatria');
            $qrCodeUrl = $totpService->getQrCodeUrl(
                $clinicaNombre,
                $currentUser->nombre_usuario,
                $pending2FaSecret
            );
        }

        return view('perfil.index', [
            'usuario' => $currentUser,
            'persona' => $currentUser->persona,
            'profesional' => $currentUser->persona?->profesional,
            'pending2FaSecret' => $pending2FaSecret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    /**
     * Actualiza el correo electrónico y teléfono de contacto del usuario.
     */
    public function update(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();
        $persona = $currentUser->persona;

        if (! $persona) {
            abort(404, 'No se encontró la persona vinculada a este usuario.');
        }

        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('personas', 'email')->ignore($persona->id_persona, 'id_persona'),
            ],
            'telefono' => ['nullable', 'string', 'max:20'],
        ], [
            'email.required' => 'El correo electrónico es obligatorio para la seguridad y recuperación de cuenta.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está enlazado a otra cuenta.',
        ]);

        $persona->update($validated);

        return back()->with('success_perfil', 'Tus datos de contacto han sido actualizados. Los códigos OTP de seguridad se enviarán a tu nuevo correo.');
    }

    /**
     * Cambia la contraseña del usuario con verificación de Have I Been Pwned y complejidad.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', 'different:current_password', Password::defaults()],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.different' => 'La nueva contraseña debe ser distinta a la actual.',
        ]);

        if (! Hash::check($request->input('current_password'), $currentUser->password_hash)) {
            return back()->withErrors([
                'current_password' => 'La contraseña actual no es correcta.',
            ]);
        }

        $currentUser->update([
            'password_hash' => Hash::make($request->input('password')),
        ]);

        // Rotar sesión por seguridad tras cambiar la contraseña
        $request->session()->regenerate(true);

        return back()->with('success_password', '¡Tu contraseña ha sido actualizada con éxito bajo los nuevos estándares de seguridad!');
    }

    /**
     * Confirma y activa la autenticación de dos factores (2FA TOTP).
     */
    public function confirmTwoFactor(Request $request, TotpService $totpService): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'Ingresa el código de 6 dígitos que muestra tu aplicación autenticadora.',
            'code.size' => 'El código debe tener exactamente 6 dígitos.',
        ]);

        $pendingSecret = session('two_factor_pending_secret');
        if (! $pendingSecret) {
            return back()->withErrors(['two_factor' => 'La sesión de configuración de 2FA ha expirado. Recarga la página.']);
        }

        if (! $totpService->verifyCode($pendingSecret, $request->input('code'))) {
            return back()->withErrors(['two_factor' => 'Código de verificación incorrecto o expirado. Revisa la hora de tu teléfono.']);
        }

        $recoveryCodes = $totpService->generateRecoveryCodes();

        $currentUser->update([
            'two_factor_secret' => $pendingSecret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => $recoveryCodes,
        ]);

        session()->forget('two_factor_pending_secret');

        return back()
            ->with('success_2fa', '¡Autenticación de Dos Factores (2FA) activada exitosamente!')
            ->with('recovery_codes', $recoveryCodes);
    }

    /**
     * Desactiva la autenticación de dos factores previa validación de contraseña.
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        /** @var Usuario $currentUser */
        $currentUser = Auth::user();

        $request->validate([
            'current_password' => ['required', 'string'],
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual para desactivar 2FA.',
        ]);

        if (! Hash::check($request->input('current_password'), $currentUser->password_hash)) {
            return back()->withErrors(['current_password_2fa' => 'Contraseña incorrecta. No se pudo desactivar el 2FA.']);
        }

        $currentUser->update([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ]);

        return back()->with('success_2fa', 'La autenticación de dos factores ha sido desactivada.');
    }
}
