<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import CampaignForm from './CampaignForm.vue';

defineProps<{ campaign: any }>();
</script>
<template>
    <Head title="Editar Campaña" />
    <AppLayout>
        <div class="p-4 md:p-6"><h1 class="text-2xl font-bold mb-6">Editar Campaña</h1><div class="max-w-2xl mx-auto p-6 rounded-xl border bg-card"><CampaignForm :campaign="campaign" /></div></div>
    </AppLayout>
</template>