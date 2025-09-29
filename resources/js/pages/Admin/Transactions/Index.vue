<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type Transaction, type User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';

// Props que llegan del TransactionController
const props = defineProps<{
    transactions: Transaction[];
    users: User[]; // Lista de usuarios para el formulario
}>();

// --- Lógica del Modal ---
const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: '',
    id_user: '',
    tipo: 'abono',
    monto: 0,
    observacion: '',
});

const openCreateModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (transaction: Transaction) => {
    isEditMode.value = true;
    form.id = transaction.id;
    form.id_user = transaction.id_user;
    form.tipo = transaction.tipo;
    form.monto = transaction.monto;
    form.observacion = transaction.observacion;
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitForm = () => {
    if (isEditMode.value) {
        form.patch(route('admin.transactions.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.transactions.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteTransaction = (transaction: Transaction) => {
    if (confirm(`¿Estás seguro de eliminar esta transacción?`)) {
        useForm({}).delete(route('admin.transactions.destroy', transaction.id), {
            preserveScroll: true,
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Transacciones', href: route('admin.transactions.index') },
];

const formatDate = (dateString: string) => {
    const options: Intl.DateTimeFormatOptions = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('es-CO', options);
};

</script>

<template>
    <Head title="Gestión de Transacciones" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-foreground">Transacciones</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Una lista de todas las transacciones registradas en el sistema.
                    </p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Button @click="openCreateModal">Añadir Transacción</Button>
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-foreground sm:pl-0">Usuario</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">Tipo</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">Monto</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">Fecha</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0"><span class="sr-only">Edit</span></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="tx in props.transactions" :key="tx.id">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-foreground sm:pl-0">{{ tx.user?.nombres }} {{ tx.user?.apellidos }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold" :class="tx.tipo === 'abono' ? 'text-green-500' : 'text-red-500'">{{ tx.tipo }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{ new Intl.NumberFormat('es-CO').format(tx.monto) }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{ formatDate(tx.created_at) }}</td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <Button variant="ghost" @click="openEditModal(tx)" class="mr-2">Editar</Button>
                                        <Button variant="destructive" @click="deleteTransaction(tx)">Eliminar</Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Editar Transacción' : 'Crear Nueva Transacción' }}</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="grid gap-4 py-4">
                     <div v-if="!isEditMode" class="grid grid-cols-4 items-center gap-4">
                        <Label for="id_user" class="text-right">Usuario</Label>
                        <div class="col-span-3">
                             <select id="id_user" v-model="form.id_user" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option disabled value="">Selecciona un usuario</option>
                                <option v-for="user in props.users" :key="user.id" :value="user.id">
                                    {{ user.nombres }} {{ user.apellidos }}
                                </option>
                            </select>
                            <InputError :message="form.errors.id_user" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="tipo" class="text-right">Tipo</Label>
                        <div class="col-span-3">
                            <select id="tipo" v-model="form.tipo" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                                <option value="abono">Abono</option>
                                <option value="retiro">Retiro</option>
                            </select>
                            <InputError :message="form.errors.tipo" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="monto" class="text-right">Monto</Label>
                        <div class="col-span-3">
                            <Input id="monto" type="number" v-model="form.monto" step="0.01" class="w-full" />
                            <InputError :message="form.errors.monto" class="mt-1" />
                        </div>
                    </div>

                     <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="observacion" class="text-right">Observación</Label>
                        <div class="col-span-3">
                            <Input id="observacion" v-model="form.observacion" class="w-full" />
                            <InputError :message="form.errors.observacion" class="mt-1" />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="closeModal">Cancelar</Button>
                        <Button type="submit" :disabled="form.processing">Guardar</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>