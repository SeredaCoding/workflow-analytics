<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    staticSrc: {
        type: String,
        default: null,
    },
});

const isDarkMode = ref(window.matchMedia('(prefers-color-scheme: dark)').matches);

let mediaQuery;

onMounted(() => {
    mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
    mediaQuery.addEventListener('change', (e) => {
        isDarkMode.value = e.matches;
    });
});

onUnmounted(() => {
    if (mediaQuery) {
        mediaQuery.removeEventListener('change', () => {});
    }
});

const currentLogoSrc = computed(() => {
    if (props.staticSrc) {
        return props.staticSrc;
    }
    return isDarkMode.value ? '/images/logo.png' : '/images/logo_dark.png';
});
</script>

<template>
    <img :src="currentLogoSrc" alt="WorkFlow Analytics" />
</template>
