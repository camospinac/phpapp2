<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm, usePage, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import PlanSelector from '@/components/PlanSelector.vue';
import { type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { TrendingUp, Landmark, Gift, Gem, Download, Send } from 'lucide-vue-next';
import { onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Share2, Mail, MessageSquareText, Facebook  } from 'lucide-vue-next';



import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';


interface Recipient {
    id: string;
    nombres: string;
    apellidos: string;
}

interface PaymentMethod {
    id: number;
    name: string;
    account_details: string;
    logo_path: string | null;
}

const isTransferModalOpen = ref(false);
const transferStep = ref(1); // 1 = Buscar usuario, 2 = Ingresar monto
const recipientUser = ref<Recipient | null>(null);

const findUserForm = useForm({
    recipient_code: '',
});
const transferForm = useForm({
    recipient_code: '',
    amount: '',
    password: '',
});

const formattedTransferAmount = computed({
    // 'get' formatea el número para mostrarlo en el input
    get() {
        if (!transferForm.amount) return '';
        return Number(transferForm.amount).toLocaleString('es-CO');
    },
    // 'set' limpia el número cuando el usuario escribe
    set(newValue) {
        transferForm.amount = newValue.replace(/[^0-9]/g, '');
    }
});

// const getLogoUrl = (path: string | null) => {
//     if (!path) return 'https://placehold.co/100x100/gray/white?text=Logo';
//     // Asumimos que están en public/storage/ como lo configuramos
//     return `/storage/${path}`;
// }

// 4. Función COMPLETA para buscar al destinatario
const findRecipient = () => {
    findUserForm.clearErrors();
    findUserForm.processing = true; // Mostramos un estado de carga si quieres

    axios.post(route('users.find-by-code'), { referral_code: findUserForm.recipient_code })
        .then(response => {
            recipientUser.value = response.data;
            transferForm.recipient_code = findUserForm.recipient_code;
            transferStep.value = 2; // Avanzamos al siguiente paso
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                // Si el backend devuelve un error de validación
                findUserForm.setError('recipient_code', 'Código de referido no encontrado o inválido.');
            } else {
                // Para otros errores
                alert('Ocurrió un error inesperado. Por favor, inténtalo de nuevo.');
            }
        })
        .finally(() => {
            findUserForm.processing = false; // Terminamos el estado de carga
        });
};

// 5. Función COMPLETA para enviar la transferencia
const handleTransferSubmit = () => {
    transferForm.post(route('transfer.store'), {
        preserveScroll: true,
        onSuccess: () => {
            resetTransferModal(); // Llamamos a la función de reseteo al tener éxito
        },
    });
};

// 6. Función COMPLETA para cerrar y limpiar el modal
const resetTransferModal = () => {
    isTransferModalOpen.value = false;
    // Usamos un pequeño retraso para que el contenido no desaparezca antes de que se cierre el modal
    setTimeout(() => {
        transferStep.value = 1;
        recipientUser.value = null;
        findUserForm.reset();
        transferForm.reset();
    }, 300);
};


interface Plan {
    id: number;
    name: string;
    description: string;
    image_url: string | null;
    investment_type: 'abierta' | 'cerrada';
    calculation_type: string;
    fixed_percentage: number | null;
    closed_profit_percentage: number | null;
    closed_duration_days: number | null;
    percentages: number[] | null;
}

interface Withdrawal {
    id: number;
    code: string;
    amount: number;
    status: string;
    created_at: string;
}

const handleBackButton = (event: PopStateEvent) => {
    if (confirm('¿Estás seguro de que quieres salir? Perderás la sesión actual.')) {
        history.back();
    } else {
        history.pushState(null, '', location.href);
    }
};

onMounted(() => {
    if (props.subscriptions && props.subscriptions.length > 0) {
        activeTabSubscriptionId.value = props.subscriptions[0].id;
    }
    history.pushState(null, '', location.href);
    window.addEventListener('popstate', handleBackButton);

});

onUnmounted(() => {
    window.removeEventListener('popstate', handleBackButton);
});

interface Transaction {
    id: string;
    created_at: string;
    tipo: 'abono' | 'retiro';
    monto: number;
    observacion: string;
    type_detail: string;
}

interface Payment {
    id: number;
    amount: number;
    status: 'pending' | 'paid' | 'overdue' | 'reinvested' | 'accredited';
    payment_due_date: string;
}


interface Subscription {
    id: number;
    initial_investment: number;
    status: string;
    plan: Plan;
    payments: Payment[];
    contract_type: 'abierta' | 'cerrada';
    sequence_id: number;
    profit_amount: number;
    payment_receipt_path: string | null;
    created_at: string;
}

// --- PROPS ---
const props = withDefaults(defineProps<{
    subscriptions?: Subscription[];
    plans?: Plan[];
    totalInversion?: number;
    totalUtilidad?: number;
    totalGanancia?: number;
    totalAvailable?: number;
    transactions?: Transaction[];
    withdrawals?: Withdrawal[];
    paymentMethods?: PaymentMethod[];
}>(), {
    subscriptions: () => [],
    plans: () => [],
    transactions: () => [],
    totalInversion: 0,
    totalUtilidad: 0,
    totalGanancia: 0,
    totalAvailable: 0,
    withdrawals: () => [],
    paymentMethods: () => [],
});

const transferSubscriptions = computed(() =>
    props.subscriptions.filter(sub => sub.payment_receipt_path)
);

const balanceSubscriptions = computed(() =>
    props.subscriptions.filter(sub => !sub.payment_receipt_path)
);

const switchToClosed = (subscriptionId: number) => {
    if (confirm('¿Estás seguro de que quieres cambiar este plan a un Contrato Cerrado? Esta acción es irreversible.')) {
        router.post(route('subscriptions.switch', { subscription: subscriptionId }), {}, {
            preserveScroll: true,
        });
    }
};

const page = usePage();
const user = usePage().props.auth.user;

// const activeTabSubscriptionId = ref<number | null>(props.subscriptions[0]?.id ?? null);
const activeTabSubscriptionId = ref<number | null>(null);
const isInvestmentModalOpen = ref(false); // Renombrado para claridad
const isWithdrawalModalOpen = ref(false);
const generatedCode = ref<string | null>(null);


const withdrawalForm = useForm({
    amount: '',
    // Se asegura de que el valor inicial SÍ exista en la lista de props
    payment_method: props.paymentMethods.length > 0 ? props.paymentMethods[0].name : '',
    destination_phone_number: '',
});

const formattedWithdrawalAmount = computed({
    get() {
        if (!withdrawalForm.amount) return '';
        return Number(withdrawalForm.amount).toLocaleString('es-CO');
    },
    set(newValue) {
        withdrawalForm.amount = newValue.replace(/[^0-9]/g, '');
    }
});

const activeCampaign = page.props.active_campaign as any;
const isCampaignModalOpen = ref(false);

onMounted(() => {
    if (activeCampaign && !sessionStorage.getItem(`campaign_seen_${activeCampaign.id}`)) {
        isCampaignModalOpen.value = true;
        sessionStorage.setItem(`campaign_seen_${activeCampaign.id}`, 'true'); 4
    }
});

const handleWithdrawalSubmit = () => {
    if (confirm('¿Estás seguro de que deseas solicitar este retiro? Esta acción moverá los fondos de tu balance disponible.')) {
        withdrawalForm.post(route('withdrawals.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if (page.props.flash?.withdrawal_code) {
                    generatedCode.value = page.props.flash.withdrawal_code as string;
                }
            },
        });
    }
};

const closeWithdrawalModal = () => {
    isWithdrawalModalOpen.value = false;
    withdrawalForm.reset();
    generatedCode.value = null;
};

// const paymentMethods = [
//     { name: 'Nequi', value: 'NEQUI', logo: '/img/logos/nequi.jpg' },
//     { name: 'Daviplata', value: 'DAVIPLATA', logo: '/img/logos/daviplata.png' },
//     { name: 'Movi', value: 'MOVI', logo: '/img/logos/movi.jpg' },
//     { name: 'Zelle', value: 'ZELLE', logo: '/img/logos/zelle.png' },
// ];

const getLogoUrl = (path: string | null) => {
    if (!path) return 'https://placehold.co/100x100/gray/white?text=Logo';
    
    // 1. Si es una URL externa (empieza con http o https)
    if (path.startsWith('http')) {
        return path;
    }
    
    // 2. Si es una ruta local de la carpeta public/img
    if (path.startsWith('/img')) {
        return path;
    }
    
    // 3. Por defecto, asumimos que está en storage (archivos subidos)
    return `/storage/${path}`;
}

// Definimos los métodos fijos
const staticPaymentMethods = [
    { 
        id: 1, 
        name: 'Nequi', 
        logo_path: 'https://cdn.prod.website-files.com/6317a229ebf7723658463b4b/663a6b0d43303ddf38035997_logo-nequi.svg' 
    },
    { 
        id: 2, 
        name: 'Bre-B', 
        logo_path: 'https://images.ctfassets.net/zc86zymizgq6/1Ay66qQPaLcLMzkd6ji8wH/890bfe40dfa4a3af1ca2c5ed6e52ddfa/logobre-b.png' 
    },
];



const handleNewSubscription = (formData: ReturnType<typeof useForm>) => {
    formData.post(route('subscriptions.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isInvestmentModalOpen.value = false;
        },
    });
};

// --- PROPIEDADES COMPUTADAS ---
const activeSubscription = computed(() => {
    if (!activeTabSubscriptionId.value) return null;
    return props.subscriptions.find(sub => sub.id === activeTabSubscriptionId.value) ?? null;
});

// --- BREADCRUMBS Y HELPERS ---
const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inicio', href: route('dashboard') }];

const getDaysRemaining = (dueDateString: string) => {
    const today = new Date();
    const dueDate = new Date(dueDateString);
    today.setHours(0, 0, 0, 0);
    dueDate.setUTCHours(0, 0, 0, 0);
    const diffTime = dueDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    if (diffDays === 0) return { text: 'Vence hoy', class: 'text-yellow-500' };
    if (diffDays > 0) return { text: `Faltan ${diffDays} días`, class: 'text-green-500' };
    return { text: `Vencido hace ${Math.abs(diffDays)} días`, class: 'text-red-500' };
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0, maximumFractionDigits: 0,
    }).format(amount);
};

const copyToClipboard = () => {
    if (user?.referral_code) {
        navigator.clipboard.writeText(user.referral_code).then(() => {
            alert('¡Código copiado al portapapeles!');
        });
    }
};


// --- 3. AÑADE ESTE BLOQUE ENTERO ---

// 'window.location.origin' es tu dominio (ej: "https://tudominio.com")
const shareBaseUrl = window.location.origin;

// Crea el enlace de registro completo (ej: https://tudominio.com/register?ref=MI-CODIGO)

const registrationLink = computed(() => {
    if (!user || !user.referral_code) return '';
    return `${shareBaseUrl}/register?ref=${user.referral_code}`;
});

// Función para copiar el ENLACE (diferente a copiar SÓLO el código)
const copyShareLink = () => {
    navigator.clipboard.writeText(registrationLink.value);
    // Aquí deberías mostrar un "toast" o alerta de "¡Enlace copiado!"
    alert('¡Enlace de invitación copiado!');
};

// Genera los enlaces para las apps sociales
const text = encodeURIComponent('¡Te invito a unirte a ganar con EON!');
const whatsappUrl = computed(() => `https://api.whatsapp.com/send?text=${text} ${registrationLink.value}`);
const facebookUrl = computed(() => `https://www.facebook.com/sharer/sharer.php?u=${registrationLink.value}`);
const emailUrl = computed(() => `mailto:?subject=${text}&body=Regístrate usando mi enlace: ${registrationLink.value}`);

// --- FIN DEL BLOQUE A AÑADIR ---

</script>

<template>

    <Head title="Inicio" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-y-auto">
            <template v-if="subscriptions.length > 0">
                <div class="flex justify-end"> <a :href="route('dashboard.statement.download')" target="_blank">
                        <Button variant="outline" size="sm" class="flex items-center gap-2">
                            <Download class="h-4 w-4" />
                            Descargar Extracto
                        </Button>
                    </a>
                </div>

                <div v-if="user" class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div class="flex-1 space-y-4">
                            <div class="space-y-1">
                                <h2 class="text-2xl font-bold tracking-tight">¡Bienvenido, {{ user.nombres }} {{
                                    user.apellidos }}!</h2>
                                <div v-if="user.rank" class="flex items-center gap-2">
                                    <span class="text-lg">🥉</span>
                                    <span class="font-semibold text-primary text-base">{{ user.rank.name }}</span>
                                </div>
                                <p v-else class="text-base text-muted-foreground">Aún no tienes un rango. ¡Invita a tus
                                    amigos!</p>
                            </div>

                            <div v-if="user.referral_code" class="flex items-center gap-3 pt-2">
                                <p class="text-sm font-medium text-muted-foreground">Tu código para invitar:</p>

                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-muted border cursor-pointer hover:bg-secondary"
                                    @click="copyToClipboard" title="Copiar código">
                                    <span class="font-mono font-bold text-primary">{{ user.referral_code }}</span>
                                    <button>
                                        <Copy class="h-4 w-4 text-muted-foreground" />
                                    </button>
                                </div>

                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <button
                                            class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-primary text-primary-foreground hover:bg-primary/90"
                                            title="Compartir enlace de invitación">
                                            <Share2 class="h-4 w-4" />
                                            <span class="text-sm font-medium">Compartir</span>
                                        </button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent>
                                        <DropdownMenuItem as-child>
                                            <a :href="whatsappUrl" target="_blank" class="flex items-center gap-2">
                                                <MessageSquareText class="h-4 w-4" />
                                                <span>WhatsApp</span>
                                            </a>
                                        </DropdownMenuItem>

                                        <DropdownMenuItem as-child>
                                            <a :href="facebookUrl" target="_blank" class="flex items-center gap-2">
                                                <Facebook class="h-4 w-4" />
                                                <span>Facebook</span>
                                            </a>
                                        </DropdownMenuItem>

                                        <DropdownMenuItem as-child>
                                            <a :href="emailUrl" target="_blank" class="flex items-center gap-2">
                                                <Mail class="h-4 w-4" />
                                                <span>Email</span>
                                            </a>
                                        </DropdownMenuItem>

                                        <DropdownMenuItem @click="copyShareLink" class="flex items-center gap-2">
                                            <Send class="h-4 w-4" />
                                            <span>Copiar enlace</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>

                            </div>
                        </div>

                        <div v-if="user.next_rank" class="w-full md:w-1/3 space-y-2">
                            <div class="flex justify-between items-center">
                                <p class="text-base font-medium text-muted-foreground">Progreso a:</p>
                                <p class="text-base font-bold text-foreground">{{ user.next_rank.name }}</p>
                            </div>
                            <div class="w-full bg-muted rounded-full h-2.5">
                                <div class="bg-primary h-2.5 rounded-full"
                                    :style="{ width: (user.referral_count / user.next_rank.required_referrals) * 100 + '%' }">
                                </div>
                            </div>
                            <p class="text-xs text-muted-foreground text-right">
                                {{ user.referral_count }} / {{ user.next_rank.required_referrals }} referidos
                            </p>
                        </div>

                    </div>
                </div>

                <div v-if="subscriptions.length > 0" class="grid auto-rows-min gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        class="relative flex flex-col justify-center p-6  overflow-hidden rounded-xl border bg-card text-card-foreground">
                        <h3 class="text-lg font-medium text-muted-foreground">Inversión Total</h3>
                        <p class="mt-1 text-4xl font-semibold tracking-tight">{{ formatCurrency(totalInversion) }}</p>
                    </div>

                    <div
                        class="relative flex flex-col justify-center p-6  overflow-hidden rounded-xl border bg-card text-card-foreground">
                        <h3 class="text-lg font-medium text-muted-foreground">Utilidad Total</h3>
                        <p class="mt-1 text-4xl font-semibold tracking-tight text-green-500">+ {{
                            formatCurrency(totalUtilidad) }}</p>
                    </div>
                    <div
                        class="relative flex flex-col justify-center p-6  overflow-hidden rounded-xl border bg-card text-card-foreground">
                        <h3 class="text-lg font-medium text-muted-foreground">Ganancia Total</h3>
                        <p class="mt-1 text-4xl font-semibold tracking-tight text-blue-500">{{
                            formatCurrency(totalGanancia)
                        }}</p>
                    </div>
                </div>

                <!-- <div class="flex justify-center py-4 space-x-4">
                <Button @click="isInvestmentModalOpen = true" size="lg">Quiero Invertir</Button>
                <Button @click="isWithdrawalModalOpen = true" size="lg" variant="outline">Retirar Efectivo</Button>
                <Link :href="route('referrals.index')">
                <Button size="lg" variant="secondary">Mis Referidos</Button>
                </Link>
            </div> -->

                <div class="relative flex-1 rounded-xl border bg-card text-card-foreground p-6">

                    <div class="border-b mb-4">
                        <nav class="-mb-px flex space-x-6 overflow-x-auto">
                            <button v-for="sub in subscriptions" :key="sub.id" @click="activeTabSubscriptionId = sub.id"
                                :class="[
                                    'whitespace-nowrap py-3 px-1 border-b-2 font-medium text-base',
                                    activeTabSubscriptionId === sub.id
                                        ? 'border-primary text-primary'
                                        : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border'
                                ]">
                                <div class="flex flex-col text-center">
                                    <span>{{ sub.plan.name }} #{{ sub.sequence_id }}</span>
                                    <span class="text-xs capitalize text-muted-foreground/80">({{ sub.contract_type
                                    }})</span>
                                </div>
                            </button>
                        </nav>
                    </div>

                    <div v-if="activeSubscription" class="overflow-x-auto">
                        <div></div>
                        <div v-if="activeSubscription.contract_type === 'abierta' && new Date().getTime() - new Date(activeSubscription.created_at).getTime() < 172800000"
                            class="mb-4 flex justify-end">
                            <Button @click="switchToClosed(activeSubscription.id)">
                                Cambiar a Contrato Cerrado
                            </Button>
                        </div>
                        <p v-else-if="activeSubscription.contract_type === 'abierta'"
                            class="text-sm text-muted-foreground mb-4">
                            El periodo para cambiar a contrato cerrado ha expirado.
                        </p>
                        <div class="grid grid-cols-2 gap-4 mb-4 text-center">
                            <div class="p-4 bg-muted rounded-lg">
                                <h4 class="text-sm font-medium text-muted-foreground">Inversión en este Plan</h4>
                                <p class="mt-1 text-xl md:text-2xl font-bold">{{
                                    formatCurrency(activeSubscription.initial_investment) }}</p>
                            </div>
                            <div class="p-4 bg-muted rounded-lg">
                                <h4 class="text-sm font-medium text-muted-foreground">Ganancia Esperada</h4>
                                <p class="mt-1 text-xl md:text-2xl font-bold text-green-600">+{{
                                    formatCurrency(activeSubscription.profit_amount) }}</p>
                            </div>
                        </div>

                        <table class="min-w-full text-sm text-left">
                            <thead class="border-b">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-medium"># Pago</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Tipo Contrato</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-right">Monto</th>
                                    <th scope="col" class="px-4 py-3 font-medium text-center">Estado</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Fecha de Pago</th>
                                    <th scope="col" class="px-4 py-3 font-medium">Tiempo Restante</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(payment, index) in activeSubscription.payments" :key="payment.id"
                                    class="border-b">

                                    <td class="px-4 py-3 font-medium text-muted-foreground">{{ index + 1 }}</td>
                                    <td class="px-4 py-3 capitalize font-medium">{{ activeSubscription.contract_type }}
                                    </td>
                                    <td class="px-4 py-3 font-mono text-right">{{ formatCurrency(payment.amount) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="{
                                            'bg-yellow-100 text-yellow-800': payment.status === 'pending',
                                            'bg-green-100 text-green-800': payment.status === 'paid',
                                            'bg-blue-100 text-blue-800': payment.status === 'accredited',
                                            'bg-purple-100 text-purple-800': payment.status === 'reinvested',
                                        }">
                                            {{ payment.status.charAt(0).toUpperCase() + payment.status.slice(1) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-muted-foreground">{{ payment.payment_due_date }}</td>
                                    <td class="px-4 py-3 font-semibold"
                                        :class="getDaysRemaining(payment.payment_due_date).class">
                                        {{ getDaysRemaining(payment.payment_due_date).text }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="flex items-center justify-center h-[40vh] text-muted-foreground">
                        <p v-if="subscriptions.length > 0">Selecciona un plan para ver los detalles.</p>
                        <p v-else>Aún no tienes un plan de pagos activo.</p>
                    </div>

                </div>

                <details class="group border rounded-lg overflow-hidden bg-card">
                    <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-muted">
                        <h3 class="text-lg font-semibold">Ver Historial Detallado</h3>
                        <span
                            class="transition-transform duration-300 group-open:rotate-180 text-muted-foreground">▼</span>
                    </summary>

                    <div class="p-4 border-t text-sm space-y-3">

                        <details class="group border rounded-lg overflow-hidden">
                            <summary
                                class="flex items-center justify-between p-3 cursor-pointer bg-muted/50 hover:bg-muted">
                                <h4 class="font-semibold">Inversiones</h4>
                                <span
                                    class="transition-transform duration-300 group-open:rotate-180 text-muted-foreground text-xs">▼</span>
                            </summary>
                            <div class="p-3 border-t">
                                <p v-if="transferSubscriptions.length === 0"
                                    class="text-muted-foreground text-center py-4">
                                    No tienes inversiones de este tipo.</p>
                                <table v-else class="min-w-full text-sm text-left">
                                    <thead class="border-b">
                                        <tr class="text-muted-foreground">
                                            <th scope="col" class="px-4 py-3 font-medium">#</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Plan</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Monto</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="sub in transferSubscriptions" :key="sub.id">
                                            <td class="py-2 font-mono">#{{ sub.sequence_id }}</td>
                                            <td class="py-2">{{ sub.plan.name }}</td>
                                            <td class="py-2 font-mono">{{ formatCurrency(sub.initial_investment)
                                            }}</td>
                                            <td class="py-2">{{ new Date(sub.created_at).toLocaleString('es-CO')
                                            }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary
                                class="flex items-center justify-between p-3 cursor-pointer bg-muted/50 hover:bg-muted">
                                <h4 class="font-semibold">Re-inversiones</h4>
                                <span
                                    class="transition-transform duration-300 group-open:rotate-180 text-muted-foreground text-xs">▼</span>
                            </summary>
                            <div class="p-3 border-t">
                                <p v-if="balanceSubscriptions.length === 0"
                                    class="text-muted-foreground text-center py-4">
                                    No tienes re-inversiones de este tipo.</p>
                                <table v-else class="min-w-full text-sm text-left">
                                    <thead class="border-b">
                                        <tr class="text-muted-foreground">
                                            <th scope="col" class="px-4 py-3 font-medium">#</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Plan</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Monto</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="sub in balanceSubscriptions" :key="sub.id">
                                            <td class="py-2 font-mono">#{{ sub.sequence_id }}</td>
                                            <td class="py-2">{{ sub.plan.name }}</td>
                                            <td class="py-2 font-mono">{{ formatCurrency(sub.initial_investment) }}</td>
                                            <td class="py-2">{{ new Date(sub.created_at).toLocaleString('es-CO') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary
                                class="flex items-center justify-between p-3 cursor-pointer bg-muted/50 hover:bg-muted">
                                <h4 class="font-semibold">Solicitudes de Retiro</h4>
                                <span
                                    class="transition-transform duration-300 group-open:rotate-180 text-muted-foreground text-xs">▼</span>
                            </summary>
                            <div class="p-3 border-t">
                                <p v-if="withdrawals.length === 0" class="text-muted-foreground text-center py-4">No has
                                    solicitado retiros.</p>
                                <table v-else class="min-w-full text-sm text-left">
                                    <thead class="border-b">
                                        <tr class="text-muted-foreground">
                                            <th scope="col" class="px-4 py-3 font-medium">Código</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Monto</th>
                                            <th class="py-2 text-center">Estado</th>
                                            <th scope="col" class="px-4 py-3 font-medium">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="w in withdrawals" :key="w.id">
                                            <td class="py-2 font-mono">{{ w.code }}</td>
                                            <td class="py-2 font-mono">{{ formatCurrency(w.amount) }}</td>
                                            <td class="py-2 capitalize">{{ w.status }}</td>
                                            <td class="py-2">{{ new Date(w.created_at).toLocaleString('es-CO') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </details>

                        <details class="group border rounded-lg overflow-hidden">
                            <summary
                                class="flex items-center justify-between p-3 cursor-pointer bg-muted/50 hover:bg-muted">
                                <h4 class="font-semibold">Historial de Transacciones</h4>
                                <span
                                    class="transition-transform duration-300 group-open:rotate-180 text-muted-foreground">▼</span>
                            </summary>

                            <div class="p-4 border-t text-sm">
                                <div v-if="transactions.length === 0"
                                    class="flex items-center justify-center py-12 text-muted-foreground">
                                    <p>No tienes movimientos en tu historial.</p>
                                </div>

                                <div v-else class="overflow-x-auto">
                                    <table class="w-full">
                                        <thead class="border-b">
                                            <tr class="text-muted-foreground">
                                                <th class="py-2 text-left font-medium">Fecha</th>
                                                <th class="py-2 text-left font-medium">Tipo</th>
                                                <th class="py-2 text-left font-medium">Monto</th>
                                                <th class="py-2 text-left font-medium">Observación</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="tx in transactions" :key="tx.id"
                                                class="border-b last:border-none">
                                                <td class="py-2 text-muted-foreground whitespace-nowrap">{{ new
                                                    Date(tx.created_at).toLocaleString('es-CO') }}</td>
                                                <td class="py-2">
                                                    <div class="flex flex-col">
                                                        <span class="font-semibold"
                                                            :class="tx.tipo === 'abono' ? 'text-green-500' : 'text-red-500'">
                                                            {{ tx.tipo.charAt(0).toUpperCase() + tx.tipo.slice(1) }}
                                                        </span>
                                                        <span class="text-xs text-muted-foreground">
                                                            ({{ tx.type_detail }})
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="py-2 text-left font-mono"
                                                    :class="tx.tipo === 'abono' ? 'text-green-500' : 'text-red-500'">
                                                    {{ tx.tipo === 'abono' ? '+' : '-' }} {{ formatCurrency(tx.monto) }}
                                                </td>
                                                <td class="py-2 text-muted-foreground">{{ tx.observacion }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </details>
                    </div>
                </details>


                <div v-if="subscriptions.length > 0" class="grid md:grid-cols-3 gap-6 items-center">
                    <div
                        class="md:col-span-1 p-6 rounded-xl border bg-card text-card-foreground h-full flex flex-col justify-center">
                        <h3 class="text-lg font-medium text-muted-foreground">Saldo Disponible</h3>
                        <p class="mt-1 text-4xl font-semibold tracking-tight text-teal-500">{{
                            formatCurrency(totalAvailable) }}</p>
                    </div>
                    <div class="md:col-span-2 flex flex-col items-center gap-4">

                        <div class="w-full flex flex-col md:flex-row justify-center items-center gap-4">
                            <Button @click="isInvestmentModalOpen = true"
                                class="w-full md:w-auto text-lg h-12 px-6 animate-spotlight">
                                <TrendingUp class="mr-3 h-6 w-6" />
                                Nueva Inversión
                            </Button>
                            <Button @click="isWithdrawalModalOpen = true" variant="outline"
                                class="w-full md:w-auto text-lg h-12 px-6">
                                <Landmark class="mr-3 h-6 w-6" />
                                Retirar Saldo
                            </Button>

                            <Button @click="isTransferModalOpen = true" variant="outline"
                                class="w-full md:w-auto text-lg h-12 px-6">
                                <Send class="mr-3 h-6 w-6" />
                                Enviar Dinero
                            </Button>
                        </div>

                        <div class="w-full flex flex-col md:flex-row justify-center items-center gap-4">
                            <Link :href="route('referrals.index')" class="w-full md:w-auto">
                            <Button variant="secondary" class="w-full text-lg h-12 px-6">
                                <Gift class="mr-3 h-6 w-6" />
                                Mis Referidos
                            </Button>
                            </Link>
                            <Link :href="route('winners.index')" class="w-full md:w-auto">
                            <Button variant="secondary" class="w-full text-lg h-12 px-6">
                                <Gem class="mr-3 h-6 w-6" />
                                Últimos Ganadores
                            </Button>
                            </Link>
                        </div>

                    </div>
                </div>
            </template>
            <template v-else>
                <div v-if="user" class="p-6 rounded-xl border bg-card text-card-foreground">

                    <div class="flex flex-col md:flex-row items-center justify-between gap-6 mb-6">

                        <div class="space-y-1 text-center md:text-left">
                            <h2 class="text-2xl font-bold tracking-tight">¡Bienvenido a bordo, {{ user.nombres }} {{
                                user.apellidos }}!</h2>
                            <p class="text-base text-muted-foreground">Estás a un paso de poner tu dinero a trabajar.
                            </p>
                        </div>

                        <div class="w-full md:w-auto flex-shrink-0">
                            <Button @click="isInvestmentModalOpen = true" size="lg"
                                class="w-full text-lg h-12 px-6 animate-spotlight">
                                <TrendingUp class="mr-3 h-6 w-6" />
                                Realizar mi primera inversión
                            </Button>
                        </div>

                    </div>

                    <hr class="border-border my-6">

                    <div class="flex flex-col md:flex-row items-center gap-3">

                        <div v-if="user.referral_code" class="flex items-center gap-3">
                            <p class="text-sm font-medium text-muted-foreground">Tu código usuario:</p>
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-muted border cursor-pointer hover:bg-secondary"
                                @click="copyToClipboard" title="Copiar código">
                                <span class="font-mono font-bold text-primary">{{ user.referral_code }}</span>
                                <button>
                                    <Copy class="h-4 w-4 text-muted-foreground" />
                                </button>
                            </div>
                        </div>

                        <div class="p-4 rounded-lg bg-muted text-center border w-full">
                            <p class="font-semibold">🏆 ¡Mira lo que nuestros usuarios más fieles han ganado!</p>
                            <Link :href="route('winners.index')" class="mt-2 inline-block">
                            <Button size="sm" variant="outline">Ver Ganadores</Button>
                            </Link>
                        </div>

                    </div>

                </div>
                <div class="p-6 rounded-xl border bg-card text-card-foreground">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold tracking-tight">¿Qué contrato te conviene?</h2>
                        <p class="text-muted-foreground mt-2">Compara nuestras dos modalidades de contrato y elige la
                            que mejor se alinee con tus metas financieras.</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                        <div class="space-y-4 p-4 border rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">🕊️</span>
                                <h3 class="text-xl font-bold">Contrato Abierto</h3>
                            </div>
                            <p class="text-sm font-semibold text-primary">Mayor control, liquidez y libertad.</p>
                            <p class="text-muted-foreground text-sm">
                                Ideal para quienes desean un flujo de caja constante y la flexibilidad para reaccionar a
                                oportunidades o imprevistos.
                            </p>
                            <div class="p-3 bg-muted/50 rounded-md border text-sm">
                                <p><strong>Perfil del Inversor:</strong></p>
                                <p class="text-muted-foreground">
                                    ☕ Doña Rosa, 52 años, emprendedora. Invierte en un Contrato Abierto para recibir
                                    ganancias cada 15 días, dándole liquidez para reinvertir en su negocio y
                                    mejorar la calidad de vida de su familia.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4 p-4 border rounded-lg">
                            <div class="flex items-center gap-3">
                                <span class="text-3xl">🔒</span>
                                <h3 class="text-xl font-bold">Contrato Cerrado</h3>
                            </div>
                            <p class="text-sm font-semibold text-primary">Mayor rendimiento, concentración y potencia a
                                largo plazo.</p>
                            <p class="text-muted-foreground text-sm">
                                Diseñado para quienes buscan maximizar sus resultados sin la necesidad de retiros a
                                corto plazo, potenciando el crecimiento de su capital.
                            </p>
                            <div class="p-3 bg-muted/50 rounded-md border text-sm">
                                <p><strong>Perfil del Inversor:</strong></p>
                                <p class="text-muted-foreground">
                                    📱 Julián, 38 años, emprendedor tecnológico. Elige un Contrato Cerrado para
                                    hacer crecer su capital de forma acelerada durante 90 días, con el objetivo de usar
                                    las ganancias para expandir su negocio.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold text-center mb-4">Tabla Comparativa</h3>
                        <div class="overflow-x-auto rounded-lg border">
                            <table class="w-full text-sm">
                                <thead class="bg-muted/50">
                                    <tr class="text-left">
                                        <th class="p-3 font-medium">Característica</th>
                                        <th class="p-3 font-medium">Contrato Abierto</th>
                                        <th class="p-3 font-medium">Contrato Cerrado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-t">
                                        <td class="p-3 font-semibold">Rentabilidad</td>
                                        <td class="p-3">Hasta 15% mensual</td>
                                        <td class="p-3 font-bold">Hasta 50% trimestral</td>
                                    </tr>
                                    <tr class="border-t">
                                        <td class="p-3 font-semibold">Liquidación de Pagos</td>
                                        <td class="p-3">Periódica (6 cuotas)</td>
                                        <td class="p-3 font-bold">Pago único a 90 días</td>
                                    </tr>
                                    <tr class="border-t">
                                        <td class="p-3 font-semibold">Ideal Para</td>
                                        <td class="p-3">Generar flujo de caja y tener flexibilidad.</td>
                                        <td class="p-3 font-bold">Maximizar el crecimiento del capital a largo plazo.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>
        </div>


        <Dialog :open="isInvestmentModalOpen" @update:open="isInvestmentModalOpen = false">
            <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Crear Nueva Inversión</DialogTitle>
                    <DialogDescription>
                        Selecciona un plan y define cómo quieres realizar el pago.
                    </DialogDescription>
                </DialogHeader>
                <PlanSelector :plans="plans" :total-available="totalAvailable" :paymentMethods="paymentMethods"
                    @submit="handleNewSubscription" />
            </DialogContent>
        </Dialog>

        <Dialog :open="isWithdrawalModalOpen" @update:open="closeWithdrawalModal">
            <DialogContent class="sm:max-w-[800px] max-h-[90vh] overflow-y-auto">
                <div v-if="!generatedCode">
                    <DialogHeader>
                        <DialogTitle>Solicitar Retiro de Efectivo</DialogTitle>
                        <DialogDescription>
                            Elige el método de pago e ingresa los datos para tu retiro.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="handleWithdrawalSubmit" class="grid gap-6 py-4">
                        <div class="grid gap-2">
                            <Label>Enviar a</Label>
                            <div class="grid grid-cols-2 gap-3">
    <label v-for="method in staticPaymentMethods" :key="method.id"
        class="flex flex-col items-center justify-center p-4 border rounded-lg cursor-pointer transition-all"
        :class="{ 'ring-2 ring-primary border-primary': withdrawalForm.payment_method === method.name }">
        
        <input type="radio" v-model="withdrawalForm.payment_method" :value="method.name"
            class="sr-only" />
        
        <img :src="getLogoUrl(method.logo_path)" :alt="method.name"
            class="h-16 w-full object-contain mb-2">
            
        <span class="text-lg font-semibold">{{ method.name }}</span>
    </label>
</div>
                            <InputError :message="withdrawalForm.errors.payment_method" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="withdrawal-phone">Número de Teléfono de Destino</Label>
                            <Input id="withdrawal-phone" type="tel" v-model="withdrawalForm.destination_phone_number"
                                required placeholder="Ej: 3001234567" />
                            <InputError :message="withdrawalForm.errors.destination_phone_number" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="withdrawal-amount">Monto a Retirar</Label>
                            <Input id="withdrawal-amount" type="text" inputmode="numeric"
                                v-model="formattedWithdrawalAmount" required placeholder="Ej: 50.000" />
                            <p class="text-lg text-muted-foreground">
                                Disponible: {{ formatCurrency(totalAvailable) }}
                            </p>
                            <InputError :message="withdrawalForm.errors.amount" />
                        </div>

                        <Button type="submit" :disabled="withdrawalForm.processing">
                            Generar Código de Retiro
                        </Button>
                    </form>
                </div>
                <div v-else class="text-center py-4">
                    <DialogHeader>
                        <DialogTitle class="text-2xl">¡Código Generado con Éxito!</DialogTitle>
                    </DialogHeader>
                    <div class="my-6">
                        <p class="text-sm text-muted-foreground">Tu código único de retiro es:</p>
                        <p class="text-5xl font-bold tracking-widest bg-muted rounded-lg py-3 my-2">
                            {{ generatedCode }}
                        </p>
                    </div>
                    <p class="text-sm text-muted-foreground">
                        En un tiempo estimado de 2 horas, recibirás el pago en el número de teléfono y cuenta
                        proporcionada.
                    </p>
                    <Button @click="closeWithdrawalModal" class="mt-6 w-full">
                        Finalizar
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <Dialog v-if="activeCampaign" :open="isCampaignModalOpen" @update:open="isCampaignModalOpen = false">
            <DialogContent class="sm:max-w-md p-0">
                <img v-if="activeCampaign.image_url" :src="`/storage/${activeCampaign.image_url}`" alt="Campaña"
                    class="w-full rounded-t-lg">
                <DialogHeader class="p-6">
                    <DialogTitle class="text-2xl">{{ activeCampaign.title }}</DialogTitle>
                    <DialogDescription class="pt-2 text-base text-muted-foreground">
                        {{ activeCampaign.content }}
                    </DialogDescription>
                </DialogHeader>
            </DialogContent>
        </Dialog>

        <Dialog :open="isTransferModalOpen" @update:open="resetTransferModal">
            <DialogContent class="sm:max-w-md">
                <div v-if="transferStep === 1">
                    <DialogHeader>
                        <DialogTitle>Enviar Dinero</DialogTitle>
                        <DialogDescription>Ingresa el código de referido del usuario al que deseas enviar saldo.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="findRecipient" class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="recipient_code">Código de Referido</Label>
                            <Input id="recipient_code" v-model="findUserForm.recipient_code" required />
                            <InputError :message="findUserForm.errors.recipient_code" />
                        </div>
                        <Button type="submit" :disabled="findUserForm.processing">Buscar Usuario</Button>
                    </form>
                </div>

                <div v-else-if="transferStep === 2 && recipientUser">
                    <DialogHeader>
                        <DialogTitle>Confirmar Transferencia</DialogTitle>
                        <DialogDescription>
                            Vas a enviar saldo a <strong class="text-primary">{{ recipientUser.nombres }} {{
                                recipientUser.apellidos }}</strong>.
                        </DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="handleTransferSubmit" class="grid gap-4 py-4">
                        <div class="grid gap-2">
                            <Label for="transfer-amount">Monto a Enviar</Label>
                            <Input id="transfer-amount" type="text" inputmode="numeric"
                                v-model="formattedTransferAmount" required />
                            <InputError :message="transferForm.errors.amount" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="transfer-password">Confirma tu Contraseña</Label>
                            <Input id="transfer-password" type="password" v-model="transferForm.password" required />
                            <InputError :message="transferForm.errors.password" />
                        </div>
                        <div class="flex justify-between">
                            <Button type="button" variant="ghost" @click="transferStep = 1">Atrás</Button>
                            <Button type="submit" :disabled="transferForm.processing">Confirmar Envío</Button>
                        </div>
                    </form>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>