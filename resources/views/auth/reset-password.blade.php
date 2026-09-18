<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Gestión Segura — Crear contraseña</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-brand-gray-light px-4 text-brand-navy antialiased">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(22,36,61,0.05)] sm:p-8">
            <h1 class="text-xl font-medium text-brand-navy">Crea tu contraseña</h1>
            <p class="mt-1 text-sm text-brand-gray">Tu cuenta se creó automáticamente al cotizar con nosotros.</p>

            @if ($errors->any())
                <p class="mt-4 rounded-xl bg-brand-coral/10 px-4 py-3 text-sm text-brand-coral-text">
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Correo</span>
                    <input type="email" name="email" value="{{ old('email', $email) }}" required autofocus autocomplete="username" class="field-control px-4 py-3 uppercase" />
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Nueva contraseña</span>
                    <input type="password" name="password" required autocomplete="new-password" minlength="8" class="field-control px-4 py-3" />
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Confirmar contraseña</span>
                    <input type="password" name="password_confirmation" required autocomplete="new-password" minlength="8" class="field-control px-4 py-3" />
                </label>

                <button type="submit" class="primary-action w-full">Guardar contraseña</button>
            </form>
        </div>
    </body>
</html>
