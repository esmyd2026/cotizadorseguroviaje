<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import { useCountriesApi } from '../composables/useCountriesApi';
import { useWizardState } from '../composables/useWizardState';

const emit = defineEmits(['next']);
const { state } = useWizardState();
const { loading, error, loadAll } = useCountriesApi();

const query = ref('');
const countries = ref([]);
const isOpen = ref(false);
const activeIndex = ref(-1);
const container = ref(null);
const popularCodes = ['ES', 'US', 'CO', 'PE', 'AR', 'MX', 'BR', 'CL'];

if (state.destinations.length === 0 && state.destination) {
    state.destinations = [state.destination];
}

const displayResults = computed(() => {
    const needle = normalize(query.value);
    const selectedCodes = new Set(state.destinations.map((destination) => destination.code));
    const source = needle
        ? countries.value.filter((country) => normalize(country.name).includes(needle) || country.code.toLowerCase().includes(needle))
        : popularCodes.map((code) => countries.value.find((country) => country.code === code)).filter(Boolean);

    return source.filter((country) => !selectedCodes.has(country.code)).slice(0, 15);
});
const isListVisible = computed(() => isOpen.value && displayResults.value.length > 0);
const canContinue = computed(() =>
    state.tripType === 'direct' ? state.destinations.length === 1 : state.destinations.length >= 2,
);
const activeOptionId = computed(() =>
    activeIndex.value >= 0 && displayResults.value[activeIndex.value]
        ? `country-option-${displayResults.value[activeIndex.value].code}`
        : undefined,
);

function normalize(value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

async function loadCountries() {
    if (countries.value.length === 0) {
        countries.value = await loadAll();
    }
}

function selectTripType(type) {
    state.tripType = type;

    if (type === 'direct' && state.destinations.length > 1) {
        state.destinations = [state.destinations[0]];
    }

    syncPrimaryDestination();
    query.value = '';
}

function select(country) {
    if (state.tripType === 'direct') {
        state.destinations = [country];
        isOpen.value = false;
    } else if (state.destinations.length < 5) {
        state.destinations.push(country);
    }

    syncPrimaryDestination();
    query.value = '';
    activeIndex.value = -1;
}

function removeDestination(code) {
    state.destinations = state.destinations.filter((destination) => destination.code !== code);
    syncPrimaryDestination();
}

function syncPrimaryDestination() {
    state.destination = state.destinations[0] ?? null;
}

function continueToNext() {
    if (canContinue.value) {
        emit('next');
    }
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        isOpen.value = false;
    }
}

function onArrowDown() {
    if (isListVisible.value) {
        activeIndex.value = (activeIndex.value + 1) % displayResults.value.length;
    }
}

function onArrowUp() {
    if (isListVisible.value) {
        activeIndex.value = activeIndex.value <= 0 ? displayResults.value.length - 1 : activeIndex.value - 1;
    }
}

function onEnter() {
    if (isListVisible.value && activeIndex.value >= 0) {
        select(displayResults.value[activeIndex.value]);
        return;
    }

    continueToNext();
}

onMounted(() => {
    loadCountries();
    document.addEventListener('click', handleClickOutside);
});
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div>
        <span class="section-kicker"><AppIcon name="plane" :size="15" /> Diseña tu viaje</span>
        <h1 class="step-title">¿Cómo será tu recorrido?</h1>
        <p class="step-copy max-w-lg">Cuéntanos si visitarás un solo país o si tu viaje incluye varias paradas.</p>

        <div class="mt-6 grid grid-cols-2 gap-2 rounded-2xl bg-brand-gray-light p-1.5" role="radiogroup" aria-label="Tipo de viaje">
            <button
                type="button"
                role="radio"
                :aria-checked="state.tripType === 'direct'"
                class="flex min-h-16 items-center gap-2.5 rounded-xl px-3 text-left transition-all"
                :class="state.tripType === 'direct' ? 'bg-white text-brand-navy shadow-[0_4px_14px_rgba(22,36,61,0.09)]' : 'text-brand-gray'"
                @click="selectTripType('direct')"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="state.tripType === 'direct' ? 'bg-brand-amber/20 text-brand-navy-mid' : 'bg-white/60'"><AppIcon name="map-pin" :size="18" /></span>
                <span><span class="block text-sm font-semibold">Un destino</span><span class="block text-[10px] text-brand-gray">Viaje directo</span></span>
            </button>
            <button
                type="button"
                role="radio"
                :aria-checked="state.tripType === 'multiple'"
                class="flex min-h-16 items-center gap-2.5 rounded-xl px-3 text-left transition-all"
                :class="state.tripType === 'multiple' ? 'bg-white text-brand-navy shadow-[0_4px_14px_rgba(22,36,61,0.09)]' : 'text-brand-gray'"
                @click="selectTripType('multiple')"
            >
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="state.tripType === 'multiple' ? 'bg-brand-amber/20 text-brand-navy-mid' : 'bg-white/60'"><AppIcon name="route" :size="19" /></span>
                <span><span class="block text-sm font-semibold">Varias paradas</span><span class="block text-[10px] text-brand-gray">2 a 5 países</span></span>
            </button>
        </div>

        <div ref="container" class="relative mt-5">
            <label for="destination-search" class="field-label">
                {{ state.tripType === 'direct' ? 'País de destino' : 'Agrega los países en orden de visita' }}
            </label>
            <div class="field-control flex items-center gap-3 px-3.5 py-2.5 sm:px-4">
                <span class="field-icon"><AppIcon name="search" :size="19" /></span>
                <input
                    id="destination-search"
                    v-model="query"
                    type="text"
                    :placeholder="state.tripType === 'direct' ? 'Busca tu destino' : 'Busca la siguiente parada'"
                    role="combobox"
                    aria-autocomplete="list"
                    aria-controls="destination-listbox"
                    :aria-expanded="isListVisible"
                    :aria-activedescendant="activeOptionId"
                    class="min-w-0 w-full border-0 bg-transparent p-0 text-base text-brand-navy placeholder:text-slate-400 focus:outline-none"
                    @focus="isOpen = true; loadCountries()"
                    @keydown.down.prevent="onArrowDown"
                    @keydown.up.prevent="onArrowUp"
                    @keydown.enter.prevent="onEnter"
                    @keydown.esc="isOpen = false"
                />
                <span v-if="loading" class="h-4 w-4 shrink-0 animate-spin rounded-full border-2 border-slate-300 border-t-brand-navy-mid" aria-hidden="true" />
                <span v-else-if="state.tripType === 'multiple'" class="shrink-0 text-xs font-semibold text-brand-gray">{{ state.destinations.length }}/5</span>
            </div>

            <ul v-if="isListVisible" id="destination-listbox" role="listbox" class="absolute z-20 mt-2 max-h-72 w-full overflow-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_20px_48px_rgba(22,36,61,0.16)]">
                <li v-if="!query.trim()" class="px-3 pb-2 pt-1 text-[10px] font-semibold tracking-[0.12em] text-brand-gray uppercase">Destinos populares</li>
                <li
                    v-for="(country, index) in displayResults"
                    :id="`country-option-${country.code}`"
                    :key="country.code"
                    role="option"
                    :aria-selected="index === activeIndex"
                    class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-3 transition-colors"
                    :class="index === activeIndex ? 'bg-brand-gray-light' : 'hover:bg-slate-50'"
                    @click="select(country)"
                    @mouseenter="activeIndex = index"
                >
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white text-lg shadow-sm" aria-hidden="true">{{ country.flag }}</span>
                    <span class="flex-1 font-medium text-brand-navy">{{ country.name }}</span>
                    <AppIcon name="plus" :size="16" class="text-brand-navy-mid" />
                </li>
            </ul>

            <p v-else-if="isOpen && !loading && query.trim() && !error" class="absolute z-10 mt-2 w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-brand-gray shadow-lg" role="status">
                No encontramos países con “{{ query }}”.
            </p>

            <div v-if="error" class="mt-3 flex items-center justify-between gap-3 rounded-xl bg-brand-coral/10 px-3.5 py-3 text-xs text-brand-coral-text" role="alert">
                <span class="flex items-start gap-2"><AppIcon name="info-circle" :size="15" class="mt-0.5 shrink-0" /> {{ error }}</span>
                <button type="button" class="shrink-0 font-semibold underline underline-offset-2" @click="loadCountries">Reintentar</button>
            </div>
        </div>

        <TransitionGroup v-if="state.destinations.length" name="list" tag="ol" class="mt-4 space-y-2" aria-label="Itinerario seleccionado">
            <li v-for="(destination, index) in state.destinations" :key="destination.code" class="soft-card flex items-center gap-3 p-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-navy text-xs font-semibold text-white">{{ index + 1 }}</span>
                <span class="text-lg" aria-hidden="true">{{ destination.flag }}</span>
                <span class="min-w-0 flex-1 truncate text-sm font-semibold text-brand-navy">{{ destination.name }}</span>
                <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl text-brand-gray hover:bg-white hover:text-brand-coral-text" :aria-label="`Quitar ${destination.name}`" @click="removeDestination(destination.code)">
                    <AppIcon name="x" :size="16" />
                </button>
            </li>
        </TransitionGroup>

        <div v-if="state.tripType === 'multiple'" class="mt-4 flex items-start gap-2.5 rounded-xl bg-brand-teal/10 px-3.5 py-3 text-xs leading-5 text-brand-gray">
            <AppIcon name="info-circle" :size="16" class="mt-0.5 shrink-0 text-brand-navy-mid" />
            <p>La cotización aplicará el recargo regional más alto de los países incluidos en tu recorrido.</p>
        </div>

        <button type="button" class="primary-action mt-6 w-full sm:w-auto" :disabled="!canContinue" @click="continueToNext">
            Continuar <AppIcon name="arrow-right" :size="18" />
        </button>
        <p v-if="state.tripType === 'multiple' && state.destinations.length === 1" class="mt-2 text-xs text-brand-gray">Agrega una parada más para continuar.</p>
    </div>
</template>
