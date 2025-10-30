<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input'; 
import { Label } from '@/components/ui/label'; 
import { Button } from '@/components/ui/button'; 

// Helper de moneda
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
    }).format(amount);
};

const props = defineProps<{
    stats: {
        total_in_transfers: number;
        total_in_reinvestments: number;
        total_out_withdrawals: number;
        total_out_profits: number;
        net_flow: number;
    };
    projections: { month: string; total_due: string | number }[];
    filters: { start_date: string; end_date: string };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard Financiero', href: route('admin.dashboard') },
];

const dateFilters = ref({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date,
});

const applyFilters = () => {
    router.get(route('admin.reports.dashboard'), dateFilters.value, {
        preserveState: true,
        preserveScroll: true,
    });
};

</script>

<template>
    <Head title="Dashboard Financiero" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 space-y-6">
            
            <div class="p-4 rounded-lg border bg-card">
                <Card>
                <CardContent class="pt-6">
                    <form @submit.prevent="applyFilters" class="flex flex-col md:flex-row items-end gap-4">
                        <div class="grid gap-2 w-full">
                            <Label for="start_date">Fecha de Inicio</Label>
                            <Input id="start_date" type="date" v-model="dateFilters.start_date" />
                        </div>
                        <div class="grid gap-2 w-full">
                            <Label for="end_date">Fecha de Fin</Label>
                            <Input id="end_date" type="date" v-model="dateFilters.end_date" />
                        </div>
                        <Button type="submit" class="w-full md:w-auto">Filtrar</Button>
                    </form>
                </CardContent>
            </Card>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-green-600">Total Ingresos</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span>Transferencias:</span>
                            <strong>{{ formatCurrency(stats.total_in_transfers) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Reinversiones:</span>
                            <strong>{{ formatCurrency(stats.total_in_reinvestments) }}</strong>
                        </div>
                        <hr class="my-2" />
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span>{{ formatCurrency(Number(stats.total_in_transfers) + Number(stats.total_in_reinvestments)) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="text-red-600">Total Egresos</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div class="flex justify-between">
                            <span>Retiros:</span>
                            <strong>{{ formatCurrency(stats.total_out_withdrawals) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Pagos de Ganancias:</span>
                            <strong>{{ formatCurrency(stats.total_out_profits) }}</strong>
                        </div>
                         <hr class="my-2" />
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total:</span>
                            <span>{{ formatCurrency(Number(stats.total_out_withdrawals) + Number(stats.total_out_profits)) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <Card :class="stats.net_flow >= 0 ? 'bg-green-50' : 'bg-red-50'">
                    <CardHeader>
                        <CardTitle>Flujo Neto</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <strong class="text-3xl" :class="stats.net_flow >= 0 ? 'text-green-700' : 'text-red-700'">
                            {{ formatCurrency(stats.net_flow) }}
                        </strong>
                        <p class="text-sm text-muted-foreground">Ingresos Totales - Egresos Totales</p>
                    </CardContent>
                </Card>
            </div>

            <h2 class="text-xl font-semibold">Proyección de Pagos Futuros</h2>
            <Card>
                <CardContent class="pt-6">
                    <p class="text-muted-foreground">
                        Total de dinero que la plataforma debe pagar a los clientes (basado en cuotas pendientes).
                    </p>
                    <div class="space-y-4 mt-4">
                        <div v-for="proj in projections" :key="proj.month" class="flex justify-between items-center p-3 rounded-lg bg-muted">
                            <span class="font-medium">Mes: {{ proj.month }}</span>
                            <strong class="text-lg text-red-600">{{ formatCurrency(Number(proj.total_due)) }}</strong>
                        </div>
                        <div v-if="!projections.length" class="text-center py-4">
                            <p>No hay pagos futuros pendientes.</p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>