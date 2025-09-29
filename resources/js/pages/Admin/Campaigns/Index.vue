<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

defineProps<{ campaigns: any[] }>();

const deleteCampaign = (id: number) => {
    if (confirm('¿Estás seguro?')) {
        router.delete(route('admin.campaigns.destroy', id));
    }
};
</script>
<template>
    <Head title="Gestionar Campañas" />
    <AppLayout>
        <div class="p-4 md:p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Gestionar Campañas</h1>
                <Link :href="route('admin.campaigns.create')">
                    <Button>Crear Campaña</Button>
                </Link>
            </div>
            <div class="rounded-xl border bg-card text-card-foreground">
                <table class="w-full text-sm">
                    <thead><tr class="border-b"><th class="p-3 text-left">Nombre Interno</th><th class="p-3 text-left">Título Visible</th><th class="p-3 text-center">Estado</th><th class="p-3 text-center">Acciones</th></tr></thead>
                    <tbody>
                        <tr v-for="campaign in campaigns" :key="campaign.id" class="border-b">
                            <td class="p-3 font-medium">{{ campaign.name }}</td>
                            <td class="p-3">{{ campaign.title }}</td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="campaign.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                    {{ campaign.is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                            </td>
                            <td class="p-3"><div class="flex justify-center gap-2"><Link :href="route('admin.campaigns.edit', campaign.id)"><Button variant="outline" size="sm">Editar</Button></Link><Button @click="deleteCampaign(campaign.id)" variant="destructive" size="sm">Eliminar</Button></div></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>