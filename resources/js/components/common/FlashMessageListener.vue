<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { toast } from 'vue-sonner';

const page = usePage();

// Ambil dari props.flash yang sudah diekspos di HandleInertiaRequests.php
const flashSuccess = computed(() => (page.props.flash as any)?.success);
const flashError = computed(() => (page.props.flash as any)?.error);
const flashWarning = computed(() => (page.props.flash as any)?.warning);
const flashInfo = computed(() => (page.props.flash as any)?.info);

// Eksekusi toast setiap kali prop flash berubah
watch(flashSuccess, (msg) => {
    if (msg && typeof msg === 'string') {
        toast.success(msg);
    }
}, { immediate: true });

watch(flashError, (msg) => {
    if (msg && typeof msg === 'string') {
        toast.error(msg);
    }
}, { immediate: true });

watch(flashWarning, (msg) => {
    if (msg && typeof msg === 'string') {
        toast.warning(msg);
    }
}, { immediate: true });

watch(flashInfo, (msg) => {
    if (msg && typeof msg === 'string') {
        toast.info(msg);
    }
}, { immediate: true });

</script>

<template>
    <div style="display: none"></div>
</template>
