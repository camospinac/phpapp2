<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { AlertTriangle, CheckCircle, Users, Wallet } from 'lucide-vue-next';

// --- INTERFACES PARA TIPADO ---
interface Stats {
    totalUsers: number;
    activeSubscriptions: number;
    pendingSubscriptions: number;
    pendingWithdrawalsValue: number;
}

interface Activity {
    type: 'Suscripción' | 'Retiro';
    user_name: string;
    amount: number;
    status: string;
    date: string;
}

// --- PROPS ---
const props = defineProps<{
    stats: Stats;
    recentActivity: Activity[];
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin Dashboard', href: route('admin.dashboard') },
];

// --- HELPERS ---
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0,
    }).format(amount);
};
</script>

<template>
    <Head title="Admin Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Total Usuarios</h3>
                        <Users class="h-5 w-5 text-muted-foreground" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.totalUsers }}</p>
                </div>
                <div class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Suscripciones Activas</h3>
                        <CheckCircle class="h-5 w-5 text-green-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.activeSubscriptions }}</p>
                </div>
                <div class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Pendientes de Aprobación</h3>
                        <AlertTriangle class="h-5 w-5 text-yellow-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.pendingSubscriptions }}</p>
                </div>
                <div class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Dinero Pendiente por Retirar</h3>
                        <Wallet class="h-5 w-5 text-red-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ formatCurrency(stats.pendingWithdrawalsValue) }}</p>
                </div>
            </div>

            <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
                <h3 class="text-xl font-semibold mb-4">Actividad Reciente</h3>
                <div v-if="recentActivity.length === 0" class="text-center py-12 text-muted-foreground">
                    <p>No hay actividad reciente para mostrar.</p>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full text-sm text-left">
                        <thead class="border-b">
                            <tr>
                                <th class="px-4 py-3 font-medium">Tipo</th>
                                <th class="px-4 py-3 font-medium">Usuario</th>
                                <th class="px-4 py-3 font-medium text-right">Monto</th>
                                <th class="px-4 py-3 font-medium text-center">Estado</th>
                                <th class="px-4 py-3 font-medium text-right">Hace</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, index) in recentActivity" :key="index" class="border-b">
                                <td class="px-4 py-3">
                                    <span class="font-semibold" :class="{'text-blue-500': item.type === 'Suscripción', 'text-orange-500': item.type === 'Retiro'}">
                                        {{ item.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">{{ item.user_name }}</td>
                                <td class="px-4 py-3 font-mono text-right">{{ formatCurrency(item.amount) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                        'bg-green-100 text-green-800': item.status === 'active' || item.status === 'completed',
                                        'bg-yellow-100 text-yellow-800': item.status === 'pending_verification' || item.status === 'pending',
                                    }">
                                        {{ item.status.replace('_', ' ') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-muted-foreground">{{ item.date }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>