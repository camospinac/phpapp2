<script setup lang="ts">
import { ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { throttle } from 'lodash';

// --- INTERFACES PARA TIPADO ---
interface User {
    nombres: string;
    apellidos: string;
    identification_number: string;
}

interface Withdrawal {
    id: number;
    code: string;
    amount: number;
    status: string;
    created_at: string;
    user: User;
    payment_method: string;
    destination_phone_number: string;
}

interface Link {
    url: string | null;
    label: string;
    active: boolean;
}

// --- PROPS ---
const props = defineProps<{
    withdrawals: {
        data: Withdrawal[];
        links: Link[];
    };
    filters: {
        search: string;
    };
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: '#' },
    { title: 'Gestión de Retiros', href: route('admin.withdrawals.index') },
];

// --- ESTADO Y LÓGICA DE BÚSQUEDA ---
const search = ref(props.filters.search);

watch(search, throttle((newValue) => {
    router.get(route('admin.withdrawals.index'), { search: newValue }, {
        preserveState: true,
        replace: true,
    });
}, 300)); // Espera 300ms después de que el usuario deja de teclear

// --- ACCIONES ---
const completeWithdrawal = (withdrawal: Withdrawal) => {
    if (confirm(`¿Confirmas haber entregado ${formatCurrency(withdrawal.amount)} al usuario ${withdrawal.user.nombres}?`)) {
        router.patch(route('admin.withdrawals.complete', { withdrawal: withdrawal.id }), {}, {
            preserveScroll: true,
        });
    }
};

const rejectWithdrawal = (withdrawal: Withdrawal) => {
    if (confirm(`¿Estás seguro de RECHAZAR el retiro de ${formatCurrency(withdrawal.amount)}? El dinero será devuelto automáticamente al usuario.`)) {
        router.patch(route('admin.withdrawals.reject', { withdrawal: withdrawal.id }), {}, {
            preserveScroll: true,
        });
    }
};

// --- HELPERS ---
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString('es-CO', {
        dateStyle: 'short', timeStyle: 'short'
    });
};
</script>

<template>

    <Head title="Gestionar Retiros" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
                <h3 class="text-xl font-semibold">Solicitudes de Retiro Pendientes</h3>
                <div class="w-full md:w-1/3">
                    <Input v-model="search" type="search" placeholder="Buscar por código de 6 dígitos..." />
                </div>
            </div>

            <div v-if="withdrawals.data.length === 0"
                class="flex items-center justify-center h-[40vh] text-muted-foreground">
                <p class="text-center">No se encontraron solicitudes de retiro pendientes.</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="border-b">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-medium">Código</th>
                            <th scope="col" class="px-4 py-3 font-medium">Usuario</th>
                            <th class="px-4 py-3 font-medium"># Documento</th>
                            <th class="px-4 py-3 font-medium">Método Pago</th>
                            <th class="px-4 py-3 font-medium">Teléfono Destino</th>
                            <th scope="col" class="px-4 py-3 font-medium text-right">Monto</th>
                            <th scope="col" class="px-4 py-3 font-medium">Fecha Solicitud</th>
                            <th scope="col" class="px-4 py-3 font-medium text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="w in withdrawals.data" :key="w.id" class="border-b">
                            <td class="px-4 py-3 font-mono font-bold">{{ w.code }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ w.user.nombres }} {{ w.user.apellidos }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ w.user.identification_number }}</td>
                            <td class="px-4 py-3 font-semibold">{{ w.payment_method }}</td>
                            <td class="px-4 py-3 font-mono">{{ w.destination_phone_number }}</td>
                            <td class="px-4 py-3 font-mono text-right">{{ formatCurrency(w.amount) }}</td>
                            <td class="px-4 py-3 text-muted-foreground">{{ formatDate(w.created_at) }}</td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <Button @click="completeWithdrawal(w)" size="sm" variant="secondary"
                                        class="bg-blue-50 text-blue-700 hover:bg-blue-100 border-blue-200">
                                        Pagado
                                    </Button>

                                    <Button @click="rejectWithdrawal(w)" size="sm" variant="destructive">
                                        Rechazar
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="withdrawals.links.length > 3" class="flex justify-center mt-4">
                    <nav class="flex space-x-1">
                        <a v-for="(link, index) in withdrawals.links" :key="index" :href="link.url ?? undefined"
                            @click.prevent="link.url && router.visit(link.url, { preserveState: true })"
                            class="px-3 py-1 text-sm rounded-md" :class="{
                                'bg-primary text-primary-foreground': link.active,
                                'hover:bg-muted': link.url,
                                'text-muted-foreground cursor-not-allowed': !link.url
                            }" v-html="link.label" />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>