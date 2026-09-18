<script setup>
import { computed } from 'vue';
import { formatShortDate } from '../utils/dates';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    destination: { type: Object, required: true },
    destinations: { type: Array, default: () => [] },
    departureDate: { type: String, required: true },
    returnDate: { type: String, required: true },
    days: { type: Number, required: true },
    editable: { type: Boolean, default: true },
});

defineEmits(['edit']);

const itinerary = computed(() => (props.destinations.length ? props.destinations : [props.destination]));
const itineraryLabel = computed(() =>
    itinerary.value.length === 1 ? itinerary.value[0].name : `${itinerary.value.length} países en tu recorrido`,
);
</script>

<template>
    <div class="soft-card mb-6 flex items-center justify-between gap-3 p-3.5 sm:px-4">
        <div class="flex min-w-0 items-center gap-3 text-sm">
            <span class="flex shrink-0 -space-x-2">
                <span v-for="item in itinerary.slice(0, 3)" :key="item.code" class="flex h-9 w-9 items-center justify-center rounded-xl border-2 border-white bg-white text-lg shadow-sm">{{ item.flag }}</span>
            </span>
            <div class="min-w-0">
                <p class="truncate font-semibold text-brand-navy">{{ itineraryLabel }}</p>
                <p class="truncate text-xs text-brand-gray">{{ formatShortDate(departureDate) }} → {{ formatShortDate(returnDate) }} · {{ days }} días</p>
            </div>
        </div>
        <button
            v-if="editable"
            type="button"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-brand-navy-mid transition hover:bg-white hover:shadow-sm"
            aria-label="Editar viaje"
            @click="$emit('edit')"
        >
            <AppIcon name="edit" :size="17" />
        </button>
    </div>
</template>
