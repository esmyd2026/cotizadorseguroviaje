import { reactive } from 'vue';

function initialState() {
    return {
        step: 'destination',
        tripType: 'direct',
        destination: null,
        destinations: [],
        departureDate: '',
        returnDate: '',
        traveler: {
            firstName: '',
            lastName: '',
            documentType: 'cedula',
            documentId: '',
            emailLocalPart: '',
            emailProvider: 'gmail.com',
            emailCustomDomain: '',
            phoneCountryCode: 'EC',
            phoneNumber: '',
            birthDate: '',
        },
        quote: null,
        fieldErrors: {},
        serverError: null,
    };
}

const state = reactive(initialState());

const MACRO_STEPS = {
    destination: 1,
    dates: 1,
    traveler: 2,
    calculating: 3,
    result: 3,
    confirmation: 4,
    payment: 5,
    success: 5,
};

export function useWizardState() {
    function goTo(step) {
        state.step = step;
    }

    function reset() {
        Object.assign(state, initialState());
    }

    function macroStep() {
        return MACRO_STEPS[state.step] ?? 1;
    }

    return { state, goTo, reset, macroStep };
}
