<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types'; // Asumo que tienes tu tipo BreadcrumbItem
import { Button } from '@/components/ui/button';
import { Check, X, Pencil } from 'lucide-vue-next';
// No se importan Table, Badge, ni Label (no se usa)

defineProps<{
    paymentMethods: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [ // Usando 'title' como en tu ejemplo
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Métodos de Pago', href: route('admin.payment-methods.index') },
];
</script>

<template>
    <Head title="Métodos de Pago" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 md:p-6">
            <div class="p-4 md:p-6 rounded-lg border bg-card">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold">Gestionar Métodos de Pago</h2>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full min-w-full text-sm">
                        <thead class="border-b">
                            <tr class="hover:bg-muted/50">
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Nombre</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Detalles de la Cuenta</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Estado</th>
                                <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="method in paymentMethods" :key="method.id" class="hover:bg-muted/50">
                                <td class="p-4 align-middle font-medium">{{ method.name }}</td>
                                <td class="p-4 align-middle">{{ method.account_details }}</td>
                                <td class="p-4 align-middle">
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors',
                                            method.is_active
                                                ? 'border-transparent bg-green-600 text-white'
                                                : 'border-transparent bg-destructive text-destructive-foreground'
                                        ]"
                                    >
                                        <component :is="method.is_active ? Check : X" class="w-4 h-4 mr-1" />
                                        {{ method.is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="p-4 align-middle">
                                    <Button as-child variant="outline" size="icon">
                                        <Link :href="route('admin.payment-methods.edit', method.id)">
                                            <Pencil class="w-4 h-4" />
                                        </Link>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>