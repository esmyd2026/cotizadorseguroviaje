<script setup>
defineProps({
    name: { type: String, required: true },
    size: { type: Number, default: 20 },
    strokeWidth: { type: Number, default: 1.8 },
});
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        :stroke-width="strokeWidth"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <template v-if="name === 'plane'">
            <path d="M22 2 9.6 14.4" />
            <path d="m22 2-7.9 20-4.5-7.6L2 9.9 22 2Z" />
        </template>
        <template v-else-if="name === 'shield'">
            <path d="M12 22s8-3.8 8-10V5l-8-3-8 3v7c0 6.2 8 10 8 10Z" />
            <path d="m9 12 2 2 4-4" />
        </template>
        <template v-else-if="name === 'calendar'">
            <rect x="3" y="5" width="18" height="16" rx="2" />
            <path d="M16 3v4M8 3v4M3 10h18" />
        </template>
        <template v-else-if="name === 'user'">
            <circle cx="12" cy="8" r="4" />
            <path d="M4.5 21a7.5 7.5 0 0 1 15 0" />
        </template>
        <template v-else-if="name === 'quote'">
            <path d="M5 5h14v14H5z" />
            <path d="M8 9h8M8 13h5M15 16l2 2 4-4" />
        </template>
        <template v-else-if="name === 'check'">
            <path d="m5 12 4 4L19 6" />
        </template>
        <template v-else-if="name === 'check-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="m8 12 2.6 2.6L16 9" />
        </template>
        <template v-else-if="name === 'lock'">
            <rect x="5" y="10" width="14" height="11" rx="2" />
            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
        </template>
        <template v-else-if="name === 'arrow-right'">
            <path d="M5 12h14M14 6l6 6-6 6" />
        </template>
        <template v-else-if="name === 'arrow-left'">
            <path d="M19 12H5M10 6l-6 6 6 6" />
        </template>
        <template v-else-if="name === 'search'">
            <circle cx="11" cy="11" r="7" />
            <path d="m20 20-4-4" />
        </template>
        <template v-else-if="name === 'chevron-down'">
            <path d="m6 9 6 6 6-6" />
        </template>
        <template v-else-if="name === 'chevron-left'">
            <path d="m15 18-6-6 6-6" />
        </template>
        <template v-else-if="name === 'chevron-right'">
            <path d="m9 18 6-6-6-6" />
        </template>
        <template v-else-if="name === 'edit'">
            <path d="M12 20h9" />
            <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
        </template>
        <template v-else-if="name === 'download'">
            <path d="M12 3v12M7 10l5 5 5-5M5 21h14" />
        </template>
        <template v-else-if="name === 'map-pin'">
            <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" />
            <circle cx="12" cy="10" r="2.5" />
        </template>
        <template v-else-if="name === 'sparkle'">
            <path d="m12 3 1.2 3.8L17 8l-3.8 1.2L12 13l-1.2-3.8L7 8l3.8-1.2L12 3Z" />
            <path d="m19 15 .7 2.3L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.7L19 15Z" />
        </template>
        <template v-else-if="name === 'clock'">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        </template>
        <template v-else-if="name === 'document'">
            <path d="M6 2h8l4 4v16H6z" />
            <path d="M14 2v5h5M9 13h6M9 17h6" />
        </template>
        <template v-else-if="name === 'mail'">
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <path d="m3 7 9 6 9-6" />
        </template>
        <template v-else-if="name === 'id-card'">
            <rect x="3" y="5" width="18" height="14" rx="2" />
            <circle cx="8" cy="11" r="2" />
            <path d="M5.5 16a3 3 0 0 1 5 0M13 10h5M13 14h4" />
        </template>
        <template v-else-if="name === 'info-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="M12 11v5M12 8h.01" />
        </template>
        <template v-else-if="name === 'question-circle'">
            <circle cx="12" cy="12" r="9" />
            <path d="M9.8 9a2.4 2.4 0 1 1 3.6 2.1c-.9.5-1.4 1-1.4 2.2M12 17h.01" />
        </template>
        <template v-else-if="name === 'phone'">
            <path d="M6.6 3h3l1.5 4-2 1.6a16 16 0 0 0 6.3 6.3l1.6-2 4 1.5v3c0 2-1.6 3.6-3.6 3.5A15.4 15.4 0 0 1 3.1 6.6C3 4.6 4.6 3 6.6 3Z" />
        </template>
        <template v-else-if="name === 'cake'">
            <path d="M4 11h16v10H4zM7 8v3M12 8v3M17 8v3" />
            <path d="M7 5c1 1 1 2 0 3M12 5c1 1 1 2 0 3M17 5c1 1 1 2 0 3M4 15c2 2 4-2 6 0s4-2 6 0 4-1 4-1" />
        </template>
        <template v-else-if="name === 'home'">
            <path d="m3 11 9-8 9 8" />
            <path d="M5 10v11h14V10M9 21v-7h6v7" />
        </template>
        <template v-else-if="name === 'credit-card'">
            <rect x="2.5" y="5" width="19" height="14" rx="2.5" />
            <path d="M2.5 9.5h19M6 15h3" />
        </template>
        <template v-else-if="name === 'key'">
            <circle cx="8" cy="15" r="4" />
            <path d="m11 12 8-8M16 7l2 2M14 9l2 2" />
        </template>
        <template v-else-if="name === 'login'">
            <path d="M14 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-3" />
            <path d="M10 12h11M17 8l4 4-4 4" />
        </template>
        <template v-else-if="name === 'route'">
            <circle cx="6" cy="18" r="2" />
            <circle cx="18" cy="6" r="2" />
            <path d="M8 18h3a3 3 0 0 0 3-3V9a3 3 0 0 1 3-3" />
        </template>
        <template v-else-if="name === 'plus'">
            <path d="M12 5v14M5 12h14" />
        </template>
        <template v-else-if="name === 'x'">
            <path d="m6 6 12 12M18 6 6 18" />
        </template>
    </svg>
</template>
