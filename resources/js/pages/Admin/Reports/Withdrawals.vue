<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Search, FilterX } from 'lucide-vue-next';

// --- INTERFACES & PROPS ---
interface User {
    nombres: string;
    apellidos: string;
    identification_number: string;
}
interface Withdrawal {
    id: number;
    code: string;
    amount: number;
    status: 'pending' | 'completed' | 'cancelled';
    created_at: string;
    updated_at: string;
    user: User;
}
interface Link {
    url: string | null;
    label: string;
    active: boolean;
}
const props = defineProps<{
    withdrawals: {
        data: Withdrawal[];
        links: Link[];
    };
    filters: {
        start_date: string;
        end_date: string;
        status: string;
        identification: string;
    };
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Reportes', href: '#' },
    { title: 'Retiros', href: route('admin.reports.withdrawals') },
];

// --- FILTERS ---
const filters = ref({
    start_date: props.filters.start_date ?? '',
    end_date: props.filters.end_date ?? '',
    status: props.filters.status ?? '',
    identification: props.filters.identification ?? '',
});

const applyFilters = () => {
    router.get(route('admin.reports.withdrawals'), filters.value, {
        preserveState: true,
        replace: true,
    });
};

// --- HELPERS ---
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0
    }).format(amount);
};
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};
</script>

<template>
    <Head title="Reporte de Retiros" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
            <h3 class="text-xl font-semibold mb-4">Reporte Detallado de Retiros</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 p-4 border rounded-lg items-end">
                   <div>
                    <Label for="identification">Cédula / ID</Label>
                    <Input id="identification" type="text" v-model="filters.identification" placeholder="Ej: 1070..." />
                </div>
                <div>
                    <Label for="start_date">Pagado Desde</Label>
                    <Input id="start_date" type="date" v-model="filters.start_date" />
                </div>
                <div>
                    <Label for="end_date">Pagado Hasta</Label>
                    <Input id="end_date" type="date" v-model="filters.end_date" />
                </div>
                <div>
                    <Label for="status">Estado</Label>
                    <select 
                        id="status" 
                        v-model="filters.status"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="completed">Completado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                </div>


                <div class="flex space-x-2">
    <Button @click="applyFilters" size="icon" title="Aplicar Filtros">
        <Search class="h-4 w-4" />
    </Button>
    
    <Button @click="router.visit(route('admin.reports.withdrawals'))" variant="outline" size="icon" title="Limpiar Filtros">
        <FilterX class="h-4 w-4" />
    </Button>

    <a :href="route('admin.reports.withdrawals.export', filters)" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2 w-full">
    Exportar a Excel
</a>
</div>


              
            </div>

            <div v-if="withdrawals.data.length === 0" class="flex items-center justify-center h-[40vh] text-muted-foreground">
                <p>No se encontraron retiros con los filtros seleccionados.</p>
            </div>
            
            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="border-b">
                        <tr>
                            <th class="px-4 py-3 font-medium">Código</th>
                            <th class="px-4 py-3 font-medium">Usuario</th>
                            <th class="px-4 py-3 font-medium"># Documento</th>
                            <th class="px-4 py-3 font-medium text-right">Monto</th>
                            <th class="px-4 py-3 font-medium text-center">Estado</th>
                            <th class="px-4 py-3 font-medium text-right">Fecha Solicitud</th>
                            <th class="px-4 py-3 font-medium text-right">Fecha Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="w in withdrawals.data" :key="w.id" class="border-b">
                            <td class="px-4 py-3 font-mono font-bold">{{ w.code }}</td>
                            <td class="px-4 py-3">{{ w.user.nombres }} {{ w.user.apellidos }}</td>
                            <td class="px-4 py-3">{{ w.user.identification_number }}</td>
                            <td class="px-4 py-3 font-mono text-right">{{ formatCurrency(w.amount) }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full capitalize" :class="{
                                    'bg-yellow-100 text-yellow-800': w.status === 'pending',
                                    'bg-green-100 text-green-800': w.status === 'completed',
                                    'bg-red-100 text-red-800': w.status === 'cancelled'
                                }">
                                    {{ w.status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-muted-foreground">{{ formatDate(w.created_at) }}</td>
                            <td class="px-4 py-3 text-right text-muted-foreground">
                                {{ w.status === 'completed' ? formatDate(w.updated_at) : 'N/A' }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div v-if="withdrawals.links.length > 3" class="flex justify-center mt-6">
                    <nav class="flex space-x-1">
                        <Link v-for="(link, index) in withdrawals.links" 
                           :key="index"
                           :href="link.url ?? '#'"
                           class="px-3 py-1 text-sm rounded-md transition-colors"
                           :class="{
                                'bg-primary text-primary-foreground hover:bg-primary/90': link.active,
                                'hover:bg-muted': !!link.url,
                                'text-muted-foreground cursor-not-allowed': !link.url
                           }"
                           v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>