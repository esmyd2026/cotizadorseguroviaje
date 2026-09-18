<script setup>
import { ref } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import { useWizardState } from '../composables/useWizardState';
import { formatShortDate } from '../utils/dates';

const emit = defineEmits(['next']);

const { state, goTo } = useWizardState();
const confirmed = ref(false);

function money(value) {
    return `USD $${Number(value).toFixed(2)}`;
}

function confirm() {
    if (!confirmed.value || !state.quote) {
        return;
    }

    state.serverError = null;
    emit('next');
}
</script>

<template>
    <div v-if="state.quote">
        <span class="section-kicker"><AppIcon name="shield" :size="15" /> Antes de pagar</span>
        <h1 class="step-title">Confirma tus datos</h1>
        <p class="step-copy">Revísalos antes de emitir tu seguro. Puedes editar cualquier sección.</p>

        <section class="mt-7 sm:mt-8">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-xs font-semibold tracking-wide text-brand-gray uppercase"><AppIcon name="user" :size="15" /> Viajero</h2>
                <button type="button" class="flex min-h-10 items-center gap-1.5 rounded-lg px-2 text-xs font-semibold text-brand-navy-mid hover:bg-brand-gray-light" @click="goTo('traveler')">
                    <AppIcon name="edit" :size="14" /> Editar
                </button>
            </div>
            <div class="soft-card mt-2 p-4">
                <p class="font-medium text-brand-navy">{{ state.quote.insured.first_name }} {{ state.quote.insured.last_name }}</p>
                <p class="text-sm text-brand-gray">{{ state.quote.insured.document_type_label }} · {{ state.quote.insured.document_id }}</p>
                <p class="mt-1 text-sm text-brand-gray">{{ state.quote.insured.email }}</p>
                <p v-if="state.quote.insured.phone" class="text-sm text-brand-gray">{{ state.quote.insured.phone }}</p>
            </div>
        </section>

        <section class="mt-6">
            <div class="flex items-center justify-between">
                <h2 class="flex items-center gap-2 text-xs font-semibold tracking-wide text-brand-gray uppercase"><AppIcon name="plane" :size="15" /> Viaje</h2>
                <button type="button" class="flex min-h-10 items-center gap-1.5 rounded-lg px-2 text-xs font-semibold text-brand-navy-mid hover:bg-brand-gray-light" @click="goTo('destination')">
                    <AppIcon name="edit" :size="14" /> Editar
                </button>
            </div>
            <div class="soft-card mt-2 flex items-center gap-3 p-4 text-sm text-brand-navy">
                <span class="flex shrink-0 -space-x-2" aria-hidden="true">
                    <span v-for="destination in state.quote.destinations.slice(0, 3)" :key="destination.code" class="flex h-10 w-10 items-center justify-center rounded-xl border-2 border-white bg-white text-lg shadow-sm">{{ destination.flag }}</span>
                </span>
                <div>
                    <p class="font-semibold">{{ state.quote.destination.country_name }}</p>
                    <p class="text-xs text-brand-gray">{{ formatShortDate(state.quote.trip.departure_date) }} → {{ formatShortDate(state.quote.trip.return_date) }} · {{ state.quote.trip.days }} días</p>
                </div>
            </div>
        </section>

        <section class="mt-6 flex items-center justify-between rounded-2xl bg-brand-navy px-4 py-4 text-white shadow-[0_12px_28px_rgba(22,36,61,0.16)]">
            <span class="text-xs font-semibold tracking-wide text-white/65 uppercase">Total a contratar</span>
            <span class="font-heading text-xl font-medium">{{ money(state.quote.pricing.total) }}</span>
        </section>

        <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-2xl border p-4 text-sm transition" :class="confirmed ? 'border-brand-gold bg-brand-amber/10 text-brand-navy' : 'border-slate-200 text-brand-gray hover:border-slate-300'">
            <input v-model="confirmed" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0 rounded border-slate-300 accent-brand-amber focus:ring-brand-gold" />
            <span class="leading-5">Confirmo que los datos ingresados son correctos.</span>
        </label>

        <p v-if="state.serverError" class="mt-3 text-sm text-brand-coral-text" role="alert">{{ state.serverError }}</p>

        <button type="button" class="primary-action mt-6 w-full" :disabled="!confirmed" @click="confirm">
            <span>Continuar al pago</span>
            <AppIcon name="arrow-right" :size="18" />
        </button>
    </div>
</template>
