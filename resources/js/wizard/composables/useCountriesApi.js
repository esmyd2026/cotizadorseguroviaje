import { ref } from 'vue';

let allCountriesCache = null;

export function useCountriesApi() {
    const results = ref([]);
    const loading = ref(false);
    const error = ref(null);

    async function search(query) {
        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(`/api/countries?search=${encodeURIComponent(query)}`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No pudimos cargar el listado de países. Intenta nuevamente.');
            }

            const payload = await response.json();
            results.value = payload.data;
        } catch {
            error.value = 'No pudimos cargar el listado de países. Intenta nuevamente.';
            results.value = [];
        } finally {
            loading.value = false;
        }
    }

    async function loadAll() {
        if (allCountriesCache) {
            return allCountriesCache;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await fetch('/api/countries', {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error('No pudimos cargar el listado de países. Intenta nuevamente.');
            }

            const payload = await response.json();
            allCountriesCache = payload.data;

            return allCountriesCache;
        } catch {
            error.value = 'No pudimos cargar el listado de países. Intenta nuevamente.';

            return [];
        } finally {
            loading.value = false;
        }
    }

    return { results, loading, error, search, loadAll };
}
