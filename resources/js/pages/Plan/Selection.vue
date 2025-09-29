<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthBase from '@/layouts/AuthLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PlusCircle, MinusCircle } from 'lucide-vue-next';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';

const currentStep = ref(1);

const transferMethods = ref([
    { name: 'Nequi', logo: '/img/logos/nequi.jpg', phone: '300 123 4567' },
    { name: 'Daviplata', logo: '/img/logos/daviplata.png', phone: '310 987 6543' },
    { name: 'Moovii', logo: '/img/logos/movi.jpg', phone: '320 555 1234' },
]);

const selectedTransferMethod = ref(transferMethods.value[0]);

// --- INTERFACES Y PROPS ---
interface Plan {
    id: number;
    name: string;
    description: string;
    image_url: string | null;
    calculation_type: string;
    fixed_percentage: number | null;
    closed_profit_percentage: number | null;
    closed_duration_days: number | null;
}

const props = defineProps<{ plans: Plan[] }>();

const formatCurrency = (amount: number | string) => {
    const number = Number(amount);
    if (isNaN(number)) return '';
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0
    }).format(number);
};

const formattedAmount = computed(() => {
    if (form.amount === '') return '';
    return formatCurrency(form.amount);
});

// --- ESTADO ---
const isModalOpen = ref(false);
const selectedPlanForModal = ref<Plan | null>(null);

// --- FORMULARIO ---
const form = useForm({
    plan_id: null as number | null,
    amount: '',
    receipt: null as File | null,
    investment_contract_type: 'abierta',
});

// --- LÓGICA DEL MODAL ---
const openPlanModal = (plan: Plan) => {
    selectedPlanForModal.value = plan;
    form.plan_id = plan.id;
    isModalOpen.value = true;
};
const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    selectedPlanForModal.value = null;
};

const calculatedPayments = computed(() => {
    if (!form.amount || !form.plan_id) return [];
    const amount = parseFloat(form.amount);
    if (isNaN(amount) || amount <= 0) return [];
    const selectedPlan = props.plans.find(p => p.id === form.plan_id);
    if (!selectedPlan) return [];

    const payments = [];

    // Lógica para Contrato CERRADO (esta ya estaba bien)
    if (form.investment_contract_type === 'cerrada') {
        const percentage = selectedPlan.closed_profit_percentage ?? 50;
        const duration = selectedPlan.closed_duration_days ?? 90;

        const baseProfit = amount * (percentage / 100);
        const totalProfit = baseProfit * 3;
        const totalPayment = amount + totalProfit;

        let finalDate = new Date();
        finalDate.setDate(finalDate.getDate() + duration);

        payments.push({
            label: `Pago Único a ${duration} días`,
            value: totalPayment,
            date: finalDate.toISOString().split('T')[0]
        });

    }
    // Lógica para Contrato ABIERTO (esta es la que corregimos)
    else {
        // 1. La fecha de inicio es hoy + 15 días calendario.
        let dueDate = new Date();
        dueDate.setDate(dueDate.getDate() + 15);

        if (selectedPlan.calculation_type === 'fixed_plus_final' && selectedPlan.fixed_percentage) {
            const fixedPayment = amount * (selectedPlan.fixed_percentage / 100);

            for (let i = 1; i <= 5; i++) {
                payments.push({
                    label: `Pago ${i}`,
                    value: fixedPayment,
                    date: dueDate.toISOString().split('T')[0]
                });
                // 2. Simplemente sumamos 15 días calendario para el siguiente pago.
                dueDate.setDate(dueDate.getDate() + 15);
            }

            const finalPayment = amount + fixedPayment;
            payments.push({
                label: `Pago Final`,
                value: finalPayment,
                date: dueDate.toISOString().split('T')[0]
            });

        } else if (selectedPlan.calculation_type === 'equal_installments' && selectedPlan.fixed_percentage) {
            const fixedPayment = amount * (selectedPlan.fixed_percentage / 100);
            const totalProfit = fixedPayment * 6;
            const totalToPay = amount + totalProfit;
            const installment = totalToPay / 6;

            for (let i = 1; i <= 6; i++) {
                payments.push({
                    label: `Pago ${i} de 6`,
                    value: installment,
                    date: dueDate.toISOString().split('T')[0]
                });
                // 2. Simplemente sumamos 15 días calendario.
                dueDate.setDate(dueDate.getDate() + 15);
            }
        }
    }

    return payments.map(p => ({
        ...p,
        formattedValue: new Intl.NumberFormat('es-CO', {
            style: 'currency', currency: 'COP', minimumFractionDigits: 0
        }).format(p.value)
    }));
});

// --- FUNCIÓN DE ENVÍO Y WATCH ---
const submit = () => { form.post(route('plan.store'), { onSuccess: () => closeModal() }); };

const increaseAmount = () => {
    const min = 200000;
    const max = 10000000; // 1. Nuevo máximo

    let currentValue = parseFloat(form.amount) || 0;
    let step;

    // 2. Lógica de incremento dinámico
    if (currentValue < 2000000) {
        step = 100000; // Incrementa de 100k
    } else {
        step = 1000000; // Incrementa de 1M
    }

    if (currentValue < min) {
        currentValue = min;
    } else if (currentValue < max) {
        currentValue += step;
    }

    // Asegurarse de no pasarse del máximo
    if (currentValue > max) {
        currentValue = max;
    }

    form.amount = String(currentValue);
};

const decreaseAmount = () => {
    const min = 200000;

    let currentValue = parseFloat(form.amount);
    let step;

    // 3. Lógica de decremento dinámico
    if (currentValue <= 2000000) {
        step = 100000; // Decrementa de 100k
    } else {
        step = 1000000; // Decrementa de 1M
    }

    if (currentValue > min) {
        currentValue -= step;
    }

    // Asegurarse de no bajar del mínimo
    if (currentValue < min) {
        currentValue = min;
    }

    form.amount = String(currentValue);
};
</script>

<template>

    <Head title="Seleccionar Plan" />
    <AppLayout>
        <AuthBase title="Selecciona tu Plan" description="Elige uno de nuestros planes para empezar."
            :show-logo="false">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div v-for="plan in plans" :key="plan.id" @click="openPlanModal(plan)"
                    class="rounded-xl border bg-card text-card-foreground shadow transition-all duration-200 cursor-pointer hover:ring-2 hover:ring-primary">
                    <img :src="plan.image_url ?? 'https://placehold.co/600x400/gray/white?text=Plan'"
                        alt="Imagen del Plan" class="aspect-video w-full rounded-t-xl object-cover">
                    <div class="p-6">
                        <h3 class="text-xl font-semibold">{{ plan.name }}</h3>
                        <p class="mt-2 text-base text-muted-foreground">{{ plan.description }}</p>
                    </div>
                </div>
            </div>
            <Dialog :open="isModalOpen" @update:open="closeModal">
                <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle class="text-2xl">{{ selectedPlanForModal?.name }}</DialogTitle>
                        <DialogDescription class="text-base">{{ selectedPlanForModal?.description }}</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submit" class="flex flex-col gap-6 pt-4">
                        <div v-if="currentStep === 1" class="grid gap-6">
                            <div class="relative">
                                <div class="relative flex justify-center text-xs uppercase">
                                    <Label class="text-lg">¿Qué contrato te conviene?</Label>
                                </div>
                            </div>

                            <details class="group border rounded-lg overflow-hidden">
                                <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-muted">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">🕊️</span>
                                        <h4 class="font-semibold">Contrato Abierto</h4>
                                    </div>
                                    <span class="transition-transform duration-300 group-open:rotate-180">▼</span>
                                </summary>
                                <div class="p-4 border-t text-base text-muted-foreground space-y-3">
                                    <p><strong>Mayor control, liquidez y libertad.</strong></p>
                                    <p>Ideal para quienes desean controlar de cerca sus ingresos y mantener flexibilidad
                                        a
                                        lo largo del tiempo.</p>
                                    <div class="p-3 bg-muted/50 rounded-md border">
                                        <p><strong>Ejemplo de aplicación:</strong><br>
                                            ☕ Doña Rosa, 52 años, vendedora de café. Con esfuerzo salió adelante y hoy
                                            invierte en un Contrato Abierto que le da liquidez y ganancias cada 15 días.
                                            Así
                                            vive más holgada y con nuevas oportunidades para su negocio y su familia.
                                        </p>
                                    </div>
                                </div>
                            </details>

                            <details class="group border rounded-lg overflow-hidden">
                                <summary class="flex items-center justify-between p-4 cursor-pointer hover:bg-muted">
                                    <div class="flex items-center gap-3">
                                        <span class="text-xl">🔒</span>
                                        <h4 class="font-semibold">Contrato Cerrado</h4>
                                    </div>
                                    <span class="transition-transform duration-300 group-open:rotate-180">▼</span>
                                </summary>
                                <div class="p-4 border-t text-base text-muted-foreground space-y-3">
                                    <p><strong>Mayor rendimiento, concentración y a largo plazo.</strong></p>
                                    <p>Ideal para quienes buscan maximizar resultados sin necesidad de retiros
                                        mensuales.
                                    </p>
                                    <div class="p-3 bg-muted/50 rounded-md border">
                                        <p><strong>Ejemplo de aplicación:</strong><br>
                                            📱 Julián, 38 años, emprendedor. Empezó arreglando celulares con lo justo,
                                            pero
                                            nunca dejó de soñar. Hoy invierte en un contrato cerrado de 3 meses que le
                                            da
                                            rentabilidad y el impulso para cumplir metas y seguir creciendo.</p>
                                    </div>
                                </div>
                            </details>
                            <input type="hidden" name="investment_contract_type" :value="form.investment_contract_type">
                            <div class="grid gap-2">
                                <Label class="text-lg">Tipo de Contrato</Label>
                                <div class="flex items-center space-x-4 rounded-md border p-2 bg-background">
                                    <label
                                        class="flex items-center space-x-2 cursor-pointer p-2 rounded-md flex-1 justify-center transition-all duration-200"
                                        :class="{
                                            'bg-muted': form.investment_contract_type === 'abierta',
                                            'opacity-60': form.investment_contract_type !== 'abierta'
                                        }">
                                        <input type="radio" v-model="form.investment_contract_type" value="abierta"
                                            class="sr-only" />
                                        <span>Abierto</span>
                                    </label>

                                    <label
                                        class="flex items-center space-x-2 cursor-pointer p-2 rounded-md flex-1 justify-center transition-all duration-200"
                                        :class="{
                                            'bg-muted': form.investment_contract_type === 'cerrada',
                                            'opacity-60': form.investment_contract_type !== 'cerrada'
                                        }">
                                        <input type="radio" v-model="form.investment_contract_type" value="cerrada"
                                            class="sr-only" />
                                        <span>Cerrado</span>
                                    </label>
                                </div>
                            </div>
                            <div class="grid gap-2">
                                <Label for="amount" class="text-center text-lg">Inversión</Label>

                                <div class="flex items-center justify-center gap-2">
                                    <Button type="button" @click="decreaseAmount" variant="outline" size="icon"
                                        class="h-14 w-14 rounded-full [touch-action:manipulation]"
                                        :disabled="parseFloat(form.amount) <= 200000">
                                        <MinusCircle class="h-8 w-8" />
                                    </Button>

                                    <Input id="amount" type="text" :value="formattedAmount" required
                                        placeholder="Inversión" readonly
                                        class="h-16 w-48 text-center font-bold text-2xl" />

                                    <Button type="button" @click="increaseAmount" variant="outline" size="icon"
                                        class="h-14 w-14 rounded-full [touch-action:manipulation]"
                                        :disabled="parseFloat(form.amount) >= 10000000">
                                        <PlusCircle class="h-8 w-8" />
                                    </Button>
                                </div>

                                <InputError :message="form.errors.amount" />
                            </div>

                            <div v-if="calculatedPayments.length > 0" class="grid gap-2">
                                <Label class="text-base">Vista Previa de Pagos</Label>
                                <div class="rounded-md border">
                                    <table class="w-full text-base">
                                        <thead class="border-b">
                                            <tr class="text-muted-foreground">
                                                <th class="p-2 text-left font-medium">Descripción</th>
                                                <th class="p-2 text-right font-medium">Monto</th>
                                                <th class="p-2 text-right font-medium">Fecha Pago</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(payment, index) in calculatedPayments" :key="index"
                                                class="border-b last:border-none">
                                                <td class="p-2 text-left font-bold">{{ payment.label }}</td>
                                                <td class="p-2 text-right font-mono">{{ payment.formattedValue }}</td>
                                                <td class="p-2 text-right font-mono text-muted-foreground">{{
                                                    payment.date
                                                    }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <Button type="button" @click="currentStep = 2" class="w-full" :disabled="!form.amount">
                                Siguiente
                            </Button>
                        </div>

                        <div v-else-if="currentStep === 2" class="grid gap-6 animate-in fade-in-50">
                            <div class="grid gap-2">
                                <Label>Elige dónde realizar el pago</Label>
                                <div class="grid grid-cols-3 gap-3">
                                    <div v-for="method in transferMethods" :key="method.name"
                                        @click="selectedTransferMethod = method"
                                        class="flex flex-col items-center justify-center p-3 border rounded-lg cursor-pointer transition-all"
                                        :class="{ 'ring-2 ring-primary border-primary': selectedTransferMethod.name === method.name }">
                                        <img :src="method.logo" :alt="method.name"
                                            class="h-12 w-12 object-contain mb-2">
                                        <span class="text-xs font-medium">{{ method.name }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-4 rounded-lg bg-muted text-center">
                                <p class="text-sm text-muted-foreground">Realiza la transferencia al siguiente número de
                                    {{ selectedTransferMethod.name }}:</p>
                                <p class="text-2xl font-mono font-bold my-2">{{ selectedTransferMethod.phone }}</p>
                            </div>

                            <!-- <div class="p-4 rounded-lg bg-muted text-center">
                                <p class="text-sm text-muted-foreground">Por favor, realiza la transferencia al
                                    siguiente número:</p>
                                <p class="text-2xl font-mono font-bold my-2">300 123 4567</p>
                                <p class="text-xs text-muted-foreground">(Nequi / Daviplata / Transfiya)</p>
                            </div> -->

                            <div class="grid gap-2">
                                <Label for="receipt" class="text-base">Adjunta tu comprobante de pago</Label>
                                <Input id="receipt" type="file"
                                    @input="form.receipt = ($event.target as HTMLInputElement).files?.[0] ?? null"
                                    accept="image/*" required />
                                <InputError :message="form.errors.receipt" />
                            </div>

                            <div class="flex items-center gap-4">
                                <Button type="button" variant="outline" @click="currentStep = 1"
                                    class="w-1/3">Atrás</Button>
                                <Button type="submit" class="w-2/3" :disabled="form.processing || !form.receipt">
                                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                                    Confirmar y Generar
                                </Button>
                            </div>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </AuthBase>
    </AppLayout>
</template>