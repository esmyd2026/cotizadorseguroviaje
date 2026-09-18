<script setup>
import AppIcon from './AppIcon.vue';

defineProps({ section: { type: String, required: true } });
defineEmits(['close']);
const logoUrl = '/image/logo.png';
</script>

<template>
    <Teleport to="body">
        <Transition name="sheet">
            <div class="fixed inset-0 z-50 flex items-end bg-brand-navy/35 backdrop-blur-[2px] sm:items-center sm:justify-center sm:p-6" @click.self="$emit('close')">
                <section class="max-h-[78vh] w-full overflow-y-auto rounded-t-[1.75rem] bg-white px-5 pb-[calc(6.5rem+env(safe-area-inset-bottom))] pt-3 shadow-[0_-20px_60px_rgba(22,36,61,0.2)] sm:max-w-xl sm:rounded-[1.75rem] sm:p-7 sm:shadow-[0_24px_70px_rgba(22,36,61,0.24)]">
                    <div class="mx-auto h-1 w-10 rounded-full bg-slate-200 sm:hidden" />
                    <div class="mt-4 flex items-center justify-between">
                        <img :src="logoUrl" alt="Gestión Segura" class="h-9 w-auto" />
                        <button type="button" class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-gray-light text-brand-gray" aria-label="Cerrar" @click="$emit('close')"><AppIcon name="x" :size="18" /></button>
                    </div>

                    <div v-if="section === 'plans'" class="mt-6">
                        <span class="section-kicker"><AppIcon name="shield" :size="15" /> Coberturas</span>
                        <h2 class="step-title text-2xl">Protección para tu viaje</h2>
                        <p class="step-copy">La cotización reúne coberturas esenciales para acompañarte fuera de casa.</p>
                        <div class="mt-5 grid gap-3">
                            <article class="rounded-2xl border-2 border-brand-amber bg-brand-amber/5 p-4">
                                <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold text-brand-navy-mid">PLAN DEMOSTRATIVO</p><h3 class="mt-1 text-lg font-medium text-brand-navy">Protección Viajera</h3></div><span class="rounded-full bg-brand-amber px-2.5 py-1 text-[10px] font-bold text-brand-navy">RECOMENDADO</span></div>
                                <ul class="mt-4 space-y-2.5 text-sm text-brand-gray">
                                    <li class="flex gap-2"><AppIcon name="check-circle" :size="17" class="shrink-0 text-brand-navy-mid" /> Asistencia médica durante el viaje</li>
                                    <li class="flex gap-2"><AppIcon name="check-circle" :size="17" class="shrink-0 text-brand-navy-mid" /> Orientación ante pérdida de equipaje</li>
                                    <li class="flex gap-2"><AppIcon name="check-circle" :size="17" class="shrink-0 text-brand-navy-mid" /> Atención para emergencias en viaje</li>
                                    <li class="flex gap-2"><AppIcon name="check-circle" :size="17" class="shrink-0 text-brand-navy-mid" /> Cobertura para uno o varios destinos</li>
                                </ul>
                            </article>
                        </div>
                        <p class="mt-3 text-[11px] leading-5 text-brand-gray">Información demostrativa. Las coberturas, exclusiones y límites definitivos se establecen en la póliza emitida.</p>
                    </div>

                    <div v-else-if="section === 'faq'" class="mt-6">
                        <span class="section-kicker"><AppIcon name="question-circle" :size="15" /> Ayuda</span>
                        <h2 class="step-title text-2xl">Preguntas frecuentes</h2>
                        <div class="mt-5 divide-y divide-slate-100 rounded-2xl border border-slate-200 px-4">
                            <details class="group py-4" open><summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-brand-navy">¿Cómo se calcula el precio?<AppIcon name="chevron-down" :size="16" class="transition group-open:rotate-180" /></summary><p class="mt-2 pr-5 text-xs leading-5 text-brand-gray">Se consideran los días de viaje y el recargo de la región de destino. En recorridos con varias paradas se aplica el recargo regional más alto.</p></details>
                            <details class="group py-4"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-brand-navy">¿Puedo incluir varios países?<AppIcon name="chevron-down" :size="16" class="transition group-open:rotate-180" /></summary><p class="mt-2 pr-5 text-xs leading-5 text-brand-gray">Sí. Selecciona “Varias paradas” al comenzar y agrega entre dos y cinco países.</p></details>
                            <details class="group py-4"><summary class="flex cursor-pointer list-none items-center justify-between gap-3 text-sm font-semibold text-brand-navy">¿La cotización se puede descargar?<AppIcon name="chevron-down" :size="16" class="transition group-open:rotate-180" /></summary><p class="mt-2 pr-5 text-xs leading-5 text-brand-gray">Sí. Antes de contratar podrás descargar un PDF con el itinerario, los datos y el desglose del precio.</p></details>
                        </div>
                    </div>

                    <div v-else class="mt-6">
                        <span class="section-kicker"><AppIcon name="phone" :size="15" /> Asesoría</span>
                        <h2 class="step-title text-2xl">Estamos para ayudarte</h2>
                        <p class="step-copy">Un asesor puede orientarte antes de contratar tu seguro de viaje.</p>
                        <div class="mt-5 space-y-3">
                            <a href="tel:+593989596590" class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><span class="field-icon"><AppIcon name="phone" :size="18" /></span><span><span class="block text-xs text-brand-gray">Teléfono</span><span class="text-sm font-semibold text-brand-navy">+593 988 492 339</span></span></a>
                            <a href="mailto:contacto@gestionsegura.com.ec" class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><span class="field-icon"><AppIcon name="mail" :size="18" /></span><span class="min-w-0"><span class="block text-xs text-brand-gray">Correo</span><span class="block truncate text-sm font-semibold text-brand-navy">contacto@gestionsegura.com.ec</span></span></a>
                            <div class="flex items-center gap-3 rounded-2xl border border-slate-200 p-4"><span class="field-icon"><AppIcon name="clock" :size="18" /></span><span><span class="block text-xs text-brand-gray">Horario</span><span class="text-sm font-semibold text-brand-navy">Lun–Vie · 08:30–17:30</span></span></div>
                        </div>
                    </div>
                </section>
            </div>
        </Transition>
    </Teleport>
</template>
