<script setup>
import { ref } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import { useWizardState } from '../composables/useWizardState';
import { formatShortDate } from '../utils/dates';
import { regionLabel } from '../utils/regions';

const emit = defineEmits(['next']);
const { state } = useWizardState();
const showBreakdown = ref(false);
const logoUrl = '/image/logo.png';

function money(value) {
    return `USD $${Number(value).toFixed(2)}`;
}
</script>

<template>
    <div v-if="state.quote">
        <div class="flex items-center justify-between gap-4">
            <img :src="logoUrl" alt="Gestión Segura" class="h-auto w-36 object-contain sm:w-44" />
            <div class="text-right">
                <p class="text-[9px] font-semibold tracking-[0.12em] text-brand-gray uppercase">Referencia</p>
                <p class="font-mono text-xs font-semibold text-brand-navy">{{ state.quote.reference }}</p>
            </div>
        </div>

        <div class="relative mt-6 overflow-hidden rounded-[1.6rem] bg-brand-navy px-5 py-7 text-white shadow-[0_22px_50px_rgba(22,36,61,0.22)] sm:px-8 sm:py-9">
            <span class="absolute -right-12 -top-16 h-44 w-44 rounded-full bg-brand-amber/15" aria-hidden="true" />
            <span class="absolute -bottom-20 -left-14 h-44 w-44 rounded-full border-[28px] border-white/5" aria-hidden="true" />

            <div class="relative">
                <p class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1.5 text-[10px] font-semibold tracking-wide text-white uppercase">
                    <AppIcon name="check-circle" :size="15" /> Cotización lista
                </p>

                <div class="mt-6 flex items-center gap-3">
                    <span class="flex shrink-0 -space-x-2" aria-hidden="true">
                        <span v-for="destination in state.quote.destinations.slice(0, 3)" :key="destination.code" class="flex h-11 w-11 items-center justify-center rounded-2xl border-2 border-brand-navy bg-white text-xl shadow-lg">{{ destination.flag }}</span>
                    </span>
                    <div>
                        <p class="text-xs text-white/55">{{ state.quote.destinations.length > 1 ? 'Tu recorrido' : 'Tu viaje a' }}</p>
                        <p class="font-heading text-lg font-medium">{{ state.quote.destination.country_name }}</p>
                    </div>
                </div>

                <div class="mt-6 border-t border-white/10 pt-6 text-center sm:text-left">
                    <p class="text-[10px] font-semibold tracking-[0.14em] text-white/55 uppercase">Total del seguro</p>
                    <p class="font-heading mt-1 text-5xl font-medium tracking-[-0.045em] text-white sm:text-6xl">{{ money(state.quote.pricing.total) }}</p>
                    <p class="mt-2 flex items-center justify-center gap-1.5 text-xs text-white/60 sm:justify-start">
                        <AppIcon name="calendar" :size="14" />
                        {{ formatShortDate(state.quote.trip.departure_date) }} — {{ formatShortDate(state.quote.trip.return_date) }} · {{ state.quote.trip.days }} días
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4 rounded-2xl border border-slate-200 bg-white">
            <button
                type="button"
                class="flex min-h-14 w-full items-center justify-between gap-3 px-4 text-left text-sm font-semibold text-brand-navy transition hover:bg-brand-gray-light/60 sm:px-5"
                :aria-expanded="showBreakdown"
                aria-controls="price-breakdown"
                @click="showBreakdown = !showBreakdown"
            >
                <span class="flex items-center gap-2.5"><span class="field-icon"><AppIcon name="quote" :size="17" /></span> ¿Cómo calculamos el precio?</span>
                <AppIcon name="chevron-down" :size="17" class="shrink-0 text-brand-gray transition-transform duration-200" :class="showBreakdown ? 'rotate-180' : ''" />
            </button>

            <Transition name="expand">
                <div v-if="showBreakdown" id="price-breakdown">
                    <div class="overflow-hidden">
                        <dl class="space-y-3 border-t border-slate-100 px-4 py-4 text-sm sm:px-5">
                            <div class="flex items-center justify-between gap-4 text-brand-gray">
                                <dt>{{ state.quote.trip.days }} días × {{ money(state.quote.pricing.daily_rate) }}</dt>
                                <dd class="font-medium text-brand-navy">{{ money(state.quote.pricing.subtotal) }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 text-brand-gray">
                                <dt>Recargo {{ regionLabel(state.quote.destination.region) }} · {{ Number(state.quote.pricing.surcharge_percentage) }}%</dt>
                                <dd class="font-medium text-brand-navy">{{ money(state.quote.pricing.surcharge_amount) }}</dd>
                            </div>
                            <div class="flex items-center justify-between border-t border-slate-200 pt-3 text-base font-semibold text-brand-navy">
                                <dt>Total</dt>
                                <dd>{{ money(state.quote.pricing.total) }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </Transition>
        </div>

        <div class="mt-6 grid gap-2.5 sm:grid-cols-[1fr_auto]">
            <button type="button" class="primary-action order-1 w-full sm:order-2 sm:px-8" @click="emit('next')">
                Contratar seguro <AppIcon name="arrow-right" :size="18" />
            </button>
            <a :href="`/api/quotes/${state.quote.reference}/pdf`" target="_blank" rel="noopener" class="secondary-action order-2 min-h-12 border border-slate-200 sm:order-1">
                <AppIcon name="download" :size="17" /> Descargar PDF
            </a>
        </div>

        <p class="mt-4 flex items-center justify-center gap-1.5 text-xs text-brand-gray"><AppIcon name="shield" :size="14" /> Precio calculado y protegido por Gestión Segura.</p>
    </div>
</template>
