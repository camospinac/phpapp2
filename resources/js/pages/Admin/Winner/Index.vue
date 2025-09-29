<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';

// Add the new fields to the interfaces
interface User {
    nombres: string;
    apellidos: string;
    identification_number: string;
}
interface Winner {
    id: number;
    prize: string;
    win_date: string;
    city: string;
    photo_path: string;
    user: User;
}
defineProps<{
    winners: {
        data: Winner[];
        links: any[];
    };
}>();

const deleteWinner = (id: number) => {
    if (confirm('¿Estás seguro de que quieres eliminar este registro?')) {
        router.delete(route('admin.winners.destroy', id));
    }
};
</script>
<template>
    <Head title="Gestionar Ganadores" />
    <AppLayout>
        <div class="p-4 md:p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Gestionar Ganadores</h1>
                <Link :href="route('admin.winners.create')">
                    <Button>Añadir Ganador</Button>
                </Link>
            </div>
            <div class="rounded-xl border bg-card text-card-foreground">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="p-3 text-left">Foto</th> <th class="p-3 text-left">Usuario</th>
                            <th class="p-3 text-left">Cédula</th> <th class="p-3 text-left">Premio</th>
                            <th class="p-3 text-left">Ciudad</th> <th class="p-3 text-left">Fecha</th>
                            <th class="p-3 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="winner in winners.data" :key="winner.id" class="border-b">
                            <td class="p-3">
                                <img v-if="winner.photo_path" :src="`/storage/${winner.photo_path}`" alt="Foto" class="h-12 w-12 rounded-full object-cover">
                                <span v-else class="text-xs text-muted-foreground">Sin foto</span>
                            </td>
                            <td class="p-3 font-medium">{{ winner.user.nombres }} {{ winner.user.apellidos }}</td>
                            <td class="p-3 text-muted-foreground">{{ winner.user.identification_number }}</td> <td class="p-3">{{ winner.prize }}</td>
                            <td class="p-3 text-muted-foreground">{{ winner.city }}</td> <td class="p-3">{{ winner.win_date }}</td>
                            <td class="p-3">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('admin.winners.edit', winner.id)">
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </Link>
                                    <Button @click="deleteWinner(winner.id)" variant="destructive" size="sm">Eliminar</Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>