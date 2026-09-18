<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useCountriesApi } from '../composables/useCountriesApi';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    countryCode: { type: String, required: true },
    number: { type: String, required: true },
    hasError: { type: Boolean, default: false },
});

const emit = defineEmits(['update:countryCode', 'update:number', 'blur', 'validation']);
const { loadAll } = useCountriesApi();

const countries = ref([]);
const isOpen = ref(false);
const query = ref('');
const container = ref(null);
const touched = ref(false);
const validationState = ref('idle');
let validationTimer = null;
let validationRequest = null;

const selectedCountry = computed(() => countries.value.find((country) => country.code === props.countryCode) ?? null);
const filteredCountries = computed(() => {
    const needle = normalize(query.value);

    if (!needle) {
        return countries.value;
    }

    return countries.value.filter(
        (country) => normalize(country.name).includes(needle) || country.dial_code?.includes(query.value.trim()),
    );
});
const maxDigits = computed(() => (props.countryCode === 'EC' ? 10 : selectedCountry.value?.phone_length?.max ?? 15));
const validationMessage = computed(() => {
    if (!props.number) {
        return 'Ingresa tu número de teléfono.';
    }

    if (validationState.value === 'pending') {
        return 'Verificando número...';
    }

    if (validationState.value === 'invalid') {
        return props.countryCode === 'EC'
            ? 'Ingresa un teléfono ecuatoriano válido, por ejemplo 0987654321.'
            : 'Ingresa un número válido para el país seleccionado.';
    }

    return '';
});
const showError = computed(() => props.hasError || (touched.value && validationState.value === 'invalid'));

watch(
    () => [props.number, props.countryCode],
    () => scheduleValidation(),
    { immediate: true },
);

onMounted(async () => {
    countries.value = await loadAll();
    scheduleValidation();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    clearTimeout(validationTimer);
    validationRequest?.abort();
    document.removeEventListener('click', handleClickOutside);
});

function normalize(value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

function hasPlausibleLength() {
    if (props.countryCode === 'EC') {
        return props.number.length === 9 || props.number.length === 10;
    }

    const length = selectedCountry.value?.phone_length;

    return length ? props.number.length >= length.min && props.number.length <= length.max : props.number.length >= 6;
}

function scheduleValidation() {
    clearTimeout(validationTimer);
    validationRequest?.abort();

    if (!props.number || !hasPlausibleLength()) {
        validationState.value = props.number ? 'invalid' : 'idle';
        emit('validation', { valid: false, pending: false });
        return;
    }

    validationState.value = 'pending';
    emit('validation', { valid: false, pending: true });
    validationTimer = setTimeout(validateNumber, 350);
}

async function validateNumber() {
    validationRequest = new AbortController();

    try {
        const response = await fetch('/api/phone/validate', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ country_code: props.countryCode, number: props.number }),
            signal: validationRequest.signal,
        });
        const payload = await response.json();
        const valid = response.ok && payload.data?.valid === true;
        validationState.value = valid ? 'valid' : 'invalid';
        emit('validation', { valid, pending: false, formatted: payload.data?.formatted ?? null });
    } catch (error) {
        if (error.name !== 'AbortError') {
            validationState.value = 'invalid';
            emit('validation', { valid: false, pending: false });
        }
    }
}

function selectCountry(country) {
    emit('update:countryCode', country.code);
    isOpen.value = false;
    query.value = '';
}

function onNumberInput(event) {
    const digitsOnly = event.target.value.replace(/\D/g, '').slice(0, maxDigits.value);
    emit('update:number', digitsOnly);
}

function onBlur() {
    touched.value = true;
    emit('blur');
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        isOpen.value = false;
    }
}
</script>

<template>
    <div ref="container" class="relative">
        <div class="grid grid-cols-[7.6rem_minmax(0,1fr)] gap-2">
            <button
                type="button"
                class="field-control flex items-center gap-1.5 px-3 text-sm"
                :class="showError ? 'field-control--error' : ''"
                :aria-expanded="isOpen"
                @click="isOpen = !isOpen"
            >
                <span v-if="selectedCountry" class="text-lg" aria-hidden="true">{{ selectedCountry.flag }}</span>
                <AppIcon v-else name="map-pin" :size="18" class="text-brand-navy-mid" />
                <span class="font-medium text-brand-navy">{{ selectedCountry?.dial_code ?? 'País' }}</span>
                <AppIcon name="chevron-down" :size="14" class="ml-auto text-slate-400 transition-transform" :class="isOpen ? 'rotate-180' : ''" />
            </button>

            <div class="field-control flex items-center px-3.5" :class="showError ? 'field-control--error' : ''">
                <input
                    type="tel"
                    inputmode="numeric"
                    autocomplete="tel-national"
                    :value="number"
                    :maxlength="maxDigits"
                    :placeholder="countryCode === 'EC' ? '0987654321' : 'Número de teléfono'"
                    class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm text-brand-navy focus:outline-none"
                    @input="onNumberInput"
                    @blur="onBlur"
                />
                <span v-if="validationState === 'pending'" class="h-4 w-4 animate-spin rounded-full border-2 border-slate-200 border-t-brand-navy-mid" aria-label="Verificando" />
                <span v-else-if="validationState === 'valid'" class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700" aria-label="Número válido"><AppIcon name="check" :size="12" :stroke-width="2.8" /></span>
            </div>
        </div>

        <p
            v-if="(touched || props.hasError) && validationMessage"
            class="mt-1.5 flex items-start gap-1.5 text-xs"
            :class="validationState === 'pending' ? 'text-brand-gray' : 'text-brand-coral-text'"
            :role="validationState === 'invalid' ? 'alert' : 'status'"
        >
            <AppIcon :name="validationState === 'pending' ? 'clock' : 'info-circle'" :size="14" class="mt-0.5 shrink-0" />
            {{ validationMessage }}
        </p>

        <Transition name="popover">
            <div v-if="isOpen" class="absolute left-0 top-full z-30 mt-2 w-[min(19rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-[0_20px_48px_rgba(22,36,61,0.18)]">
                <div class="border-b border-slate-100 p-2.5">
                    <div class="flex items-center gap-2 rounded-xl bg-brand-gray-light px-3">
                        <AppIcon name="search" :size="16" class="text-brand-gray" />
                        <input v-model="query" type="text" placeholder="Buscar país o código" class="min-w-0 flex-1 border-0 bg-transparent py-2.5 text-sm text-brand-navy focus:outline-none" />
                    </div>
                </div>
                <ul class="max-h-60 overflow-auto p-1.5">
                    <li v-for="country in filteredCountries" :key="country.code">
                        <button type="button" class="flex min-h-11 w-full items-center gap-2 rounded-xl px-3 text-left text-sm hover:bg-brand-gray-light" @click="selectCountry(country)">
                            <span class="text-base" aria-hidden="true">{{ country.flag }}</span>
                            <span class="flex-1 text-brand-navy">{{ country.name }}</span>
                            <span class="text-brand-gray">{{ country.dial_code }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
