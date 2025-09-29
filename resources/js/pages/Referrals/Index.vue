<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

// --- INTERFACES PARA TIPADO ---
interface Plan {
    name: string;
}

interface Subscription {
    initial_investment: number;
    plan: Plan;
}

interface Referral {
    id: string;
    nombres: string;
    apellidos: string;
    created_at: string; // Fecha en que se registró el referido
    subscriptions: Subscription[]; // El controlador nos devuelve un array, aunque solo tenga 1 elemento
}

// --- PROPS ---
const props = defineProps<{
    referrals: Referral[];
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Mis Referidos', href: route('referrals.index') },
];

// --- HELPERS ---
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
</script>

<template>
    <Head title="Mis Referidos" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
            <h3 class="text-xl font-semibold mb-4">Mis Referidos</h3>
            
            <div v-if="referrals.length === 0" class="flex items-center justify-center h-[40vh] text-muted-foreground">
                <div class="text-center">
                    <p class="text-lg">Aún no tienes referidos.</p>
                    <p class="text-sm mt-2">¡Comparte tu código para empezar a ganar!</p>
                    </div>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="border-b">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Nombre del Referido</th>
                            <th scope="col" class="px-4 py-3 font-medium">Plan Elegido</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Monto Invertido</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Fecha de Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ref in referrals" :key="ref.id" class="border-b">
                            <td class="px-4 py-3 font-medium">{{ ref.nombres }} {{ ref.apellidos }}</td>
                            <td class="px-4 py-3 text-muted-foreground">
                                {{ ref.subscriptions.length > 0 ? ref.subscriptions[0].plan.name : 'Aún no ha invertido' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-right">
                                {{ ref.subscriptions.length > 0 ? formatCurrency(ref.subscriptions[0].initial_investment) : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-muted-foreground text-right">{{ formatDate(ref.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>