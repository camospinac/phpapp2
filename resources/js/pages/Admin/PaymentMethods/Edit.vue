<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types'; // Asumo que tienes tu tipo BreadcrumbItem
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import InputError from '@/components/InputError.vue'; 

const props = defineProps<{
    paymentMethod: any;
}>();

// --- ESTA ES LA CORRECCIÓN #1 ---
// Tu breadcrumb anterior no estaba usando el prop, por eso se sentía "roto".
// Ahora sí usa el nombre del método en el título.
const breadcrumbs: BreadcrumbItem[] =[
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Métodos de Pago', href: route('admin.payment-methods.index') },
    { title: `Editar ${props.paymentMethod.name}`, href: route('admin.payment-methods.edit', props.paymentMethod.id) }
];

const form = useForm({
    _method: 'PATCH',
    name: props.paymentMethod.name,
    account_details: props.paymentMethod.account_details,
    is_active: props.paymentMethod.is_active,
});

const submit = () => {
    form.post(route('admin.payment-methods.update', props.paymentMethod.id));
};
</script>

<template>
    <Head :title="`Editar ${paymentMethod.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        
        <div class="p-4 md:p-6">
            <div class="p-4 md:p-6 rounded-lg border bg-card">
                <h2 class="text-xl font-semibold mb-6">
                    Editar Método de Pago
                </h2>
                
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nombre del Método</Label>
                        <Input id="name" v-model="form.name" disabled  class="disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed "/>
                    </div>

                    <div class="grid gap-2">
                        <Label for="account_details">Detalles de la Cuenta (Número, Cédula, etc.)</Label>
                        <Input 
                            id="account_details" 
                            v-model="form.account_details" 
                            placeholder="Ej: 3001234567 - Juan Pérez" 
                        />
                        <InputError :message="form.errors.account_details" />
                    </div>

                    <div class="flex items-center space-x-3">
                        <input 
                            type="checkbox" 
                            id="is_active" 
                            :checked="form.is_active"
                            @change="form.is_active = ($event.target as HTMLInputElement).checked"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
                        />
                        <Label for="is_active">Método Activo</Label>
                        <InputError :message="form.errors.is_active" />
                    </div>

                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing">
                            Guardar Cambios
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>