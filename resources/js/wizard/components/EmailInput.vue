<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import AppIcon from './AppIcon.vue';

const props = defineProps({
    localPart: { type: String, required: true },
    provider: { type: String, required: true },
    customDomain: { type: String, required: true },
    hasError: { type: Boolean, default: false },
});

const emit = defineEmits(['update:localPart', 'update:provider', 'update:customDomain', 'blur']);

const providers = [
    { value: 'gmail.com', label: 'GMAIL.COM' },
    { value: 'hotmail.com', label: 'HOTMAIL.COM' },
    { value: 'outlook.com', label: 'OUTLOOK.COM' },
    { value: 'yahoo.com', label: 'YAHOO.COM' },
    { value: 'icloud.com', label: 'ICLOUD.COM' },
    { value: 'otro', label: 'Dominio propio' },
];

const container = ref(null);
const isOpen = ref(false);

function selectProvider(provider) {
    emit('update:provider', provider.value);
    isOpen.value = false;
}

function handleClickOutside(event) {
    if (container.value && !container.value.contains(event.target)) {
        isOpen.value = false;
    }
}

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div ref="container" class="relative">
        <div class="field-control flex items-stretch" :class="hasError ? 'field-control--error' : ''">
            <span class="ml-2.5 flex items-center text-brand-navy-mid"><AppIcon name="mail" :size="18" /></span>
            <input
                type="text"
                inputmode="email"
                autocomplete="email"
                :value="props.localPart"
                placeholder="NOMBRE"
                class="min-w-0 flex-1 border-0 bg-transparent px-2 py-3 text-sm text-brand-navy uppercase focus:ring-0 focus:outline-none"
                @input="emit('update:localPart', $event.target.value.toLocaleUpperCase('es-EC').replace(/[@\s]/g, ''))"
                @blur="emit('blur')"
            />
            <span class="flex items-center text-sm text-brand-gray">@</span>
            <button
                type="button"
                class="flex max-w-[9.5rem] items-center gap-1.5 rounded-r-[0.85rem] px-2.5 text-sm font-medium text-brand-navy transition hover:bg-brand-gray-light sm:max-w-none sm:px-3"
                :aria-expanded="isOpen"
                @click="isOpen = !isOpen"
            >
                <span class="truncate uppercase">{{ props.provider === 'otro' ? 'Otro dominio' : props.provider }}</span>
                <AppIcon name="chevron-down" :size="14" class="shrink-0 text-slate-400 transition-transform" :class="isOpen ? 'rotate-180' : ''" />
            </button>
        </div>

        <Transition name="popover">
            <div v-if="isOpen" class="absolute right-0 z-30 mt-2 w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white p-2 shadow-[0_20px_48px_rgba(22,36,61,0.18)]">
                <button
                    v-for="option in providers"
                    :key="option.value"
                    type="button"
                    class="flex min-h-10 w-full items-center justify-between rounded-xl px-3 text-left text-sm transition"
                    :class="option.value === props.provider ? 'bg-brand-gray-light font-semibold text-brand-navy' : 'text-brand-gray hover:bg-slate-50 hover:text-brand-navy'"
                    @click="selectProvider(option)"
                >
                    {{ option.label }}
                    <AppIcon v-if="option.value === props.provider" name="check" :size="15" class="text-brand-navy-mid" />
                </button>
            </div>
        </Transition>

        <Transition name="step">
            <div v-if="props.provider === 'otro'" class="mt-2">
                <div class="field-control flex items-center px-3.5" :class="hasError ? 'field-control--error' : ''">
                    <span class="mr-2 text-sm font-medium text-brand-gray">@</span>
                    <input
                        type="text"
                        inputmode="url"
                        :value="props.customDomain"
                        placeholder="EMPRESA.COM"
                        class="min-w-0 flex-1 border-0 bg-transparent py-3 text-sm text-brand-navy uppercase focus:outline-none"
                        @input="emit('update:customDomain', $event.target.value.toLocaleUpperCase('es-EC').replace(/[@\s]/g, ''))"
                        @blur="emit('blur')"
                    />
                </div>
                <p class="mt-1.5 text-xs text-brand-gray">Escribe solo el dominio, sin www ni @.</p>
            </div>
        </Transition>
    </div>
</template>
