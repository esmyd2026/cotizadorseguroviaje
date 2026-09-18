<script setup>
import AppIcon from './AppIcon.vue';

const props = defineProps({
    current: {
        type: Number,
        required: true,
    },
});

const steps = [
    { label: 'Tu viaje', icon: 'plane' },
    { label: 'Tus datos', icon: 'user' },
    { label: 'Cotización', icon: 'quote' },
    { label: 'Confirmación', icon: 'check-circle' },
    { label: 'Pago', icon: 'credit-card' },
];
</script>

<template>
    <div>
        <div class="sm:hidden">
            <div class="mb-2 flex items-center justify-between text-xs">
                <span class="font-semibold text-brand-navy">{{ steps[props.current - 1].label }}</span>
                <span class="text-brand-gray">Paso {{ props.current }} de {{ steps.length }}</span>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-slate-200">
                <div class="h-full rounded-full bg-brand-navy-mid transition-all duration-300" :style="{ width: `${(props.current / steps.length) * 100}%` }" />
            </div>
        </div>

        <ol class="hidden items-center sm:flex">
            <li v-for="(step, index) in steps" :key="step.label" class="flex flex-1 items-center last:flex-none">
                <div class="flex items-center gap-2">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border text-xs font-semibold transition-all duration-300"
                        :class="
                            index + 1 <= props.current
                                ? 'border-brand-navy-mid bg-brand-navy-mid text-white shadow-[0_5px_14px_rgba(35,50,102,0.18)]'
                                : 'border-slate-200 bg-white text-slate-400'
                        "
                    >
                        <AppIcon :name="index + 1 < props.current ? 'check' : step.icon" :size="16" :stroke-width="2.2" />
                    </span>
                    <span
                        class="hidden text-sm font-medium xl:block"
                        :class="index + 1 <= props.current ? 'text-brand-navy' : 'text-slate-400'"
                    >
                        {{ step.label }}
                    </span>
                </div>
                <div
                    v-if="index < steps.length - 1"
                    class="mx-2 h-0.5 flex-1 overflow-hidden rounded-full bg-slate-200 transition-colors duration-300 xl:mx-4"
                    :class="index + 1 < props.current ? 'bg-brand-navy-mid' : 'bg-slate-200'"
                />
            </li>
        </ol>
    </div>
</template>
