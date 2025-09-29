<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
</script>
<template>
    <Head title="Cuenta Bloqueada" />
    <div class="flex items-center justify-center min-h-screen bg-gray-100">
        <div class="p-8 max-w-md mx-auto bg-white rounded-xl shadow-md text-center">
            <h1 class="text-2xl font-bold text-red-600">Cuenta Bloqueada</h1>
            <p class="mt-4 text-gray-600">
                Tu cuenta ha sido bloqueada debido a la detección de anomalías.
            </p>
            <p class="mt-2 text-gray-600">
                Por favor, comunícate con un asesor al siguiente número para resolver la situación:
            </p>
            <p class="mt-4 text-xl font-semibold text-gray-900">
                +57 300 123 4567
            </p>
        </div>
    </div>
</template>