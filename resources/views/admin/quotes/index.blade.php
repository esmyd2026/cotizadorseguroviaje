<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="theme-color" content="#16243D">
        <title>Gestión Segura — Cotizaciones y seguros</title>
        @vite(['resources/css/app.css'])
    </head>
    <body class="min-h-screen bg-brand-gray-light text-brand-navy antialiased">
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-amber text-brand-navy shadow-[0_6px_16px_rgba(250,182,0,0.22)]">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5" aria-hidden="true">
                            <path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z" />
                            <path d="m9 12 2 2 4-4" />
                        </svg>
                    </span>
                    <div>
                        <p class="font-heading text-sm font-medium leading-tight text-brand-navy">Gestión Segura</p>
                        <p class="text-[10px] font-medium tracking-[0.12em] text-brand-gray uppercase">Administración</p>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <a href="{{ route('wizard') }}" class="flex min-h-10 items-center gap-2 rounded-xl px-3 text-xs font-semibold text-brand-navy-mid transition hover:bg-brand-gray-light">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path d="M22 2 9.6 14.4" /><path d="m22 2-7.9 20-4.5-7.6L2 9.9 22 2Z" /></svg>
                        <span class="hidden sm:inline">Ir al cotizador</span>
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

        <main class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10 lg:px-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold tracking-[0.12em] text-brand-navy-mid uppercase">Gestión comercial</p>
                    <h1 class="mt-1 text-2xl font-medium tracking-tight text-brand-navy sm:text-3xl">Cotizaciones y seguros</h1>
                    <p class="mt-1 text-sm text-brand-gray">Consulta y filtra las solicitudes registradas.</p>
                </div>
                <span class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1.5 text-xs font-medium text-brand-gray shadow-sm">
                    {{ $quotes->total() }} {{ $quotes->total() === 1 ? 'registro' : 'registros' }}
                </span>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Cotizaciones</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">{{ $stats['total'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Contratadas</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">{{ $stats['contracted'] }}</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Conversión</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">{{ $stats['conversion_rate'] }}%</p>
                </div>
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)]">
                    <p class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Ingresos contratados</p>
                    <p class="mt-1 font-heading text-2xl font-medium text-brand-navy">USD ${{ number_format($stats['revenue'], 2) }}</p>
                </div>
            </div>

            <form method="GET" class="mt-6 grid gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_10px_30px_rgba(22,36,61,0.05)] sm:grid-cols-[1fr_auto_auto_auto] sm:items-end">
                <label class="flex-1">
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Buscar</span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Referencia, cliente, identificación o destino"
                        class="field-control px-4 py-2.5"
                    />
                </label>

                <label>
                    <span class="mb-1.5 block text-sm font-medium text-brand-navy">Estado</span>
                    <select
                        name="status"
                        class="field-control px-4 py-2.5 sm:min-w-40"
                    >
                        <option value="">Todos</option>
                        @foreach ($statuses as $option)
                            <option value="{{ $option->value }}" @selected($status === $option->value)>
                                {{ $option->label() }}
                            </option>
                        @endforeach
                    </select>
                </label>

                <button
                    type="submit"
                    class="primary-action min-h-[3.25rem]"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path d="M4 5h16M7 12h10M10 19h4" /></svg>
                    Filtrar
                </button>

                @if ($search !== '' || $status)
                    <a href="{{ route('admin.quotes.index') }}" class="flex min-h-[3.25rem] items-center justify-center rounded-xl px-3 text-sm font-medium text-brand-gray hover:bg-brand-gray-light hover:text-brand-navy">
                        Limpiar
                    </a>
                @endif
            </form>

            <div class="mt-5 space-y-3 md:hidden">
                @forelse ($quotes as $quote)
                    <article
                        class="cursor-pointer rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_8px_24px_rgba(22,36,61,0.05)] transition hover:border-brand-amber/60"
                        data-quote-detail='@json($quoteDetails[$quote->reference] ?? [])'
                        role="button"
                        tabindex="0"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-mono text-xs text-brand-gray">{{ $quote->reference }}</p>
                                <h2 class="mt-1 truncate font-heading text-base font-medium text-brand-navy">{{ $quote->insured->first_name }} {{ $quote->insured->last_name }}</h2>
                                <p class="text-xs text-brand-gray">ID {{ $quote->insured->document_id }}</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-semibold {{ $quote->status->value === 'contracted' ? 'bg-brand-teal/25 text-brand-navy' : 'bg-brand-amber/25 text-brand-navy' }}">
                                {{ $quote->status->label() }}
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 text-xs">
                            <div>
                                <p class="text-brand-gray">Destino</p>
                                <p class="mt-0.5 font-medium text-brand-navy">{{ $quote->destination_country_name }}</p>
                            </div>
                            <div>
                                <p class="text-brand-gray">Fechas</p>
                                <p class="mt-0.5 font-medium text-brand-navy">{{ $quote->departure_date->format('d/m') }} → {{ $quote->return_date->format('d/m/Y') }}</p>
                            </div>
                            <div>
                                <p class="text-brand-gray">Creación</p>
                                <p class="mt-0.5 font-medium text-brand-navy">{{ $quote->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-brand-gray">Total</p>
                                <p class="mt-0.5 font-heading text-base font-medium text-brand-navy">USD ${{ number_format($quote->total, 2) }}</p>
                            </div>
                        </div>
                        <p class="mt-3 text-right text-xs font-semibold text-brand-navy-mid">Ver detalle →</p>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white px-4 py-12 text-center text-sm text-brand-gray">No se encontraron cotizaciones.</div>
                @endforelse
            </div>

            <div class="mt-5 hidden overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-[0_10px_30px_rgba(22,36,61,0.05)] md:block">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase">
                        <tr>
                            <th class="px-4 py-3">Referencia</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Identificación</th>
                            <th class="px-4 py-3">Destino</th>
                            <th class="px-4 py-3">Salida</th>
                            <th class="px-4 py-3">Regreso</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3">Estado</th>
                            <th class="px-4 py-3">Creación</th>
                            <th class="px-4 py-3"><span class="sr-only">Detalle</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($quotes as $quote)
                            <tr
                                class="cursor-pointer transition-colors hover:bg-brand-gray-light/70"
                                data-quote-detail='@json($quoteDetails[$quote->reference] ?? [])'
                                role="button"
                                tabindex="0"
                            >
                                <td class="px-4 py-3 font-mono text-slate-700">{{ $quote->reference }}</td>
                                <td class="px-4 py-3 text-slate-800">{{ $quote->insured->first_name }} {{ $quote->insured->last_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $quote->insured->document_id }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $quote->destination_country_name }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $quote->departure_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-slate-600">{{ $quote->return_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-brand-navy">USD ${{ number_format($quote->total, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span
                                        class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold {{ $quote->status->value === 'contracted' ? 'bg-brand-teal/25 text-brand-navy' : 'bg-brand-amber/25 text-brand-navy' }}"
                                    >
                                        {{ $quote->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-slate-500">{{ $quote->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3 text-right text-xs font-semibold text-brand-navy-mid">Ver detalle →</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-slate-500">
                                    No se encontraron cotizaciones.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $quotes->links() }}
            </div>
        </main>

        <div id="quote-modal" class="fixed inset-0 z-50 hidden items-start justify-center overflow-y-auto bg-brand-navy/60 px-4 py-8 backdrop-blur-sm sm:items-center" data-modal-close-backdrop>
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-[0_20px_60px_rgba(22,36,61,0.25)]">
                <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 sm:px-6">
                    <div>
                        <p id="qm-reference" class="font-mono text-xs text-brand-gray"></p>
                        <h2 id="qm-name" class="mt-1 font-heading text-lg font-medium text-brand-navy"></h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <span id="qm-status" class="shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold"></span>
                        <button type="button" data-modal-close class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-brand-gray transition hover:bg-brand-gray-light hover:text-brand-navy" aria-label="Cerrar">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-5 w-5" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-5 sm:px-6">
                    <section>
                        <h3 class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Asegurado</h3>
                        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                            <div><dt class="text-xs text-brand-gray">Identificación</dt><dd id="qm-document" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Correo</dt><dd id="qm-email" class="font-medium break-all text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Teléfono</dt><dd id="qm-phone" class="font-medium text-brand-navy"></dd></div>
                        </dl>
                    </section>

                    <section>
                        <h3 class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Viaje</h3>
                        <p id="qm-trip-type" class="mt-2 text-sm text-brand-navy"></p>
                        <ul id="qm-destinations" class="mt-2 space-y-1 text-sm text-brand-navy"></ul>
                        <dl class="mt-3 grid grid-cols-3 gap-x-4 gap-y-2 text-sm">
                            <div><dt class="text-xs text-brand-gray">Salida</dt><dd id="qm-departure" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Regreso</dt><dd id="qm-return" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Días cubiertos</dt><dd id="qm-days" class="font-medium text-brand-navy"></dd></div>
                        </dl>
                    </section>

                    <section>
                        <h3 class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Precio</h3>
                        <dl class="mt-2 grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                            <div><dt class="text-xs text-brand-gray">Tarifa diaria</dt><dd id="qm-daily-rate" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Recargo</dt><dd id="qm-surcharge-pct" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Subtotal</dt><dd id="qm-subtotal" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Monto de recargo</dt><dd id="qm-surcharge-amount" class="font-medium text-brand-navy"></dd></div>
                            <div><dt class="text-xs text-brand-gray">Total</dt><dd id="qm-total" class="font-heading text-base font-medium text-brand-navy"></dd></div>
                        </dl>
                    </section>

                    <section>
                        <h3 class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Pago</h3>
                        <div id="qm-payment-block" class="mt-2 hidden">
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm sm:grid-cols-3">
                                <div><dt class="text-xs text-brand-gray">Referencia</dt><dd id="qm-payment-reference" class="font-mono font-medium text-brand-navy"></dd></div>
                                <div><dt class="text-xs text-brand-gray">Estado</dt><dd id="qm-payment-status" class="font-medium text-brand-navy"></dd></div>
                                <div><dt class="text-xs text-brand-gray">Tarjeta</dt><dd id="qm-payment-card" class="font-medium text-brand-navy"></dd></div>
                                <div><dt class="text-xs text-brand-gray">Monto</dt><dd id="qm-payment-amount" class="font-medium text-brand-navy"></dd></div>
                                <div><dt class="text-xs text-brand-gray">Autorización</dt><dd id="qm-payment-auth" class="font-mono font-medium text-brand-navy"></dd></div>
                                <div><dt class="text-xs text-brand-gray">Fecha de pago</dt><dd id="qm-payment-paid-at" class="font-medium text-brand-navy"></dd></div>
                            </dl>
                        </div>
                        <p id="qm-payment-empty" class="mt-2 text-sm text-brand-gray">Aún no se ha registrado un pago para esta cotización.</p>
                    </section>

                    <section>
                        <h3 class="text-xs font-semibold tracking-wide text-brand-gray uppercase">Cuenta de cliente</h3>
                        <div id="qm-account-block" class="mt-2 hidden">
                            <p class="text-sm text-brand-navy">Usuario: <span id="qm-account-username" class="font-mono font-medium"></span></p>
                            <p class="mt-1 text-xs text-brand-gray">La contraseña inicial es el mismo número de identificación del asegurado.</p>
                        </div>
                        <p id="qm-account-empty" class="mt-2 text-sm text-brand-gray">Se creará automáticamente cuando se apruebe el pago.</p>
                    </section>

                    <section class="border-t border-slate-100 pt-3 text-xs text-brand-gray">
                        <p>Creada el <span id="qm-created-at" class="font-medium text-brand-navy"></span></p>
                        <p id="qm-contracted-at-row" class="mt-1 hidden">Contratada el <span id="qm-contracted-at" class="font-medium text-brand-navy"></span></p>
                    </section>
                </div>
            </div>
        </div>

        <script>
            (function () {
                const modal = document.getElementById('quote-modal');
                const money = (value) => 'USD $' + Number(value).toLocaleString('es-EC', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                const dateOnly = (iso) => iso ? new Date(iso + 'T00:00:00').toLocaleDateString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric' }) : '—';
                const dateTime = (iso) => iso ? new Date(iso).toLocaleString('es-EC', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—';
                const text = (id, value) => { document.getElementById(id).textContent = value ?? '—'; };

                function openModal(data) {
                    if (!data || !data.reference) {
                        return;
                    }

                    text('qm-reference', data.reference);
                    text('qm-name', `${data.insured.first_name} ${data.insured.last_name}`);

                    const statusEl = document.getElementById('qm-status');
                    statusEl.textContent = data.status_label;
                    statusEl.className = 'shrink-0 rounded-full px-2.5 py-1 text-[11px] font-semibold ' +
                        (data.status === 'contracted' ? 'bg-brand-teal/25 text-brand-navy' : 'bg-brand-amber/25 text-brand-navy');

                    text('qm-document', `${data.insured.document_type_label}: ${data.insured.document_id}`);
                    text('qm-email', data.insured.email);
                    text('qm-phone', data.insured.phone);

                    text('qm-trip-type', data.trip_type === 'multiple' ? 'Viaje a varios destinos' : 'Viaje a un solo destino');
                    const destinationsList = document.getElementById('qm-destinations');
                    destinationsList.innerHTML = '';
                    (data.destinations || []).forEach((destination) => {
                        const li = document.createElement('li');
                        li.textContent = `${destination.name} (${destination.region})`;
                        destinationsList.appendChild(li);
                    });

                    text('qm-departure', dateOnly(data.trip.departure_date));
                    text('qm-return', dateOnly(data.trip.return_date));
                    text('qm-days', data.trip.days);

                    text('qm-daily-rate', money(data.pricing.daily_rate));
                    text('qm-surcharge-pct', `${data.pricing.surcharge_percentage}%`);
                    text('qm-subtotal', money(data.pricing.subtotal));
                    text('qm-surcharge-amount', money(data.pricing.surcharge_amount));
                    text('qm-total', money(data.pricing.total));

                    const paymentBlock = document.getElementById('qm-payment-block');
                    const paymentEmpty = document.getElementById('qm-payment-empty');
                    if (data.payment) {
                        paymentBlock.classList.remove('hidden');
                        paymentEmpty.classList.add('hidden');
                        text('qm-payment-reference', data.payment.reference);
                        text('qm-payment-status', data.payment.status_label);
                        text('qm-payment-card', `${data.payment.card_brand_label} •••• ${data.payment.card_last_four}`);
                        text('qm-payment-amount', money(data.payment.amount));
                        text('qm-payment-auth', data.payment.authorization_code);
                        text('qm-payment-paid-at', dateTime(data.payment.paid_at));
                    } else {
                        paymentBlock.classList.add('hidden');
                        paymentEmpty.classList.remove('hidden');
                    }

                    const accountBlock = document.getElementById('qm-account-block');
                    const accountEmpty = document.getElementById('qm-account-empty');
                    if (data.account) {
                        accountBlock.classList.remove('hidden');
                        accountEmpty.classList.add('hidden');
                        text('qm-account-username', data.account.username);
                    } else {
                        accountBlock.classList.add('hidden');
                        accountEmpty.classList.remove('hidden');
                    }

                    text('qm-created-at', dateTime(data.created_at));
                    const contractedRow = document.getElementById('qm-contracted-at-row');
                    if (data.contracted_at) {
                        contractedRow.classList.remove('hidden');
                        text('qm-contracted-at', dateTime(data.contracted_at));
                    } else {
                        contractedRow.classList.add('hidden');
                    }

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    document.body.classList.remove('overflow-hidden');
                }

                document.querySelectorAll('[data-quote-detail]').forEach((el) => {
                    el.addEventListener('click', () => {
                        try {
                            openModal(JSON.parse(el.dataset.quoteDetail));
                        } catch (error) {
                            console.error('No se pudo leer el detalle de la cotización', error);
                        }
                    });
                    el.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' || event.key === ' ') {
                            event.preventDefault();
                            el.click();
                        }
                    });
                });

                document.querySelectorAll('[data-modal-close]').forEach((el) => el.addEventListener('click', closeModal));
                modal.addEventListener('click', (event) => {
                    if (event.target === modal) {
                        closeModal();
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                        closeModal();
                    }
                });
            })();
        </script>
    </body>
</html>
