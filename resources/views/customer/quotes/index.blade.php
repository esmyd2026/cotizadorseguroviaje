<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <title>Gestión Segura — Mis seguros</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-5xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-amber text-brand-navy shadow-[0_6px_16px_rgba(250,182,0,0.22)]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                            <path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-heading text-sm font-medium leading-tight text-brand-navy">Gestión Segura</p>
                        <p class="text-[10px] font-medium tracking-[0.12em] text-brand-gray uppercase">Mi cuenta</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('wizard') }}" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-xs font-semibold text-brand-navy-mid transition hover:bg-brand-gray-light">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path d="M22 2 9.6 14.4" /><path d="m22 2-7.9 20-4.5-7.6L2 9.9 22 2Z" /></svg>
                        <span class="hidden sm:inline">Nueva cotización</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-xs font-semibold text-brand-gray transition hover:bg-brand-gray-light hover:text-brand-navy">
                            <span class="hidden sm:inline">Cerrar sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-[0.12em] text-brand-navy-mid uppercase">Mi cuenta</p>
                    <h1 class="mt-1 text-2xl font-medium tracking-tight text-brand-navy sm:text-3xl">Mis seguros</h1>
                    <p class="mt-1 text-sm text-brand-gray">Consulta el estado y el detalle de tus cotizaciones y pólizas.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1.5 text-xs font-medium text-brand-gray shadow-sm">
                    {{ $stats['total'] }} {{ $stats['total'] === 1 ? 'registro' : 'registros' }}
                </span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Cotizaciones</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Contratadas</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">{{ $stats['contracted'] }}</p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                @forelse ($quotes as $quote)
                    <article
                        class="cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)] transition hover:border-brand-amber/60 sm:p-5"
                        data-quote-detail='@json($quoteDetails[$quote->reference] ?? [])'
                        role="button"
                        tabindex="0"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-xs text-brand-gray">{{ $quote->reference }}</p>
                                <h2 class="mt-1 truncate font-heading text-base font-medium text-brand-navy sm:text-lg">{{ $quote->destination_country_name }}</h2>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $quote->status->value === 'contracted' ? 'bg-brand-teal/25 text-brand-navy' : 'bg-brand-amber/25 text-brand-navy' }}">
                                {{ $quote->status->label() }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-3 border-t border-slate-100 pt-4 text-xs">
                            <div>
                                <p class="text-brand-gray">Salida</p>
                                <p class="mt-0.5 font-medium text-brand-navy">{{ $quote->departure_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-brand-gray">Regreso</p>
                                <p class="mt-0.5 font-medium text-brand-navy">{{ $quote->return_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-brand-gray">Total</p>
                                <p class="mt-0.5 font-heading text-base font-medium text-brand-navy">USD ${{ number_format($quote->total, 2) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-right text-xs font-semibold text-brand-navy-mid">Ver detalle →</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-14 text-center text-sm text-brand-gray">
                        Todavía no tienes cotizaciones. <a href="{{ route('wizard') }}" class="font-semibold text-brand-navy-mid hover:underline">Cotiza tu seguro de viaje</a>.
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $quotes->links() }}
            </div>
        </main>

        @include('partials.quote-detail-modal')
    </body>
</html>
