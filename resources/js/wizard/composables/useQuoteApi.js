import { ref } from 'vue';

const GENERIC_ERROR = 'Ocurrió un error inesperado. Intenta nuevamente.';
const CONNECTION_ERROR = 'No pudimos conectar con el servidor. Verifica tu conexión e intenta nuevamente.';

export function useQuoteApi() {
    const submitting = ref(false);

    async function createQuote(payload) {
        return postJson('/api/quotes', payload);
    }

    async function payQuote(reference, payload) {
        return postJson(`/api/quotes/${reference}/payment`, payload);
    }

    async function postJson(url, payload) {
        submitting.value = true;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const body = await response.json().catch(() => ({}));

            if (response.status === 422) {
                return { ok: false, status: response.status, fieldErrors: body.errors ?? {}, message: null };
            }

            if (!response.ok) {
                return { ok: false, status: response.status, fieldErrors: {}, message: body.message ?? GENERIC_ERROR };
            }

            return { ok: true, status: response.status, data: body.data };
        } catch {
            return { ok: false, status: 0, fieldErrors: {}, message: CONNECTION_ERROR };
        } finally {
            submitting.value = false;
        }
    }

    return { submitting, createQuote, payQuote };
}
