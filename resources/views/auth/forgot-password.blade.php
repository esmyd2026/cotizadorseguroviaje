<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <title>Gestión Segura — Recuperar contraseña</title>
        @fonts
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8">
            <span class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-brand-amber/15 blur-3xl" aria-hidden="true"></span>
            <span class="pointer-events-none absolute -bottom-40 -right-32 h-[30rem] w-[30rem] rounded-full bg-brand-teal/20 blur-3xl" aria-hidden="true"></span>

            <section class="relative w-full max-w-md rounded-[2rem] border border-white/80 bg-white px-5 py-7 shadow-[0_28px_80px_rgba(22,36,61,0.14)] sm:px-9 sm:py-9">
                <div class="flex items-center justify-between">
                    <a href="{{ route('wizard') }}" aria-label="Volver al cotizador">
                        <img src="{{ asset('image/logo.png') }}" alt="Gestión Segura" class="h-auto w-44">
                    </a>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-gray-light text-brand-navy-mid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                    </span>
                </div>

                <p class="mt-8 text-xs font-semibold tracking-[0.14em] text-brand-navy-mid uppercase">Ayuda de acceso</p>
                <h1 class="mt-2 font-heading text-3xl font-medium tracking-[-0.025em] text-brand-navy">Recupera tu acceso</h1>
                <p class="mt-3 text-sm leading-6 text-brand-gray">Escribe el correo de tu cuenta. Si está registrado, recibirás un enlace seguro para crear una nueva contraseña.</p>

                @if (session('status'))
                    <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-5 text-emerald-800" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="mt-7 space-y-5">
                    @csrf
                    <label class="block">
                        <span class="field-label">Correo electrónico</span>
                        <span class="field-control flex items-center gap-2.5 px-3.5 {{ $errors->has('email') ? 'field-control--error' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="TU@CORREO.COM" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase outline-none">
                        </span>
                        @error('email')
                            <p class="form-error" role="alert">{{ $message }}</p>
                        @enderror
                    </label>

                    <button type="submit" class="primary-action w-full">
                        Enviar enlace de recuperación
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5" aria-hidden="true"><path d="M5 12h14M14 6l6 6-6 6"/></svg>
                    </button>
                </form>

                <a href="{{ route('login') }}" class="secondary-action mt-3 w-full">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path d="M19 12H5M10 6l-6 6 6 6"/></svg>
                    Volver a iniciar sesión
                </a>

                <p class="mt-5 text-center text-[11px] leading-5 text-brand-gray">El enlace de recuperación caduca en 60 minutos.</p>
            </section>
        </main>
    </body>
</html>
