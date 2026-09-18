<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    modelValue: { type: String, default: '' },
    min: { type: String, default: '' },
    max: { type: String, default: '' },
    initialView: { type: String, default: '' },
    placeholder: { type: String, default: 'Selecciona una fecha' },
    align: { type: String, default: 'left' },
    hasError: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'blur']);

const container = ref(null);
const isOpen = ref(false);
const showYears = ref(false);
const viewDate = ref(new Date());
const yearPageStart = ref(new Date().getFullYear() - 5);

const weekdays = ['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'];
const monthFormatter = new Intl.DateTimeFormat('es-EC', { month: 'long', year: 'numeric' });
const dateFormatter = new Intl.DateTimeFormat('es-EC', { day: '2-digit', month: 'short', year: 'numeric' });

const displayValue = computed(() => (props.modelValue ? dateFormatter.format(parseIso(props.modelValue)) : ''));
const monthLabel = computed(() => {
    const label = monthFormatter.format(viewDate.value);

    return label.charAt(0).toUpperCase() + label.slice(1);
});

const calendarDays = computed(() => {
    const year = viewDate.value.getFullYear();
    const month = viewDate.value.getMonth();
    const leadingBlanks = (new Date(year, month, 1).getDay() + 6) % 7;
    const totalDays = new Date(year, month + 1, 0).getDate();
    const days = Array.from({ length: leadingBlanks }, () => null);

    for (let day = 1; day <= totalDays; day++) {
        const iso = formatIso(new Date(year, month, day));
        days.push({ day, iso, disabled: isOutsideRange(iso) });
    }

    return days;
});

const yearOptions = computed(() => Array.from({ length: 12 }, (_, index) => yearPageStart.value + index));

function parseIso(value) {
    const [year, month, day] = value.split('-').map(Number);

    return new Date(year, month - 1, day);
}

function formatIso(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function isOutsideRange(value) {
    return (props.min && value < props.min) || (props.max && value > props.max);
}

function open() {
    const preferredDate = props.modelValue || props.initialView || props.max || props.min;
    viewDate.value = preferredDate ? parseIso(preferredDate) : new Date();
    yearPageStart.value = viewDate.value.getFullYear() - 5;
    showYears.value = false;
    isOpen.value = true;
}

function close() {
    if (isOpen.value) {
        isOpen.value = false;
        showYears.value = false;
        emit('blur');
    }
}

function changeMonth(offset) {
    if (showYears.value) {
        yearPageStart.value += offset * 12;
        return;
    }

    viewDate.value = new Date(viewDate.value.getFullYear(), viewDate.value.getMonth() + offset, 1);
}

function selectYear(year) {
    viewDate.value = new Date(year, viewDate.value.getMonth(), 1);
    showYears.value = false;
}

function selectDate(day) {
    if (!day || day.disabled) {
        return;
    }

    emit('update:modelValue', day.iso);
    close();
}

function toggleYears() {
    yearPageStart.value = viewDate.value.getFullYear() - 5;
    showYears.value = !showYears.value;
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        close();
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="container" class="relative">
        <button
            type="button"
            class="field-control flex items-center gap-3 px-3.5 text-left"
            :class="hasError ? 'field-control--error' : ''"
            :aria-expanded="isOpen"
            @click="isOpen ? close() : open()"
            @keydown.esc="close"
        >
            <span class="field-icon"><AppIcon name="calendar" :size="18" /></span>
            <span class="min-w-0 flex-1 text-sm" :class="displayValue ? 'font-medium text-brand-navy' : 'text-slate-400'">
                {{ displayValue || placeholder }}
            </span>
            <AppIcon name="chevron-down" :size="16" class="shrink-0 text-slate-400 transition-transform" :class="isOpen ? 'rotate-180' : ''" />
        </button>

        <Transition name="popover">
            <div
                v-if="isOpen"
                class="absolute top-full z-30 mt-2 w-[min(19rem,calc(100vw-2rem))] rounded-2xl border border-slate-200 bg-white p-3 shadow-[0_24px_60px_rgba(22,36,61,0.2)]"
                :class="align === 'right' ? 'right-0' : 'left-0'"
            >
                <div class="flex items-center justify-between gap-2">
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl text-brand-gray hover:bg-brand-gray-light hover:text-brand-navy" aria-label="Periodo anterior" @click="changeMonth(-1)">
                        <AppIcon name="chevron-left" :size="17" />
                    </button>
                    <button type="button" class="min-h-9 flex-1 rounded-xl px-2 text-sm font-semibold text-brand-navy hover:bg-brand-gray-light" @click="toggleYears">
                        {{ showYears ? `${yearOptions[0]} – ${yearOptions[yearOptions.length - 1]}` : monthLabel }}
                    </button>
                    <button type="button" class="flex h-9 w-9 items-center justify-center rounded-xl text-brand-gray hover:bg-brand-gray-light hover:text-brand-navy" aria-label="Periodo siguiente" @click="changeMonth(1)">
                        <AppIcon name="chevron-right" :size="17" />
                    </button>
                </div>

                <div v-if="showYears" class="mt-3 grid grid-cols-3 gap-1.5">
                    <button
                        v-for="year in yearOptions"
                        :key="year"
                        type="button"
                        class="min-h-10 rounded-xl text-sm font-medium transition"
                        :class="year === viewDate.getFullYear() ? 'bg-brand-navy text-white' : 'text-brand-gray hover:bg-brand-gray-light hover:text-brand-navy'"
                        @click="selectYear(year)"
                    >
                        {{ year }}
                    </button>
                </div>

                <template v-else>
                    <div class="mt-3 grid grid-cols-7 gap-1">
                        <span v-for="weekday in weekdays" :key="weekday" class="flex h-7 items-center justify-center text-[10px] font-semibold text-slate-400 uppercase">{{ weekday }}</span>
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        <span v-for="(day, index) in calendarDays" :key="day?.iso ?? `blank-${index}`" class="aspect-square">
                            <button
                                v-if="day"
                                type="button"
                                class="flex h-full w-full items-center justify-center rounded-xl text-xs font-medium transition"
                                :class="[
                                    day.iso === modelValue ? 'bg-brand-navy text-white shadow-md' : '',
                                    day.disabled ? 'cursor-not-allowed text-slate-300' : day.iso !== modelValue ? 'text-brand-navy hover:bg-brand-amber/20' : '',
                                ]"
                                :disabled="day.disabled"
                                @click="selectDate(day)"
                            >
                                {{ day.day }}
                            </button>
                        </span>
                    </div>
                </template>
            </div>
        </Transition>
    </div>
</template>
