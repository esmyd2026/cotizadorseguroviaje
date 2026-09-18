<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <meta name="description" content="Accede de forma segura a Gestión Segura.">
        <title>Gestión Segura — Iniciar sesión</title>
        @fonts
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <main class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8 sm:px-6 lg:py-12">
            <span class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-brand-amber/15 blur-3xl" aria-hidden="true"></span>
            <span class="pointer-events-none absolute -bottom-40 -right-32 h-[30rem] w-[30rem] rounded-full bg-brand-teal/20 blur-3xl" aria-hidden="true"></span>

            <section class="relative grid w-full max-w-5xl overflow-hidden rounded-[2rem] border border-white/80 bg-white shadow-[0_32px_90px_rgba(22,36,61,0.15)] lg:grid-cols-[0.92fr_1.08fr]">
                <aside class="relative hidden overflow-hidden bg-brand-navy p-10 text-white lg:flex lg:flex-col lg:justify-between">
                    <span class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-brand-amber/15" aria-hidden="true"></span>
                    <span class="absolute -bottom-24 -left-20 h-64 w-64 rounded-full border-[42px] border-white/5" aria-hidden="true"></span>

                    <div class="relative">
                        <a href="{{ route('wizard') }}" class="inline-flex rounded-2xl bg-white px-5 py-3 shadow-lg" aria-label="Volver al cotizador de Gestión Segura">
                            <img src="{{ asset('image/logo.png') }}" alt="Gestión Segura" class="h-auto w-48">
                        </a>

                        <p class="mt-12 text-xs font-semibold tracking-[0.16em] text-brand-amber uppercase">Tu protección, en un solo lugar</p>
                        <h1 class="mt-4 font-heading text-4xl font-medium leading-tight tracking-[-0.035em]">Bienvenido de nuevo.</h1>
                        <p class="mt-4 max-w-sm text-sm leading-7 text-white/65">Accede de forma segura a las herramientas y servicios de Gestión Segura.</p>
                    </div>

                    <div class="relative space-y-3">
                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-brand-amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>
                            </span>
                            Sesión protegida y acceso personal
                        </div>
                        <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white/80">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10 text-brand-amber">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M6.6 3h3l1.5 4-2 1.6a16 16 0 0 0 6.3 6.3l1.6-2 4 1.5v3c0 2-1.6 3.6-3.6 3.5A15.4 15.4 0 0 1 3.1 6.6C3 4.6 4.6 3 6.6 3Z"/></svg>
                            </span>
                            Asesoría profesional cuando la necesites
                        </div>
                    </div>
                </aside>

                <div class="px-5 py-7 sm:px-10 sm:py-10 lg:px-14 lg:py-12">
                    <div class="flex items-center justify-between lg:hidden">
                        <a href="{{ route('wizard') }}" aria-label="Volver al cotizador">
                            <img src="{{ asset('image/logo.png') }}" alt="Gestión Segura" class="h-auto w-40">
                        </a>
                        <a href="{{ route('wizard') }}" class="rounded-xl px-3 py-2 text-xs font-semibold text-brand-navy-mid transition hover:bg-brand-gray-light">Volver</a>
                    </div>

                    <div class="mt-9 lg:mt-0">
                        <p class="text-xs font-semibold tracking-[0.14em] text-brand-navy-mid uppercase">Acceso seguro</p>
                        <h2 class="mt-2 font-heading text-3xl font-medium tracking-[-0.025em] text-brand-navy">Inicia sesión</h2>
                        <p class="mt-2 text-sm leading-6 text-brand-gray">Ingresa tus credenciales para continuar.</p>
                    </div>

                    @if (session('status'))
                        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm leading-5 text-emerald-800" role="status">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="m8 12 2.5 2.5L16 9"/></svg>
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mt-5 flex items-start gap-2.5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-5 text-brand-coral-text" role="alert">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="mt-0.5 h-4 w-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 17h.01"/></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="mt-7 space-y-5">
                        @csrf

                        <label class="block">
                            <span class="field-label">Correo o número de identificación</span>
                            <span class="field-control flex items-center gap-2.5 px-3.5 {{ $errors->has('login') ? 'field-control--error' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                                <input type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="TU@CORREO.COM O TU CÉDULA/PASAPORTE" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase outline-none" />
                            </span>
                        </label>

                        <label class="block">
                            <span class="field-label">Contraseña</span>
                            <span class="field-control flex items-center gap-2.5 px-3.5 {{ $errors->has('password') ? 'field-control--error' : '' }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5 shrink-0 text-brand-navy-mid" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="Tu contraseña" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm outline-none" />
                                <button type="button" data-password-toggle data-target="login-password" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-brand-gray transition hover:bg-brand-gray-light hover:text-brand-navy focus:outline-none focus:ring-2 focus:ring-brand-gold" aria-label="Mostrar contraseña" aria-pressed="false">
                                    <svg data-eye-open viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"/><circle cx="12" cy="12" r="2.5"/></svg>
                                    <svg data-eye-closed viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="hidden h-5 w-5" aria-hidden="true"><path d="m3 3 18 18M10.6 10.7a2 2 0 0 0 2.7 2.7M9.9 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a15.5 15.5 0 0 1-2.1 2.8M6.2 6.2C3.8 8 2.5 12 2.5 12s3.5 7 9.5 7a9.7 9.7 0 0 0 3.1-.5"/></svg>
                                    <span class="sr-only">Mostrar contraseña</span>
                                </button>
                            </span>
                        </label>

                        <div class="flex items-center justify-between gap-4 text-xs">
                            <label class="flex cursor-pointer items-center gap-2 text-brand-gray">
                                <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="h-4 w-4 rounded border-slate-300 accent-brand-amber">
                                Recordarme
                            </label>
                            <a href="{{ route('password.request') }}" class="font-semibold text-brand-navy-mid transition hover:text-brand-navy hover:underline">¿Olvidaste tu contraseña?</a>
                        </div>

                        <button type="submit" class="primary-action w-full">
                            Iniciar sesión
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5" aria-hidden="true"><path d="M5 12h14M14 6l6 6-6 6"/></svg>
                        </button>
                    </form>

                    <div class="my-6 flex items-center gap-3" aria-hidden="true">
                        <span class="h-px flex-1 bg-slate-200"></span>
                        <span class="text-[10px] font-semibold tracking-[0.12em] text-slate-400 uppercase">o continúa con</span>
                        <span class="h-px flex-1 bg-slate-200"></span>
                    </div>

                    <button type="button" disabled title="Disponible cuando se configure Google OAuth" class="group relative flex min-h-[3.25rem] w-full cursor-not-allowed items-center justify-center gap-3 rounded-[0.9rem] border border-slate-300 bg-white px-4 text-sm font-medium text-[#3c4043] shadow-[0_2px_5px_rgba(60,64,67,0.12)] transition focus:outline-none focus:ring-2 focus:ring-[#4285f4]/30 disabled:opacity-75">
                        <svg viewBox="0 0 24 24" class="h-5 w-5 shrink-0" aria-hidden="true">
                            <path fill="#4285F4" d="M21.6 12.23c0-.71-.06-1.4-.19-2.07H12v3.92h5.38a4.6 4.6 0 0 1-2 3.02v2.54h3.24c1.9-1.75 2.98-4.33 2.98-7.41Z"/>
                            <path fill="#34A853" d="M12 22c2.7 0 4.97-.9 6.62-2.36l-3.24-2.54c-.9.6-2.05.96-3.38.96-2.61 0-4.82-1.76-5.61-4.13H3.04v2.62A10 10 0 0 0 12 22Z"/>
                            <path fill="#FBBC05" d="M6.39 13.93A6 6 0 0 1 6.08 12c0-.67.12-1.32.31-1.93V7.45H3.04A10 10 0 0 0 2 12c0 1.64.39 3.18 1.04 4.55l3.35-2.62Z"/>
                            <path fill="#EA4335" d="M12 5.94c1.47 0 2.79.5 3.83 1.5l2.87-2.88A9.64 9.64 0 0 0 12 2a10 10 0 0 0-8.96 5.45l3.35 2.62C7.18 7.7 9.39 5.94 12 5.94Z"/>
                        </svg>
                        <span class="font-sans tracking-normal">Continuar con Google</span>
                        <span class="absolute right-3 rounded-full bg-slate-100 px-2 py-0.5 text-[9px] font-semibold tracking-wide text-slate-500 uppercase">Próximamente</span>
                    </button>

                    <p class="mt-7 text-center text-xs leading-5 text-brand-gray">
                        ¿Necesitas ayuda? <a href="mailto:contacto@gestionsegura.com.ec" class="font-semibold text-brand-navy-mid hover:underline">Contacta a un asesor</a>
                    </p>

                    @if (config('demo.show_credentials') && config('demo.admin_password'))
                        <div class="mt-6 rounded-2xl border border-dashed border-brand-amber/50 bg-brand-amber/5 p-4 text-xs leading-5 text-brand-navy">
                            <p class="flex items-center gap-1.5 font-semibold tracking-wide text-brand-navy-mid uppercase">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4 shrink-0" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 8v.01M11 12h1v5h1"/></svg>
                                Acceso de demostración (solo entorno local)
                            </p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <div class="rounded-xl bg-white px-3 py-2 shadow-sm">
                                    <p class="font-semibold text-brand-navy">Administrador</p>
                                    <p class="mt-0.5 font-mono">{{ config('demo.admin_email') }}</p>
                                    <p class="font-mono">{{ config('demo.admin_password') }}</p>
                                </div>
                                <div class="rounded-xl bg-white px-3 py-2 shadow-sm">
                                    <p class="font-semibold text-brand-navy">Cliente demo</p>
                                    <p class="mt-0.5 font-mono">{{ config('demo.customer_document_id') }}</p>
                                    <p class="text-brand-gray">(usuario y contraseña son el mismo número)</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
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
