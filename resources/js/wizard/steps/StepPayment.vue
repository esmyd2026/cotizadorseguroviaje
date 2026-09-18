<script setup>
import { computed, reactive, ref } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import { useQuoteApi } from '../composables/useQuoteApi';
import { useWizardState } from '../composables/useWizardState';

const emit = defineEmits(['next']);
const { state } = useWizardState();
const { submitting, payQuote } = useQuoteApi();

const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;
const touched = reactive({});
const attempted = ref(false);

function newIdempotencyKey() {
    if (globalThis.crypto?.randomUUID) {
        return globalThis.crypto.randomUUID();
    }

    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const random = Math.floor(Math.random() * 16);
        const value = character === 'x' ? random : (random & 0x3) | 0x8;
        return value.toString(16);
    });
}

const form = reactive({
    idempotencyKey: newIdempotencyKey(),
    cardHolder: `${state.quote?.insured.first_name ?? ''} ${state.quote?.insured.last_name ?? ''}`.trim(),
    cardNumber: '',
    expirationMonth: '',
    expirationYear: '',
    securityCode: '',
    billingEmail: state.quote?.insured.email ?? '',
    terms: false,
});

const years = Array.from({ length: 16 }, (_, index) => currentYear + index);
const digits = computed(() => form.cardNumber.replace(/\D/g, ''));
const cardBrand = computed(() => {
    if (digits.value.startsWith('4')) return 'Visa';

    const firstTwo = Number(digits.value.slice(0, 2));
    const firstFour = Number(digits.value.slice(0, 4));
    if ((firstTwo >= 51 && firstTwo <= 55) || (firstFour >= 2221 && firstFour <= 2720)) return 'Mastercard';

    return '';
});

function passesLuhn(number) {
    let sum = 0;
    const parity = number.length % 2;

    for (let index = 0; index < number.length; index += 1) {
        let digit = Number(number[index]);
        if (index % 2 === parity) {
            digit *= 2;
            if (digit > 9) digit -= 9;
        }
        sum += digit;
    }

    return number.length === 16 && sum % 10 === 0;
}

const localErrors = computed(() => {
    const errors = {};
    const holder = form.cardHolder.trim();
    const month = Number(form.expirationMonth);
    const year = Number(form.expirationYear);

    if (holder.length < 5 || !/^[\p{L}\s'.-]+$/u.test(holder)) errors.card_holder = 'Ingresa el nombre completo de la tarjeta.';
    if (!passesLuhn(digits.value) || !cardBrand.value) errors.card_number = 'Ingresa una tarjeta Visa o Mastercard válida.';
    if (!month) errors.expiration_month = 'Selecciona el mes.';
    if (!year) errors.expiration_year = 'Selecciona el año.';
    if (month && year && (year < currentYear || (year === currentYear && month < currentMonth))) errors.expiration_month = 'La tarjeta está vencida.';
    if (!/^\d{3}$/.test(form.securityCode)) errors.security_code = 'Ingresa los 3 dígitos.';
    if (!form.terms) errors.terms = 'Acepta los términos para continuar.';

    return errors;
});

const isValid = computed(() => Object.keys(localErrors.value).length === 0);
const previewNumber = computed(() => {
    const padded = `${digits.value}${'•'.repeat(Math.max(0, 16 - digits.value.length))}`.slice(0, 16);
    return padded.match(/.{1,4}/g)?.join(' ') ?? '•••• •••• •••• ••••';
});

function fieldError(field) {
    const serverError = state.fieldErrors[field]?.[0];
    if (serverError) return serverError;
    if (attempted.value || touched[field]) return localErrors.value[field];
    return null;
}

function formatCardNumber(event) {
    const value = event.target.value.replace(/\D/g, '').slice(0, 16);
    form.cardNumber = value.match(/.{1,4}/g)?.join(' ') ?? '';
    state.fieldErrors.card_number = null;
}

function formatSecurityCode(event) {
    form.securityCode = event.target.value.replace(/\D/g, '').slice(0, 3);
    state.fieldErrors.security_code = null;
}

function formatCardHolder(event) {
    form.cardHolder = event.target.value.toLocaleUpperCase('es-EC');
    state.fieldErrors.card_holder = null;
}

function useDemoCard(number) {
    form.cardNumber = number.match(/.{1,4}/g)?.join(' ') ?? number;
    form.expirationMonth = String(currentMonth === 12 ? 11 : currentMonth + 1).padStart(2, '0');
    form.expirationYear = String(currentYear + 1);
    form.securityCode = '123';
    touched.card_number = true;
    state.fieldErrors = {};
    state.serverError = null;
}

async function submitPayment() {
    attempted.value = true;
    state.serverError = null;
    state.fieldErrors = {};

    if (!isValid.value || !state.quote) return;

    const result = await payQuote(state.quote.reference, {
        idempotency_key: form.idempotencyKey,
        card_holder: form.cardHolder,
        card_number: digits.value,
        expiration_month: Number(form.expirationMonth),
        expiration_year: Number(form.expirationYear),
        security_code: form.securityCode,
        billing_email: form.billingEmail,
        terms: form.terms,
    });

    if (!result.ok) {
        state.fieldErrors = result.fieldErrors ?? {};
        state.serverError = result.message;

        if (result.status === 402) {
            form.idempotencyKey = newIdempotencyKey();
        }

        return;
    }

    state.quote = result.data;
    emit('next');
}
</script>

<template>
    <div v-if="state.quote">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <span class="section-kicker"><AppIcon name="credit-card" :size="15" /> Pago protegido</span>
                <h1 class="step-title">Completa tu contratación</h1>
                <p class="step-copy">Simula el pago de <strong class="text-brand-navy">USD ${{ Number(state.quote.pricing.total).toFixed(2) }}</strong>. No se realizará ningún cargo real.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-[10px] font-bold tracking-wide text-amber-800 uppercase">
                <AppIcon name="sparkle" :size="13" /> Modo demostración
            </span>
        </div>

        <div class="mt-6 grid gap-6 lg:grid-cols-[0.92fr_1.08fr]">
            <div>
                <div class="relative overflow-hidden rounded-[1.4rem] bg-brand-navy p-5 text-white shadow-[0_20px_42px_rgba(22,36,61,0.25)]">
                    <span class="absolute -right-10 -top-14 h-36 w-36 rounded-full bg-brand-amber/20" />
                    <span class="absolute -bottom-12 -left-10 h-32 w-32 rounded-full border-[22px] border-white/5" />
                    <div class="relative flex min-h-44 flex-col justify-between">
                        <div class="flex items-center justify-between">
                            <AppIcon name="credit-card" :size="28" :stroke-width="1.4" />
                            <span class="font-heading text-sm font-semibold">{{ cardBrand || 'Tarjeta' }}</span>
                        </div>
                        <p class="font-mono text-lg tracking-[0.09em] sm:text-xl">{{ previewNumber }}</p>
                        <div class="flex items-end justify-between gap-4 text-[9px] uppercase tracking-wider text-white/55">
                            <div class="min-w-0">
                                <p>Titular</p>
                                <p class="mt-1 truncate text-xs font-semibold tracking-normal text-white">{{ form.cardHolder || 'Nombre del titular' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <p>Vence</p>
                                <p class="mt-1 text-xs font-semibold tracking-normal text-white">{{ form.expirationMonth || 'MM' }}/{{ form.expirationYear ? String(form.expirationYear).slice(-2) : 'AA' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="soft-card mt-4 p-3.5">
                    <p class="text-[10px] font-bold tracking-wide text-brand-gray uppercase">Tarjetas para probar</p>
                    <button type="button" class="mt-2 flex w-full items-center justify-between rounded-xl bg-white px-3 py-2 text-left text-xs shadow-sm transition hover:ring-2 hover:ring-emerald-200" @click="useDemoCard('4242424242424242')">
                        <span><strong class="text-emerald-700">Aprobada</strong><span class="ml-2 font-mono text-brand-gray">•••• 4242</span></span>
                        <span class="font-semibold text-brand-navy-mid">Usar</span>
                    </button>
                    <button type="button" class="mt-2 flex w-full items-center justify-between rounded-xl bg-white px-3 py-2 text-left text-xs shadow-sm transition hover:ring-2 hover:ring-rose-200" @click="useDemoCard('4000000000000002')">
                        <span><strong class="text-brand-coral-text">Rechazada</strong><span class="ml-2 font-mono text-brand-gray">•••• 0002</span></span>
                        <span class="font-semibold text-brand-navy-mid">Usar</span>
                    </button>
                </div>
            </div>

            <form class="space-y-4" novalidate @submit.prevent="submitPayment">
                <label class="block">
                    <span class="field-label">Nombre en la tarjeta</span>
                    <div class="field-control flex items-center gap-2 px-3" :class="fieldError('card_holder') && 'field-control--error'">
                        <span class="field-icon"><AppIcon name="user" :size="17" /></span>
                        <input :value="form.cardHolder" type="text" maxlength="100" autocomplete="cc-name" class="min-w-0 flex-1 bg-transparent py-3 text-sm uppercase outline-none" @input="formatCardHolder" @blur="touched.card_holder = true" />
                    </div>
                    <p v-if="fieldError('card_holder')" class="form-error"><AppIcon name="info-circle" :size="14" /> {{ fieldError('card_holder') }}</p>
                </label>

                <label class="block">
                    <span class="field-label">Número de tarjeta</span>
                    <div class="field-control flex items-center gap-2 px-3" :class="fieldError('card_number') && 'field-control--error'">
                        <span class="field-icon"><AppIcon name="credit-card" :size="17" /></span>
                        <input :value="form.cardNumber" type="text" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="0000 0000 0000 0000" class="min-w-0 flex-1 bg-transparent py-3 font-mono text-sm outline-none" @input="formatCardNumber" @blur="touched.card_number = true" />
                        <span v-if="cardBrand" class="text-[10px] font-bold text-brand-navy-mid">{{ cardBrand }}</span>
                    </div>
                    <p v-if="fieldError('card_number')" class="form-error"><AppIcon name="info-circle" :size="14" /> {{ fieldError('card_number') }}</p>
                </label>

                <div class="grid grid-cols-[1fr_1fr_0.9fr] gap-2.5">
                    <label class="block">
                        <span class="field-label">Mes</span>
                        <select v-model="form.expirationMonth" autocomplete="cc-exp-month" class="field-control appearance-none px-3 text-sm" :class="fieldError('expiration_month') && 'field-control--error'" @blur="touched.expiration_month = true">
                            <option value="" disabled>MM</option>
                            <option v-for="month in 12" :key="month" :value="String(month).padStart(2, '0')">{{ String(month).padStart(2, '0') }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="field-label">Año</span>
                        <select v-model="form.expirationYear" autocomplete="cc-exp-year" class="field-control appearance-none px-3 text-sm" :class="fieldError('expiration_year') && 'field-control--error'" @blur="touched.expiration_year = true">
                            <option value="" disabled>AAAA</option>
                            <option v-for="year in years" :key="year" :value="String(year)">{{ year }}</option>
                        </select>
                    </label>
                    <label class="block">
                        <span class="field-label">CVV</span>
                        <input :value="form.securityCode" type="password" inputmode="numeric" maxlength="3" autocomplete="cc-csc" placeholder="•••" class="field-control px-3 text-sm" :class="fieldError('security_code') && 'field-control--error'" @input="formatSecurityCode" @blur="touched.security_code = true" />
                    </label>
                </div>
                <p v-if="fieldError('expiration_month') || fieldError('expiration_year') || fieldError('security_code')" class="form-error"><AppIcon name="info-circle" :size="14" /> {{ fieldError('expiration_month') || fieldError('expiration_year') || fieldError('security_code') }}</p>

                <label class="block">
                    <span class="field-label">Comprobante por correo</span>
                    <div class="field-control flex items-center gap-2 bg-slate-50 px-3">
                        <span class="field-icon"><AppIcon name="mail" :size="17" /></span>
                        <input v-model="form.billingEmail" type="email" readonly class="min-w-0 flex-1 bg-transparent py-3 text-sm text-brand-gray uppercase outline-none" />
                        <AppIcon name="lock" :size="14" class="text-slate-400" />
                    </div>
                    <p v-if="fieldError('billing_email')" class="form-error"><AppIcon name="info-circle" :size="14" /> {{ fieldError('billing_email') }}</p>
                </label>

                <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-3.5 text-xs leading-5 transition" :class="form.terms ? 'border-brand-gold bg-brand-amber/10 text-brand-navy' : fieldError('terms') ? 'border-brand-coral bg-rose-50 text-brand-coral-text' : 'border-slate-200 text-brand-gray'">
                    <input v-model="form.terms" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 rounded accent-brand-amber" @blur="touched.terms = true" />
                    <span>Acepto la simulación de pago y la creación automática de mi cuenta de cliente al aprobarse.</span>
                </label>

                <div v-if="state.serverError" class="rounded-2xl border border-rose-200 bg-rose-50 p-3.5 text-sm text-brand-coral-text" role="alert">
                    <p class="flex items-start gap-2"><AppIcon name="info-circle" :size="18" class="mt-0.5 shrink-0" /> <span>{{ state.serverError }}</span></p>
                    <p class="mt-2 pl-6 text-xs">No se realizó ningún cargo. Puedes revisar los datos e intentarlo otra vez.</p>
                </div>

                <button type="submit" class="primary-action w-full" :disabled="!isValid || submitting">
                    <span>{{ submitting ? 'Procesando de forma segura...' : `Pagar USD $${Number(state.quote.pricing.total).toFixed(2)}` }}</span>
                    <AppIcon v-if="!submitting" name="lock" :size="17" />
                </button>
                <p class="flex items-center justify-center gap-1.5 text-[11px] text-brand-gray"><AppIcon name="key" :size="14" /> No almacenamos el número completo ni el CVV.</p>
            </form>
        </div>
    </div>
</template>
