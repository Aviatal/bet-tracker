<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
    >
        <rect width="24" height="24" rx="6" fill="#000000" />

        <g
            fill="none"
            stroke="#10b981"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <path
                d="M12 18s5-2.5 5-6.25V7.5L12 5.625 7 7.5v4.25c0 3.75 5 6.25 5 6.25z"
            />
            <path d="M9.5 13v-1.5" stroke-width="2" />
            <path d="M12 13V10" stroke-width="2" />
            <path d="M14.5 13v-2.5" stroke-width="2" />
        </g>
    </svg>
</template>
