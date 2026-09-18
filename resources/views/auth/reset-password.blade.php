<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <title>Gestión Segura — Crear contraseña</title>
        @fonts
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8">
            <span class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-brand-amber/15 blur-3xl" aria-hidden="true"></span>
            <span class="pointer-events-none absolute -bottom-40 -right-32 h-[30rem] w-[30rem] rounded-full bg-brand-teal/20 blur-3xl" aria-hidden="true"></span>

            <section class="relative w-full max-w-lg rounded-[2rem] border border-white/80 bg-white px-5 py-7 shadow-[0_28px_80px_rgba(22,36,61,0.14)] sm:px-9 sm:py-9">
                <div class="flex items-center justify-between">
                    <a href="{{ route('wizard') }}" aria-label="Volver al cotizador">
                        <img src="{{ asset('image/logo.png') }}" alt="Gestión Segura" class="h-auto w-44">
                    </a>
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-gray-light text-brand-navy-mid">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                    </span>
                </div>

                <p class="mt-8 text-xs font-semibold tracking-[0.14em] text-brand-navy-mid uppercase">Protege tu cuenta</p>
                <h1 class="mt-2 font-heading text-3xl font-medium tracking-[-0.025em] text-brand-navy">Crea una nueva contraseña</h1>
                <p class="mt-3 text-sm leading-6 text-brand-gray">Usa al menos 8 caracteres y evita datos fáciles de adivinar.</p>

                @if ($errors->any())
                    <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-5 text-brand-coral-text" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 17h.01"/></svg>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" class="mt-7 space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <label class="block">
                        <span class="field-label">Correo electrónico</span>
                        <span class="field-control flex items-center gap-2.5 bg-slate-50 px-3.5">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            <input type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase outline-none">
                        </span>
                    </label>

                    <label class="block">
                        <span class="field-label">Nueva contraseña</span>
                        <span class="field-control flex items-center gap-2.5 px-3.5">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                            <input id="new-password" type="password" name="password" required autocomplete="new-password" minlength="8" placeholder="Mínimo 8 caracteres" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm outline-none">
                            <button type="button" data-password-toggle data-target="new-password" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-brand-gray transition hover:bg-brand-gray-light hover:text-brand-navy focus:outline-none focus:ring-2 focus:ring-brand-gold" aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg data-eye-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                <svg data-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5" aria-hidden="true"><path d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a15.5 15.5 0 0 1-2.1 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 3.1-.5"/></svg>
                                <span class="sr-only">Mostrar contraseña</span>
                            </button>
                        </span>
                    </label>

                    <label class="block">
                        <span class="field-label">Confirmar contraseña</span>
                        <span class="field-control flex items-center gap-2.5 px-3.5">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                            <input id="confirm-password" type="password" name="password_confirmation" required autocomplete="new-password" minlength="8" placeholder="Repite la contraseña" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm outline-none">
                            <button type="button" data-password-toggle data-target="confirm-password" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-brand-gray transition hover:bg-brand-gray-light hover:text-brand-navy focus:outline-none focus:ring-2 focus:ring-brand-gold" aria-label="Mostrar contraseña" aria-pressed="false">
                                <svg data-eye-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                <svg data-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5" aria-hidden="true"><path d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a15.5 15.5 0 0 1-2.1 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 3.1-.5"/></svg>
                                <span class="sr-only">Mostrar contraseña</span>
                            </button>
                        </span>
                    </label>

                    <button type="submit" class="primary-action w-full">Guardar nueva contraseña</button>
                </form>

                <a href="{{ route('login') }}" class="secondary-action mt-3 w-full">Volver a iniciar sesión</a>
            </section>
        </main>

        <script>
            document.querySelectorAll('[data-password-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.target);
                    const isVisible = input.type === 'text';

                    input.type = isVisible ? 'password' : 'text';
                    button.setAttribute('aria-pressed', String(!isVisible));
                    button.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                    button.querySelector('[data-eye-open]').classList.toggle('hidden', !isVisible);
                    button.querySelector('[data-eye-closed]').classList.toggle('hidden', isVisible);
                });
            });
        </script>
    </body>
</html>
