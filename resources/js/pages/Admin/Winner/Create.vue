<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import WinnerForm from './WinnerForm.vue';

defineProps<{
    users: any[];
}>();
</script>
<template>
    <Head title="Añadir Ganador" />
    <AppLayout>
        <div class="p-4 md:p-6">
            <h1 class="text-2xl font-bold mb-6">Añadir Nuevo Ganador</h1>
            <div class="max-w-2xl mx-auto p-6 rounded-xl border bg-card">
                <WinnerForm :users="users" />
            </div>
        </div>
    </AppLayout>
</template>