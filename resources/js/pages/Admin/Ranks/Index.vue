<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Rank, type BreadcrumbItem } from '@/types';
import { Button } from '@/components/ui/button';
// Ya no se importan los componentes de Table ni DropdownMenu

defineProps<{
    ranks: Rank[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Rangos', href: route('admin.ranks.index') },
];

const deleteForm = useForm({});

// Esta función recibe el objeto 'rank' completo para usar su nombre en la confirmación
const deleteRank = (rank: Rank) => {
    if (confirm(`¿Estás seguro de que deseas eliminar el rango "${rank.name}"?`)) {
        deleteForm.delete(route('admin.ranks.destroy', rank.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Gestionar Rangos" />

    <AppLayout :breadcrumbs="breadcrumbs" :is-admin="true">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold">Gestionar Rangos</h1>
            <Button as-child>
                <Link :href="route('admin.ranks.create')">Crear Nuevo Rango</Link>
            </Button>
        </div>

        <!-- 
          Aquí empieza la tabla con el estilo de tu ejemplo.
          (p-4 md:p-6 no es necesario si seguimos el ejemplo exacto)
        -->
        <div class="rounded-xl border bg-card text-card-foreground">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left font-medium">Nombre</th>
                            <th class="p-3 text-left font-medium">Referidos Req.</th>
                            <th class="p-3 text-left font-medium">Recompensa (%)</th>
                            <th class="p-3 text-left font-medium">Estado</th>
                            <th class="p-3 text-right font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="ranks.length === 0">
                            <td colspan="5" class="p-3 text-center py-12 text-muted-foreground">
                                No hay rangos definidos.
                            </td>
                        </tr>
                        <tr v-for="rank in ranks" :key="rank.id" class="border-b">
                            <td class="p-3 font-medium">{{ rank.name }}</td>
                            <td class="p-3 text-muted-foreground">{{ rank.required_referrals }}</td>
                            <td class="p-3">{{ rank.reward_percentage }}%</td>
                            <td class="p-3">
                                <span :class="['px-2 py-1 rounded-full text-xs font-medium', rank.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800']">
                                    {{ rank.is_active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <!-- Botones de acción directos, como en tu ejemplo -->
                                <div class="flex justify-end gap-2">
                                    <Link :href="route('admin.ranks.edit', rank.id)">
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </Link>
                                    <Button @click="deleteRank(rank)" variant="destructive" size="sm">
                                        Eliminar
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>