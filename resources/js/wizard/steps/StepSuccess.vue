<script setup>
import AppIcon from '../components/AppIcon.vue';
import { useWizardState } from '../composables/useWizardState';

const { state, reset } = useWizardState();
</script>

<template>
    <div v-if="state.quote" class="flex min-h-[29rem] flex-col items-center justify-center text-center">
        <div class="relative mx-auto flex h-24 w-24 items-center justify-center rounded-[2rem] bg-brand-navy text-white shadow-[0_20px_45px_rgba(22,36,61,0.24)]" aria-hidden="true">
            <AppIcon name="shield" :size="46" :stroke-width="1.6" />
            <span class="absolute -right-1 -top-1 flex h-9 w-9 items-center justify-center rounded-full bg-brand-amber text-brand-navy shadow-lg"><AppIcon name="check" :size="20" :stroke-width="2.8" /></span>
        </div>

        <p class="mt-7 text-xs font-semibold tracking-[0.14em] text-brand-navy-mid uppercase">Todo listo</p>
        <h1 class="mt-2 font-heading text-3xl font-medium tracking-tight text-brand-navy sm:text-4xl">Seguro contratado</h1>
        <p class="mt-3 max-w-sm text-sm leading-6 text-brand-gray">El pago simulado fue aprobado y tu seguro quedó contratado. También creamos tu cuenta de cliente.</p>

        <div class="soft-card mt-6 px-5 py-3">
            <p class="text-[10px] font-semibold tracking-wide text-brand-gray uppercase">Referencia</p>
            <p class="mt-0.5 font-mono text-sm font-semibold text-brand-navy">{{ state.quote.reference }}</p>
        </div>

        <div v-if="state.quote.payment" class="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-brand-gray">
            <span>{{ state.quote.payment.card_brand_label }} •••• {{ state.quote.payment.card_last_four }}</span>
            <span class="hidden h-1 w-1 rounded-full bg-slate-300 sm:block" />
            <span>Aut. {{ state.quote.payment.authorization_code }}</span>
        </div>

        <div v-if="state.quote.account" class="mt-5 max-w-md rounded-2xl border border-sky-100 bg-sky-50 px-4 py-3 text-left text-xs leading-5 text-brand-navy">
            <p class="flex items-start gap-2">
                <AppIcon name="lock" :size="17" class="mt-0.5 shrink-0 text-brand-navy-mid" />
                <span>
                    Ya puedes iniciar sesión con tu cuenta de cliente. Tu usuario y tu contraseña son tu número de
                    identificación:
                    <strong class="font-mono">{{ state.quote.account.username }}</strong>.
                    Puedes cambiar la contraseña luego desde "¿Olvidaste tu contraseña?".
                </span>
            </p>
        </div>

        <div class="mt-8 flex w-full flex-col items-center gap-2.5">
            <a
                :href="`/api/quotes/${state.quote.reference}/pdf`"
                target="_blank"
                rel="noopener"
                class="primary-action w-full sm:w-auto sm:px-10"
            >
                <AppIcon name="download" :size="18" /> Descargar comprobante
            </a>
            <button type="button" class="secondary-action min-h-11" @click="reset()">
                <AppIcon name="home" :size="17" /> Volver al inicio
            </button>
            <a href="/login" class="secondary-action min-h-11"><AppIcon name="login" :size="17" /> Iniciar sesión</a>
        </div>
    </div>
</template>
