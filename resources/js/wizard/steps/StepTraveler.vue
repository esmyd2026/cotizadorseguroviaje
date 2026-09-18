<script setup>
import { computed, reactive, watch } from 'vue';
import AppIcon from '../components/AppIcon.vue';
import DatePicker from '../components/DatePicker.vue';
import EmailInput from '../components/EmailInput.vue';
import PhoneInput from '../components/PhoneInput.vue';
import TripSummary from '../components/TripSummary.vue';
import { useWizardState } from '../composables/useWizardState';
import { tripDays } from '../utils/dates';

const emit = defineEmits(['next', 'back']);
const { state, goTo } = useWizardState();

const documentTypes = [
    { value: 'cedula', label: 'Cédula', description: 'Ecuador', icon: 'id-card' },
    { value: 'passport', label: 'Pasaporte', description: 'Internacional', icon: 'document' },
];
const touched = reactive({
    firstName: false,
    lastName: false,
    documentId: false,
    email: false,
    phone: false,
    birthDate: false,
});
const phoneValidation = reactive({ valid: false, pending: false });
const days = computed(() => tripDays(state.departureDate, state.returnDate));
const adultCutoff = computed(() => {
    const date = new Date();
    date.setFullYear(date.getFullYear() - 18);

    return formatIso(date);
});
const emailDomain = computed(() =>
    state.traveler.emailProvider === 'otro' ? state.traveler.emailCustomDomain : state.traveler.emailProvider,
);
const email = computed(() => `${state.traveler.emailLocalPart}@${emailDomain.value}`);

const localErrors = computed(() => ({
    firstName: validateName(state.traveler.firstName, 'nombre'),
    lastName: validateName(state.traveler.lastName, 'apellido'),
    documentId: validateDocument(state.traveler.documentType, state.traveler.documentId),
    email: validateEmail(),
    phone: phoneValidation.valid ? null : 'Ingresa un teléfono válido para el país seleccionado.',
    birthDate: !state.traveler.birthDate
        ? 'Selecciona tu fecha de nacimiento.'
        : state.traveler.birthDate > adultCutoff.value
          ? 'Debes ser mayor de edad para contratar el seguro.'
          : null,
}));
const isValid = computed(
    () => Object.values(localErrors.value).every((message) => message === null) && !phoneValidation.pending,
);
const completedFields = computed(() => Object.values(localErrors.value).filter((message) => message === null).length);

watch(
    () => [state.traveler.emailLocalPart, state.traveler.emailProvider, state.traveler.emailCustomDomain],
    () => clearFieldError('email'),
);

function formatIso(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function validateName(value, fieldName) {
    const normalized = value.trim();

    if (!normalized) {
        return `Ingresa tu ${fieldName}.`;
    }

    if (normalized.length < 2 || !/^[\p{L}\s'-]+$/u.test(normalized)) {
        return `El ${fieldName} debe contener al menos 2 letras.`;
    }

    return null;
}

function validateDocument(type, value) {
    if (!value) {
        return 'Ingresa tu número de identificación.';
    }

    if (type === 'passport') {
        return /^[A-Z0-9]{6,20}$/.test(value) ? null : 'Usa entre 6 y 20 letras o números.';
    }

    if (!/^\d{10}$/.test(value)) {
        return 'La cédula debe tener exactamente 10 dígitos.';
    }

    const province = Number(value.slice(0, 2));
    const thirdDigit = Number(value[2]);

    if (province < 1 || province > 24 || thirdDigit > 5) {
        return 'Ingresa una cédula ecuatoriana válida.';
    }

    let sum = 0;

    for (let index = 0; index < 9; index++) {
        const product = Number(value[index]) * (index % 2 === 0 ? 2 : 1);
        sum += product > 9 ? product - 9 : product;
    }

    const checkDigit = (10 - (sum % 10)) % 10;

    return checkDigit === Number(value[9]) ? null : 'El dígito verificador de la cédula no es válido.';
}

function validateEmail() {
    if (!state.traveler.emailLocalPart.trim() || !emailDomain.value.trim()) {
        return 'Completa tu correo electrónico.';
    }

    if (state.traveler.emailProvider === 'otro' && !/^(?!-)(?:[a-z0-9-]+\.)+[a-z]{2,}$/i.test(emailDomain.value)) {
        return 'Ingresa un dominio válido, por ejemplo empresa.com.';
    }

    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value) ? null : 'Ingresa un correo electrónico válido.';
}

function visibleError(field, serverField) {
    const serverError = state.fieldErrors?.[serverField]?.[0];

    return serverError ?? (touched[field] ? localErrors.value[field] : null);
}

function clearFieldError(field) {
    if (state.fieldErrors?.[field]) {
        delete state.fieldErrors[field];
    }
}

function onNameInput(field, serverField, event) {
    state.traveler[field] = event.target.value.toLocaleUpperCase('es-EC');
    clearFieldError(serverField);
}

function selectDocumentType(type) {
    if (state.traveler.documentType === type) {
        return;
    }

    state.traveler.documentType = type;
    state.traveler.documentId = '';
    touched.documentId = false;
    clearFieldError('document_id');
}

function onDocumentInput(event) {
    const rawValue = event.target.value.toUpperCase();
    state.traveler.documentId = state.traveler.documentType === 'cedula'
        ? rawValue.replace(/\D/g, '').slice(0, 10)
        : rawValue.replace(/[^A-Z0-9]/g, '').slice(0, 20);
    clearFieldError('document_id');
}

function setBirthDate(value) {
    state.traveler.birthDate = value;
    clearFieldError('birth_date');
}

function handlePhoneValidation(validation) {
    Object.assign(phoneValidation, validation);
    clearFieldError('phone_number');
}

function continueToNext() {
    Object.keys(touched).forEach((key) => {
        touched[key] = true;
    });

    if (isValid.value) {
        emit('next');
    }
}
</script>

<template>
    <div>
        <TripSummary
            v-if="state.destination"
            :destination="state.destination"
            :destinations="state.destinations"
            :departure-date="state.departureDate"
            :return-date="state.returnDate"
            :days="days"
            @edit="goTo('destination')"
        />

        <div class="flex items-start justify-between gap-4">
            <div>
                <span class="section-kicker"><AppIcon name="user" :size="15" /> Datos del viajero</span>
                <h1 class="step-title">¿Quién va a viajar?</h1>
                <p class="step-copy">Completa los datos tal como aparecen en tu documento.</p>
            </div>
            <span class="hidden shrink-0 rounded-full bg-brand-gray-light px-3 py-1.5 text-xs font-semibold text-brand-navy-mid sm:inline-flex">
                {{ completedFields }}/6 completos
            </span>
        </div>

        <div class="mt-7 grid grid-cols-1 gap-x-4 gap-y-5 sm:mt-8 sm:grid-cols-2">
            <label class="block">
                <span class="field-label">Nombres</span>
                <span class="field-control flex items-center gap-2.5 px-3.5" :class="visibleError('firstName', 'first_name') ? 'field-control--error' : ''">
                    <AppIcon name="user" :size="18" class="shrink-0 text-brand-navy-mid" />
                    <input :value="state.traveler.firstName" type="text" autocomplete="given-name" placeholder="TUS NOMBRES" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase focus:outline-none" @input="onNameInput('firstName', 'first_name', $event)" @blur="touched.firstName = true" />
                </span>
                <p v-if="visibleError('firstName', 'first_name')" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ visibleError('firstName', 'first_name') }}</p>
            </label>

            <label class="block">
                <span class="field-label">Apellidos</span>
                <span class="field-control flex items-center gap-2.5 px-3.5" :class="visibleError('lastName', 'last_name') ? 'field-control--error' : ''">
                    <AppIcon name="user" :size="18" class="shrink-0 text-brand-navy-mid" />
                    <input :value="state.traveler.lastName" type="text" autocomplete="family-name" placeholder="TUS APELLIDOS" class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase focus:outline-none" @input="onNameInput('lastName', 'last_name', $event)" @blur="touched.lastName = true" />
                </span>
                <p v-if="visibleError('lastName', 'last_name')" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ visibleError('lastName', 'last_name') }}</p>
            </label>

            <div class="sm:col-span-2">
                <span class="field-label">Tipo de identificación</span>
                <div class="grid grid-cols-2 gap-2 rounded-2xl bg-brand-gray-light p-1.5" role="radiogroup" aria-label="Tipo de identificación">
                    <button
                        v-for="type in documentTypes"
                        :key="type.value"
                        type="button"
                        role="radio"
                        :aria-checked="state.traveler.documentType === type.value"
                        class="flex min-h-14 items-center gap-3 rounded-xl px-3 text-left transition-all"
                        :class="state.traveler.documentType === type.value ? 'bg-white text-brand-navy shadow-[0_4px_14px_rgba(22,36,61,0.09)]' : 'text-brand-gray hover:text-brand-navy'"
                        @click="selectDocumentType(type.value)"
                    >
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl" :class="state.traveler.documentType === type.value ? 'bg-brand-amber/20 text-brand-navy-mid' : 'bg-white/60'">
                            <AppIcon :name="type.icon" :size="18" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold">{{ type.label }}</span>
                            <span class="block text-[10px] text-brand-gray">{{ type.description }}</span>
                        </span>
                    </button>
                </div>
            </div>

            <label class="block">
                <span class="field-label">Número de {{ state.traveler.documentType === 'cedula' ? 'cédula' : 'pasaporte' }}</span>
                <span class="field-control flex items-center gap-2.5 px-3.5" :class="visibleError('documentId', 'document_id') ? 'field-control--error' : ''">
                    <AppIcon :name="state.traveler.documentType === 'cedula' ? 'id-card' : 'document'" :size="18" class="shrink-0 text-brand-navy-mid" />
                    <input
                        :value="state.traveler.documentId"
                        type="text"
                        :inputmode="state.traveler.documentType === 'cedula' ? 'numeric' : 'text'"
                        :maxlength="state.traveler.documentType === 'cedula' ? 10 : 20"
                        autocomplete="off"
                        :placeholder="state.traveler.documentType === 'cedula' ? '10 dígitos' : 'Ej. AB123456'"
                        class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm uppercase focus:outline-none"
                        @input="onDocumentInput"
                        @blur="touched.documentId = true"
                    />
                    <span v-if="state.traveler.documentId && !localErrors.documentId" class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-700"><AppIcon name="check" :size="12" :stroke-width="2.8" /></span>
                </span>
                <p v-if="visibleError('documentId', 'document_id')" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ visibleError('documentId', 'document_id') }}</p>
            </label>

            <div>
                <span class="field-label">Fecha de nacimiento</span>
                <DatePicker
                    :model-value="state.traveler.birthDate"
                    min="1900-01-01"
                    :max="adultCutoff"
                    :initial-view="adultCutoff"
                    placeholder="Selecciona tu fecha"
                    align="right"
                    :has-error="Boolean(visibleError('birthDate', 'birth_date'))"
                    @update:model-value="setBirthDate"
                    @blur="touched.birthDate = true"
                />
                <p v-if="visibleError('birthDate', 'birth_date')" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ visibleError('birthDate', 'birth_date') }}</p>
                <p v-else class="mt-1.5 text-xs text-brand-gray">Debes tener al menos 18 años.</p>
            </div>

            <div class="sm:col-span-2">
                <span class="field-label">Correo electrónico</span>
                <EmailInput
                    v-model:local-part="state.traveler.emailLocalPart"
                    v-model:provider="state.traveler.emailProvider"
                    v-model:custom-domain="state.traveler.emailCustomDomain"
                    :has-error="Boolean(visibleError('email', 'email'))"
                    @blur="touched.email = true"
                />
                <p v-if="visibleError('email', 'email')" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ visibleError('email', 'email') }}</p>
            </div>

            <div class="sm:col-span-2">
                <span class="field-label">Teléfono</span>
                <PhoneInput
                    v-model:country-code="state.traveler.phoneCountryCode"
                    v-model:number="state.traveler.phoneNumber"
                    :has-error="Boolean(state.fieldErrors?.phone_number)"
                    @blur="touched.phone = true"
                    @validation="handlePhoneValidation"
                />
                <p v-if="state.fieldErrors?.phone_number?.[0]" class="form-error" role="alert"><AppIcon name="info-circle" :size="14" /> {{ state.fieldErrors.phone_number[0] }}</p>
            </div>
        </div>

        <p v-if="state.serverError" class="mt-5 rounded-xl bg-brand-coral/10 px-4 py-3 text-sm text-brand-coral-text" role="alert">{{ state.serverError }}</p>

        <div class="mt-7 border-t border-slate-100 pt-5">
            <div class="mb-3 flex items-center justify-between gap-3 text-xs">
                <span class="text-brand-gray">{{ isValid ? 'Todo listo para calcular' : `Completa los datos (${completedFields}/6)` }}</span>
                <span v-if="phoneValidation.pending" class="flex items-center gap-1.5 text-brand-navy-mid"><span class="h-3 w-3 animate-spin rounded-full border-2 border-slate-200 border-t-brand-navy-mid" /> Validando</span>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" class="secondary-action px-3 sm:px-4" @click="emit('back')">
                    <AppIcon name="arrow-left" :size="18" /> Volver
                </button>
                <button type="button" class="primary-action flex-1 sm:flex-none" :disabled="!isValid" @click="continueToNext">
                    Ver mi cotización <AppIcon name="arrow-right" :size="18" />
                </button>
            </div>
        </div>
    </div>
</template>
