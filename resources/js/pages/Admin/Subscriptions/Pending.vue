<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'; // O tu layout de Admin si tienes uno
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';

// --- INTERFACES PARA TIPADO ---
interface User {
    id: string;
    nombres: string;
    apellidos: string;
    identification_number: string;
}

interface Plan {
    id: number;
    name: string;
}

interface Subscription {
    id: number;
    initial_investment: number;
    status: string;
    created_at: string;
    payment_receipt_path: string;
    contract_type: 'abierta' | 'cerrada';
    user: User;
    plan: Plan;
    profit_amount: number;
}

// --- PROPS ---
const props = defineProps<{
    subscriptions: Subscription[];
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' }, // Puedes poner la ruta a tu dashboard de admin
    { title: 'Suscripciones Pendientes', href: route('admin.subscriptions.pending') },
];

// --- FUNCIONES ---
const approveSubscription = (subscriptionId: number) => {
    // Añadimos una confirmación para evitar clics accidentales
    if (confirm('¿Estás seguro de que quieres aprobar esta suscripción? Esta acción es irreversible.')) {
        router.patch(route('admin.subscriptions.approve', { subscription: subscriptionId }), {}, {
            preserveScroll: true,
        });
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0, maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};
</script>

<template>
    <Head title="Aprobar Suscripciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
            <h3 class="text-xl font-semibold mb-4">Suscripciones Pendientes de Verificación</h3>
            
            <div v-if="subscriptions.length === 0" class="flex items-center justify-center h-[40vh] text-muted-foreground">
                <p class="text-center">¡Buen trabajo! No hay suscripciones pendientes por aprobar.</p>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="border-b">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Usuario</th>
                            <th class="px-4 py-3 font-medium"># Documento</th>
                            <th scope="col" class="px-4 py-3 font-medium">Plan</th>
                            <th scope="col" class="px-4 py-3 font-medium">Tipo Contrato</th> 
                            <th scope="col" class="px-4 py-3 font-medium text-right">Monto</th>
                            <th class="px-4 py-3 font-medium text-right">Ganancia Esperada</th>
                            <th scope="col" class="px-4 py-3 font-medium">Fecha Solicitud</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Comprobante</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in subscriptions" :key="sub.id" class="border-b">
                            <td class="px-4 py-3 font-medium">{{ sub.user.nombres }} {{ sub.user.apellidos }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sub.user.identification_number }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ sub.plan.name }}</td>
                            <td class="px-4 py-3 font-semibold capitalize"> {{ sub.contract_type }}</td>
                            <td class="px-4 py-3 font-mono text-right">{{ formatCurrency(sub.initial_investment) }}</td>
                            <td class="px-4 py-3 font-mono text-right text-green-600">+{{ formatCurrency(sub.profit_amount) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(sub.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <a :href="`/storage/${sub.payment_receipt_path}`" target="_blank" class="text-primary underline hover:text-primary/80">
                                    Ver Imagen
                                </a>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <Button @click="approveSubscription(sub.id)" size="sm">
                                    Aprobar
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>