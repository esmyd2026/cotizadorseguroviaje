<script setup>
import AppIcon from './AppIcon.vue';

defineProps({
    active: { type: String, default: 'quote' },
    auth: { type: Object, default: null },
    csrfToken: { type: String, default: '' },
});

const emit = defineEmits(['select']);
const logoUrl = '/image/logo.png';
const items = [
    { id: 'quote', label: 'Cotizar', icon: 'plane' },
    { id: 'plans', label: 'Planes', icon: 'shield' },
    { id: 'faq', label: 'Preguntas', icon: 'question-circle' },
    { id: 'contact', label: 'Contacto', icon: 'phone' },
];
</script>

<template>
    <header class="desktop-top-nav hidden sm:block">
        <div class="mx-auto grid h-20 w-full max-w-[1360px] grid-cols-[auto_minmax(0,1fr)_auto] items-center gap-4 px-5 lg:gap-7 lg:px-8">
            <button type="button" class="shrink-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-gold focus:ring-offset-4" aria-label="Ir al cotizador" @click="emit('select', 'quote')">
                <img :src="logoUrl" alt="Gestión Segura" class="h-auto w-36 object-contain lg:w-44" />
            </button>

            <nav class="mx-auto flex min-w-0 items-center justify-center gap-1 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-1.5" aria-label="Navegación principal">
                <button
                    v-for="item in items"
                    :key="item.id"
                    type="button"
                    class="inline-flex min-h-10 shrink-0 items-center gap-2 rounded-xl px-2.5 text-xs font-semibold transition duration-200 lg:px-3.5 lg:text-sm"
                    :class="item.id === active ? 'bg-white text-brand-navy shadow-[0_4px_14px_rgba(22,36,61,0.09)]' : 'text-brand-gray hover:bg-white/75 hover:text-brand-navy'"
                    :aria-current="item.id === active ? 'page' : undefined"
                    @click="emit('select', item.id)"
                >
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg transition" :class="item.id === active ? 'bg-brand-amber/20 text-brand-navy-mid' : ''">
                        <AppIcon :name="item.icon" :size="15" />
                    </span>
                    <span class="hidden lg:inline">{{ item.label }}</span>
                </button>
            </nav>

            <div class="flex shrink-0 items-center gap-2 border-l border-slate-200 pl-4">
                <a v-if="!auth" href="/login" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-navy px-3.5 text-xs font-semibold text-white shadow-[0_8px_20px_rgba(22,36,61,0.16)] transition hover:-translate-y-0.5 hover:bg-brand-navy-mid lg:px-4">
                    <AppIcon name="login" :size="16" />
                    <span class="hidden lg:inline">Iniciar sesión</span>
                    <span class="lg:hidden">Ingresar</span>
                </a>
                <a v-else-if="auth.isAdmin" href="/admin/quotes" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-navy px-3.5 text-xs font-semibold text-white shadow-[0_8px_20px_rgba(22,36,61,0.16)] transition hover:-translate-y-0.5 hover:bg-brand-navy-mid lg:px-4">
                    <AppIcon name="login" :size="16" /> Panel
                </a>
                <div v-else class="flex items-center gap-2">
                    <div class="hidden items-center gap-2 xl:flex">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-amber/20 text-xs font-bold text-brand-navy">{{ auth.name.charAt(0).toUpperCase() }}</span>
                        <span class="max-w-24 truncate text-xs font-semibold text-brand-navy">{{ auth.name.split(' ')[0] }}</span>
                    </div>
                    <a href="/mis-cotizaciones" class="inline-flex min-h-11 items-center gap-2 rounded-xl bg-brand-navy px-3 text-xs font-semibold text-white shadow-[0_8px_20px_rgba(22,36,61,0.16)] transition hover:-translate-y-0.5 hover:bg-brand-navy-mid lg:px-4" title="Ver mis seguros">
                        <AppIcon name="document" :size="16" />
                        <span class="hidden lg:inline">Mis seguros</span>
                    </a>
                    <form method="POST" action="/logout">
                        <input type="hidden" name="_token" :value="csrfToken" />
                        <button type="submit" class="flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-brand-gray transition hover:border-slate-300 hover:bg-brand-gray-light hover:text-brand-navy" title="Cerrar sesión" aria-label="Cerrar sesión">
                            <AppIcon name="login" :size="16" />
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>
</template>
