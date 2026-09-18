<script setup>
import AppIcon from '../components/AppIcon.vue';
</script>

<template>
    <div class="flex min-h-[26rem] flex-col items-center justify-center px-4 text-center" role="status">
        <div class="relative flex h-28 w-28 items-center justify-center">
            <span class="absolute inset-0 animate-ping rounded-full bg-brand-amber/15" aria-hidden="true" />
            <span class="absolute inset-2 animate-pulse rounded-full bg-brand-amber/20" aria-hidden="true" />
            <span class="relative flex h-20 w-20 items-center justify-center rounded-3xl bg-brand-navy text-white shadow-[0_18px_40px_rgba(22,36,61,0.22)]">
                <AppIcon name="shield" :size="38" :stroke-width="1.7" />
                <span class="absolute -right-1 -top-1 flex h-7 w-7 items-center justify-center rounded-full bg-brand-amber text-brand-navy shadow-md">
                    <AppIcon name="sparkle" :size="15" />
                </span>
            </span>
        </div>
        <h1 class="mt-7 font-heading text-2xl font-medium text-brand-navy sm:text-3xl">Preparando tu cotización</h1>
        <p class="mt-2 max-w-sm text-sm leading-6 text-brand-gray">Calculamos tu cobertura según el destino y la duración del viaje.</p>
        <div class="mt-7 flex items-center gap-2" aria-hidden="true">
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-gold [animation-delay:-0.3s]" />
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-gold [animation-delay:-0.15s]" />
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-brand-gold" />
        </div>
    </div>
</template>
