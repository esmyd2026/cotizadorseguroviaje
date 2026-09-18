<script setup>
import AppIcon from './AppIcon.vue';

defineProps({ active: { type: String, default: 'quote' } });
const emit = defineEmits(['select']);

const items = [
    { id: 'quote', label: 'Cotizar', icon: 'plane' },
    { id: 'plans', label: 'Planes', icon: 'shield' },
    { id: 'faq', label: 'Preguntas', icon: 'question-circle' },
    { id: 'contact', label: 'Contacto', icon: 'phone' },
];
</script>

<template>
    <nav class="mobile-bottom-nav sm:hidden" aria-label="Navegación principal">
        <div class="mobile-bottom-dock mx-auto grid max-w-sm grid-cols-4 px-2 py-2">
            <button
                v-for="item in items"
                :key="item.id"
                type="button"
                class="flex min-h-14 flex-col items-center justify-center gap-1 rounded-2xl px-1 transition duration-200"
                :class="item.id === active ? 'text-brand-navy' : 'text-slate-400 hover:text-brand-navy-mid'"
                :aria-current="item.id === active ? 'page' : undefined"
                @click="emit('select', item.id)"
            >
                <span class="flex h-7 w-11 items-center justify-center rounded-full transition-all duration-200" :class="item.id === active ? 'bg-brand-amber text-brand-navy shadow-[0_4px_14px_rgba(250,182,0,0.28)]' : ''">
                    <AppIcon :name="item.icon" :size="18" :stroke-width="item.id === active ? 2.2 : 1.8" />
                </span>
                <span class="text-[10px] font-medium leading-none">{{ item.label }}</span>
            </button>
        </div>
    </nav>
</template>
