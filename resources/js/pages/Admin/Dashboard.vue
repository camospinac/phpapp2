<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { AlertTriangle, CheckCircle, Users, Wallet, Search, FilterX } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';
import { useToast } from "vue-toastification"; // <-- 1. Importa el hook correcto
import Echo from 'laravel-echo';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';

declare global {
    interface Window {
        Echo: Echo<any>;
    }
}

const toast = useToast(); // <-- 2. Inicializa el toast

// Preparamos el sonido
const notificationSound = new Audio('/sounds/notification.mp3');

onMounted(() => {
    window.Echo.private('admin-notifications')
        .listen('NewWithdrawalRequest', (e: { userName: string, amount: string }) => {

            console.log('¡Nueva notificación!', e);

            // 3. Mostramos el Toast (¡Así de fácil!)
            toast.info(`Retiro de $${e.amount} de ${e.userName}`, {
                timeout: 10000 // 10 segundos
            });

            // 4. Reproducimos el Sonido
            notificationSound.play().catch(error => {
                console.error("Error al reproducir sonido:", error);
            });
        })

        .listen('NewInvestmentPending', (e: { userName: string, amount: string }) => {
            console.log('¡Nueva notificación de Inversión!', e);
            // Usamos un toast de "success" (verde) para este
            toast.success(`Inversión de $${e.amount} de ${e.userName} (¡REVISA!)`);
            notificationSound.play().catch(e => console.error("Error con sonido"));
        });



});


// --- INTERFACES PARA TIPADO ---
interface Stats {
    realUsers: number;         // 👈 Cambiado
    testUsers: number;
    activeRealSubscriptions: number; // 👈 Añadir
    activeTestSubscriptions: number;
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
    stats: any;
    recentActivity: any;
    filters: any; // Estos son los filtros que vienen de la URL (Laravel)
}>();

// 3. VARIABLE LOCAL (Le cambiamos el nombre a filterForm para evitar conflictos)
const filterForm = ref({
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

// 4. FUNCIONES DE FILTRADO
const applyFilters = () => {
    console.log("Aplicando filtros:", filterForm.value); // Debug para ver si los datos están capturados
    router.get(route('admin.dashboard'), filterForm.value, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    });
};

const resetFilters = () => {
    filterForm.value.date_from = '';
    filterForm.value.date_to = '';
    router.visit(route('admin.dashboard'));
};

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
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">

                <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Usuarios Reales</h3>
                        <Users class="h-5 w-5 text-green-600" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.realUsers }}</p>
                </div>

                <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Planes Activos (Reales)</h3>
                        <CheckCircle class="h-5 w-5 text-green-500" />
                    </div>
                    <p class="mt-2 text-3xl font-bold">{{ stats.activeRealSubscriptions }}</p>
                </div>

                <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm border-dashed">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Usuarios Prueba</h3>
                        <Users class="h-5 w-5 text-orange-400" />
                    </div>
                    <p class="mt-2 text-3xl font-bold text-muted-foreground">{{ stats.testUsers }}</p>
                </div>


                <div class="p-6 rounded-xl border bg-card text-card-foreground shadow-sm border-dashed">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Planes Activos (Prueba)</h3>
                        <CheckCircle class="h-5 w-5 text-orange-400 opacity-70" />
                    </div>
                    <p class="mt-2 text-3xl font-bold text-muted-foreground">{{ stats.activeTestSubscriptions }}</p>
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
               
                <div v-if="recentActivity.total === 0" class="text-center py-12 text-muted-foreground">
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
                            <tr v-for="(item, index) in recentActivity.data" :key="index" class="border-b">
                                <td class="px-4 py-3">
                                    <span class="font-semibold" :class="{
                                        'text-blue-500': item.type === 'Suscripción',
                                        'text-orange-500': item.type === 'Retiro',
                                        'text-green-600': item.type === 'Registro'
                                    }">
                                        {{ item.type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">

                                    {{ item.user_name }}

                                </td>
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
                <div v-if="recentActivity.links.length > 3" class="mt-4 flex justify-center">
                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                        <Link v-for="(link, k) in recentActivity.links" :key="k" :href="link.url ?? ''"
                            v-html="link.label" class="px-4 py-2 text-sm border" :class="[
                                link.active ? 'bg-primary text-white' : 'bg-white text-gray-700',
                                !link.url ? 'opacity-50 pointer-events-none' : ''
                            ]" />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>