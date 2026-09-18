<script setup>
import { computed } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import DatePicker from '../components/DatePicker.vue';
import { useWizardState } from '../composables/useWizardState';
import { addDaysIso, todayIso, tripDays } from '../utils/dates';

const emit = defineEmits(['next', 'back']);

const { state } = useWizardState();

const today = todayIso();

const durationPresets = [
    { label: '1 semana', days: 7 },
    { label: '15 días', days: 15 },
    { label: '1 mes', days: 30 },
];

if (!state.departureDate) {
    state.departureDate = today;
}

if (!state.returnDate) {
    state.returnDate = addDaysIso(state.departureDate, durationPresets[0].days - 1);
}

const days = computed(() => tripDays(state.departureDate, state.returnDate));

const isValidRange = computed(() => {
    if (!state.departureDate || !state.returnDate) {
        return false;
    }

    return state.returnDate >= state.departureDate;
});

function setDepartureDate(value) {
    state.departureDate = value;

    if (state.returnDate && state.returnDate < state.departureDate) {
        state.returnDate = addDaysIso(state.departureDate, durationPresets[0].days - 1);
    }
}

function setReturnDate(value) {
    state.returnDate = value;
}

function applyDurationPreset(presetDays) {
    state.returnDate = addDaysIso(state.departureDate, presetDays - 1);
}

function continueToNext() {
    if (!isValidRange.value) {
        return;
    }

    emit('next');
}
</script>

<template>
    <div>
        <span class="section-kicker"><AppIcon name="calendar" :size="15" /> Fechas del viaje</span>
        <h1 class="step-title">¿Cuándo viajas?</h1>
        <p class="step-copy">Indica la salida y el regreso. Contamos ambos días dentro de tu cobertura.</p>

        <div class="mt-7 grid grid-cols-1 gap-4 sm:mt-9 sm:grid-cols-[1fr_auto_1fr] sm:items-end">
            <div class="block">
                <span class="field-label">Salida</span>
                <DatePicker :model-value="state.departureDate" :min="today" :initial-view="state.departureDate || today" placeholder="Fecha de salida" @update:model-value="setDepartureDate" />
            </div>

            <span class="mb-4 hidden text-slate-300 sm:block"><AppIcon name="arrow-right" :size="18" /></span>

            <div class="block">
                <span class="field-label">Regreso</span>
                <DatePicker :model-value="state.returnDate" :min="state.departureDate || today" :initial-view="state.returnDate || state.departureDate || today" placeholder="Fecha de regreso" align="right" @update:model-value="setReturnDate" />
            </div>
        </div>

        <p class="mt-5 text-xs font-semibold tracking-wide text-brand-gray uppercase">Duración rápida</p>
        <div class="mt-2 flex flex-wrap gap-2">
            <button
                v-for="preset in durationPresets"
                :key="preset.label"
                type="button"
                class="min-h-10 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-brand-gray shadow-sm transition hover:border-brand-gold hover:bg-brand-amber/10 hover:text-brand-navy"
                @click="applyDurationPreset(preset.days)"
            >
                {{ preset.label }}
            </button>
        </div>

        <p v-if="state.departureDate && state.returnDate && !isValidRange" class="mt-3 text-sm text-brand-coral-text" role="alert">
            La fecha de regreso debe ser igual o posterior a la fecha de salida.
        </p>

        <Transition name="step">
            <div v-if="days" class="mt-5 flex items-center gap-3 rounded-2xl bg-brand-navy px-4 py-3.5 text-white shadow-[0_12px_28px_rgba(22,36,61,0.16)]" role="status">
                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/10"><AppIcon name="clock" :size="19" /></span>
                <div>
                    <p class="text-[11px] text-white/65">Duración de la cobertura</p>
                    <p class="text-sm font-semibold">{{ days }} {{ days === 1 ? 'día' : 'días' }} de viaje</p>
                </div>
            </div>
        </Transition>

        <div class="mt-7 flex items-center gap-2 sm:gap-3">
            <button
                type="button"
                class="secondary-action px-3 sm:px-4"
                @click="emit('back')"
            >
                <AppIcon name="arrow-left" :size="18" /> Volver
            </button>
            <button
                type="button"
                class="primary-action flex-1 sm:flex-none"
                :disabled="!isValidRange"
                @click="continueToNext"
            >
                Continuar <AppIcon name="arrow-right" :size="18" />
            </button>
        </div>
    </div>
</template>
