<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Gestión Segura — Iniciar sesión</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="flex min-h-screen items-center justify-center bg-brand-gray-light px-4 text-brand-navy antialiased">
        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-[0_10px_30px_rgba(22,36,61,0.05)] sm:p-8">
            <h1 class="text-xl font-medium text-brand-navy">Gestión Segura</h1>
            <p class="mt-1 text-sm text-brand-gray">Acceso al panel administrativo.</p>

            @if (session('status'))
                <p class="mt-4 rounded-xl bg-brand-teal/15 px-4 py-3 text-sm text-brand-navy">{{ session('status') }}</p>
            @endif

            @if ($errors->any())
                <p class="mt-4 rounded-xl bg-brand-coral/10 px-4 py-3 text-sm text-brand-coral-text">
                    {{ $errors->first() }}
                </p>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Correo o usuario</span>
                    <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" class="field-control px-4 py-3" />
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Contraseña</span>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="field-control px-4 py-3 pr-16"
                        />
                        <button
                            type="button"
                            class="absolute top-1/2 right-3 -translate-y-1/2 text-xs font-medium text-brand-navy-mid hover:text-brand-navy"
                            onclick="const i = document.getElementById('password'); i.type = i.type === 'password' ? 'text' : 'password'; this.textContent = i.type === 'password' ? 'Mostrar contraseña' : 'Ocultar contraseña';"
                        >
                            Mostrar contraseña
                        </button>
                    </div>
                </label>

                <button type="submit" class="primary-action w-full">Iniciar sesión</button>
            </form>

            <div class="mt-4 flex items-center gap-3 text-xs text-brand-gray">
                <span class="h-px flex-1 bg-slate-200"></span>
                o
                <span class="h-px flex-1 bg-slate-200"></span>
            </div>

            <button
                type="button"
                disabled
                title="Próximamente"
                class="secondary-action mt-4 w-full cursor-not-allowed justify-center opacity-60"
            >
                Continuar con Google
            </button>

            <p class="mt-4 text-center text-sm">
                <a href="{{ route('password.request') }}" class="font-medium text-brand-navy-mid hover:text-brand-navy">
                    ¿Olvidaste tu contraseña?
                </a>
            </p>
        </div>
    </body>
</html>
