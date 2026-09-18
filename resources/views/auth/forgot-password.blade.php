<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Gestión Segura — Recuperar contraseña</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-brand-gray-light px-4 text-brand-navy antialiased">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(22,36,61,0.05)] sm:p-8">
            <h1 class="text-xl font-medium text-brand-navy">Recupera tu acceso</h1>
            <p class="mt-1 text-sm text-brand-gray">
                Ingresa tu correo y, si tiene una cuenta asociada, te enviaremos un enlace para restablecerla.
            </p>

            @if (session('status'))
                <p class="mt-4 rounded-xl bg-brand-teal/15 px-4 py-3 text-sm text-brand-navy">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="mt-4 rounded-xl bg-brand-coral/10 px-4 py-3 text-sm text-brand-coral-text">
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Correo</span>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="field-control px-4 py-3" />
                </label>

                <button type="submit" class="primary-action w-full">Enviar enlace</button>
            </form>

            <p class="mt-4 text-center text-sm">
                <a href="{{ route('login') }}" class="font-medium text-brand-navy-mid hover:text-brand-navy">
                    Volver a iniciar sesión
                </a>
            </p>
        </div>
    </body>
</html>
