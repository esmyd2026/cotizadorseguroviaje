<script setup>
import { computed, ref } from 'vue';
import AppIcon from './components/AppIcon.vue';
import InfoSheet from './components/InfoSheet.vue';
import MobileBottomNav from './components/MobileBottomNav.vue';
import ProgressStepper from './components/ProgressStepper.vue';
import { useQuoteApi } from './composables/useQuoteApi';
import { useWizardState } from './composables/useWizardState';
import StepCalculating from './steps/StepCalculating.vue';
import StepConfirmation from './steps/StepConfirmation.vue';
import StepDates from './steps/StepDates.vue';
import StepDestination from './steps/StepDestination.vue';
import StepResult from './steps/StepResult.vue';
import StepPayment from './steps/StepPayment.vue';
import StepSuccess from './steps/StepSuccess.vue';
import StepTraveler from './steps/StepTraveler.vue';

const { state, goTo, macroStep } = useWizardState();
const { createQuote } = useQuoteApi();
const logoUrl = '/image/logo.png';

const stepComponents = {
    destination: StepDestination,
    dates: StepDates,
    traveler: StepTraveler,
    calculating: StepCalculating,
    result: StepResult,
    confirmation: StepConfirmation,
    payment: StepPayment,
    success: StepSuccess,
};

const currentComponent = computed(() => stepComponents[state.step]);
const showStepper = computed(() => state.step !== 'success');
const currentMacroStep = computed(() => macroStep());
const infoSection = ref(null);

async function submitQuote() {
    state.serverError = null;
    state.fieldErrors = {};
    goTo('calculating');

    const emailDomain = state.traveler.emailProvider === 'otro' ? state.traveler.emailCustomDomain : state.traveler.emailProvider;

    const payload = {
        trip_type: state.tripType,
        destination_country_codes: state.destinations.map((destination) => destination.code),
        destination_country_code: state.destination.code,
        departure_date: state.departureDate,
        return_date: state.returnDate,
        first_name: state.traveler.firstName.toLocaleUpperCase('es-EC'),
        last_name: state.traveler.lastName.toLocaleUpperCase('es-EC'),
        document_type: state.traveler.documentType,
        document_id: state.traveler.documentId,
        email: `${state.traveler.emailLocalPart}@${emailDomain}`.toLocaleUpperCase('es-EC'),
        phone_country_code: state.traveler.phoneCountryCode,
        phone_number: state.traveler.phoneNumber,
        birth_date: state.traveler.birthDate,
    };

    const minimumDelay = new Promise((resolve) => setTimeout(resolve, 600));
    const [result] = await Promise.all([createQuote(payload), minimumDelay]);

    if (!result.ok) {
        state.fieldErrors = result.fieldErrors ?? {};
        state.serverError = result.message;
        goTo('traveler');
        return;
    }

    state.quote = result.data;
    goTo('result');
}

function handleNext() {
    const transitions = {
        destination: 'dates',
        dates: 'traveler',
        result: 'confirmation',
        confirmation: 'payment',
        payment: 'success',
    };

    if (state.step === 'traveler') {
        submitQuote();
        return;
    }

    const next = transitions[state.step];

    if (next) {
        goTo(next);
    }
}

function handleBack() {
    const transitions = {
        dates: 'destination',
        traveler: 'dates',
    };

    const previous = transitions[state.step];

    if (previous) {
        goTo(previous);
    }
}

function handleMobileNavigation(section) {
    infoSection.value = section === 'quote' ? null : section;
}
</script>

<template>
    <div class="app-shell min-h-screen pb-24 sm:pb-0">
        <header class="app-header sm:hidden">
            <div class="mx-auto flex h-16 max-w-md items-center justify-between px-4">
                <div class="flex items-center gap-2.5">
                    <img :src="logoUrl" alt="Gestión Segura" class="h-9 w-auto object-contain" />
                </div>
                <a href="/login" class="trust-pill transition hover:border-brand-gold hover:bg-amber-50"><AppIcon name="login" :size="14" /> Iniciar sesión</a>
            </div>
        </header>

        <main class="mx-auto grid w-full max-w-[1180px] gap-8 px-3 py-4 sm:px-6 sm:py-10 lg:grid-cols-[300px_minmax(0,760px)] lg:gap-12 lg:py-14">
            <aside class="hidden lg:flex lg:flex-col lg:justify-between">
                <div>
                    <div class="flex items-center gap-3">
                        <img :src="logoUrl" alt="Gestión Segura" class="h-auto w-56 object-contain" />
                    </div>

                    <div class="mt-14">
                        <span class="eyebrow"><AppIcon name="sparkle" :size="15" /> Cotiza en pocos minutos</span>
                        <h1 class="mt-5 text-4xl font-light leading-[1.08] tracking-[-0.03em] text-brand-navy">
                            Viaja tranquilo.<br />Nosotros te<br /><span class="font-medium text-brand-navy-mid">acompañamos.</span>
                        </h1>
                        <p class="mt-5 max-w-[270px] text-sm leading-6 text-brand-gray">
                            Una experiencia simple, clara y respaldada por asesoría profesional en seguros.
                        </p>
                    </div>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white/75 p-4 shadow-sm">
                    <p class="text-xs font-semibold text-brand-navy">¿Necesitas orientación?</p>
                    <p class="mt-1 text-xs leading-5 text-brand-gray">Conversa con un asesor antes de contratar.</p>
                    <a href="tel:+593989596590" class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-brand-navy-mid"><AppIcon name="phone" :size="15" /> +593 988 492 339</a>
                </div>
            </aside>

            <section class="min-w-0">
                <div class="mb-3 hidden items-center justify-end sm:flex">
                    <a href="/login" class="inline-flex min-h-10 items-center gap-2 rounded-xl px-3 text-xs font-semibold text-brand-navy-mid transition hover:bg-white hover:shadow-sm">
                        <AppIcon name="login" :size="16" /> ¿Ya eres cliente? Iniciar sesión
                    </a>
                </div>
                <ProgressStepper v-if="showStepper" :current="currentMacroStep" class="mb-4 sm:mb-5" />

                <div class="wizard-surface">
                    <Transition name="step" mode="out-in">
                        <component :is="currentComponent" :key="state.step" @next="handleNext" @back="handleBack" />
                    </Transition>
                </div>

                <p class="mt-4 hidden items-center justify-center gap-1.5 text-xs text-brand-gray sm:flex">
                    <AppIcon name="lock" :size="13" /> Tus datos se procesan de forma segura.
                </p>
            </section>
        </main>

        <MobileBottomNav :active="infoSection ?? 'quote'" @select="handleMobileNavigation" />
        <InfoSheet v-if="infoSection" :section="infoSection" @close="infoSection = null" />
    </div>
</template>
