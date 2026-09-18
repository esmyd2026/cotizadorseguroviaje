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
