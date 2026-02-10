<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Referral, type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Ban } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// --- INTERFACES & PROPS ---
interface Plan { name: string; }
interface Payment { id: number; amount: number; status: string; payment_due_date: string; }
interface Transaction { id: string; created_at: string; tipo: 'abono' | 'retiro'; monto: number; observacion: string; }
interface Rank { name: string; }
interface Subscription {
    id: number;
    sequence_id: number;
    initial_investment: number;
    profit_amount: number;
    status: string;
    contract_type: 'abierta' | 'cerrada';
    created_at: string;
    plan: Plan;
}

const page = usePage();
// Asumiendo que tu objeto de usuario tiene un campo 'rol'
const isAdmin = computed(() => page.props.auth.user?.rol === 'admin');

interface User {
    id: string;
    nombres: string;
    apellidos: string;
    email: string;
    celular: string;
    identification_type: string;
    identification_number: string;
    referral_code: string;
    is_fraud: boolean;
    rank: Rank | null;
    subscriptions: Subscription[];
    transactions: Transaction[];
    referrals?: Referral[];
}
interface Stats {
    totalInversion: number;
    totalProfit: number;
    totalGanancia: number;
    totalAvailable: number;
}
const props = defineProps<{
    user: User;
    stats: Stats;
}>();

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Usuarios', href: route('admin.users.index') },
    { title: props.user.nombres, href: '#' },
];

const toggleFraudStatus = () => {
    if (confirm(`¿Estás seguro de que quieres cambiar el estado de fraude para este usuario?`)) {
        router.patch(route('admin.users.update', { user: props.user.id }), {
            is_fraud: !props.user.is_fraud
        }, {
            preserveScroll: true,
        });
    }
};

// --- BREADCRUMBS Y HELPERS (Asegúrate de que tus helpers estén completos) ---
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0,
    }).format(amount);
};
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
};
</script>

<template>

    <Head :title="`Perfil de ${user.nombres}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <div class="p-6 rounded-xl border bg-card text-card-foreground">
                <div class="flex flex-col md:flex-row gap-6">
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold">{{ user.nombres }} {{ user.apellidos }}</h2>
                        <p class="text-muted-foreground">{{ user.email }}</p>
                        <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                            <div><strong>Celular:</strong> {{ user.celular }}</div>
                            <div><strong>Documento:</strong> {{ user.identification_type }} - {{
                                user.identification_number }}</div>
                            <div><strong>Código Referido:</strong> <span class="font-mono">{{ user.referral_code
                                    }}</span></div>
                            <div><strong>Rango:</strong> {{ user.rank?.name || 'Sin Rango' }}</div>
                        </div>
                    </div>

                    <div class="text-center flex items-center gap-4">
                        <span class="px-3 py-1 text-sm font-semibold rounded-full"
                            :class="user.is_fraud ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                            {{ user.is_fraud ? 'Marcado como Fraude' : 'Cuenta Normal' }}
                        </span>

                        <Button v-if="isAdmin" @click="toggleFraudStatus" variant="destructive" size="sm"
                            class="flex items-center gap-2">
                            <Ban class="h-4 w-4" />
                            {{ user.is_fraud ? 'Desbloquear Usuario' : 'Bloquear Usuario' }}
                        </Button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="p-4 rounded-xl border bg-card">
                    <h4 class="text-sm font-medium text-muted-foreground">Inversión Total</h4>
                    <p class="text-2xl font-bold">{{ formatCurrency(stats.totalInversion) }}</p>
                </div>
                <div class="p-4 rounded-xl border bg-card">
                    <h4 class="text-sm font-medium text-muted-foreground">Ganancia Total</h4>
                    <p class="text-2xl font-bold text-green-600">+{{ formatCurrency(stats.totalProfit) }}</p>
                </div>
                <div class="p-4 rounded-xl border bg-card">
                    <h4 class="text-sm font-medium text-muted-foreground">Retorno Total</h4>
                    <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(stats.totalGanancia) }}</p>
                </div>
                <div class="p-4 rounded-xl border bg-card">
                    <h4 class="text-sm font-medium text-muted-foreground">Saldo Disponible</h4>
                    <p class="text-2xl font-bold text-teal-600">{{ formatCurrency(stats.totalAvailable) }}</p>
                </div>
            </div>

            <div class="p-6 rounded-xl border bg-card text-card-foreground">
                <h3 class="text-lg font-semibold mb-4">Suscripciones del Usuario</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="p-3 text-left">#</th>
                                <th class="p-3 text-left">Plan</th>
                                <th class="p-3 text-left">Tipo</th>
                                <th class="p-3 text-right">Inversión</th>
                                <th class="p-3 text-right">Ganancia</th>
                                <th class="p-3 text-center">Estado</th>
                                <th class="p-3 text-right">Fecha Creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sub in user.subscriptions" :key="sub.id" class="border-b">
                                <td class="p-3 font-mono">#{{ sub.sequence_id }}</td>
                                <td class="p-3 font-medium">{{ sub.plan.name }}</td>
                                <td class="p-3 capitalize">{{ sub.contract_type }}</td>
                                <td class="p-3 font-mono text-right">{{ formatCurrency(sub.initial_investment) }}</td>
                                <td class="p-3 font-mono text-right text-green-600">+{{
                                    formatCurrency(sub.profit_amount) }}</td>
                                <td class="p-3 text-center capitalize">{{ sub.status.replace('_', ' ') }}</td>
                                <td class="p-3 text-right">{{ formatDate(sub.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="p-6 rounded-xl border bg-card text-card-foreground">
                <h3 class="text-lg font-semibold mb-4">Historial de Transacciones</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="border-b">
                            <tr>
                                <th class="p-3 text-left">Fecha</th>
                                <th class="p-3 text-left">Tipo</th>
                                <th class="p-3 text-left">Observación</th>
                                <th class="p-3 text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="tx in user.transactions" :key="tx.id" class="border-b">
                                <td class="p-3 whitespace-nowrap">{{ formatDate(tx.created_at) }}</td>
                                <td class="p-3 capitalize font-semibold"
                                    :class="tx.tipo === 'abono' ? 'text-green-600' : 'text-red-600'">{{ tx.tipo }}</td>
                                <td class="p-3 text-muted-foreground">{{ tx.observacion }}</td>
                                <td class="p-3 font-mono text-right"
                                    :class="tx.tipo === 'abono' ? 'text-green-600' : 'text-red-600'">{{ tx.tipo ===
                                        'abono' ? '+' : '-' }}{{ formatCurrency(tx.monto) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="p-6 rounded-xl border bg-card text-card-foreground">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold">Usuarios Referidos</h3>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                    {{ user.referrals?.length || 0 }} Personas
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b">
                        <tr class="text-muted-foreground">
                            <th class="p-3 text-left font-medium">Nombre Completo</th>
                            <th class="p-3 text-left font-medium">Plan Elegido</th>
                            <th class="p-3 text-right font-medium">Monto Invertido</th>
                            <th class="p-3 text-right font-medium">Fecha Registro</th>
                            <th class="p-3 text-right font-medium">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="ref in user.referrals" :key="ref.id"
                            class="border-b last:border-0 hover:bg-muted/50 transition-colors">
                            <td class="p-3 font-medium">
                                {{ ref.nombres }} {{ ref.apellidos }}
                                <p class="text-[10px] text-muted-foreground font-normal">{{ ref.email }}</p>
                            </td>

                            <td class="p-3 text-muted-foreground">
                                <span v-if="ref.subscriptions.length > 0"
                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                    {{ ref.subscriptions[0].plan.name }}
                                </span>
                                <span v-else class="text-xs italic text-muted-foreground/60">
                                    Sin inversión
                                </span>
                            </td>

                            <td class="p-3 font-mono text-right">
                                {{ ref.subscriptions.length > 0 ?
                                    formatCurrency(ref.subscriptions[0].initial_investment) : '$ 0' }}
                            </td>

                            <td class="p-3 text-right text-muted-foreground text-xs">
                                {{ formatDate(ref.created_at) }}
                            </td>

                            <td class="p-3 text-right">
                                <Link :href="route('admin.users.show', { user: ref.id })">
                                    <Button variant="outline" size="sm" class="h-8">
                                        Perfil
                                    </Button>
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="!user.referrals || user.referrals.length === 0">
                            <td colspan="5" class="p-8 text-center text-muted-foreground italic">
                                Este usuario aún no tiene referidos.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>