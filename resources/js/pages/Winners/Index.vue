<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { register } from 'swiper/element/bundle';
register();


// --- INTERFACES & PROPS ---
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
const props = defineProps<{
    winners: Winner[];
}>();

const prizeCategories = ref({
    tier1: {
        title: 'Inversiones de $200.000 a $1.000.000',
        prizes: [
            { name: 'Viaje a Cartagena', imageUrl: '/img/premios/avionm.jpg' },
            { name: 'iPhone 15 Pro', imageUrl: '/img/premios/iphone.jpg' },
            { name: 'Smart TV 55"', imageUrl: '/img/premios/smart.jpg' },
            { name: 'Bono 200 mil', imageUrl: '/img/premios/bono.png' },
            { name: 'Bono 100 mil', imageUrl: '/img/premios/bono.png' },
            { name: 'Bono 50 mil', imageUrl: '/img/premios/bono.png' },
        ]
    },
    tier2: {
        title: 'Inversiones Superiores a $1.000.000',
        prizes: [
            { name: 'Moto 0km', imageUrl: '/img/premios/moto.png' },
        ]
    }
});

// --- LÓGICA PARA EL MODAL DE LA FOTO ---
const isPrizeModalOpen = ref(false);
const selectedPrize = ref<{ name: string; imageUrl: string } | null>(null);

const openPrizeModal = (prize: { name: string; imageUrl: string }) => {
    selectedPrize.value = prize;
    isPrizeModalOpen.value = true;
};


// --- LÓGICA DEL MODAL DE FOTOS ---
const isPhotoModalOpen = ref(false);
const selectedPhotoUrl = ref('');

const openPhotoModal = (photoUrl: string) => {
    selectedPhotoUrl.value = photoUrl;
    isPhotoModalOpen.value = true;
};

// --- BREADCRUMBS ---
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Últimos Ganadores', href: route('winners.index') },
];

// --- HELPERS ---
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-CO', {
        year: 'numeric', month: 'long', day: 'numeric'
    });
};
// Función para ocultar la cédula
const maskIdentification = (id: string) => {
    if (!id || id.length <= 4) return '****';
    return id.slice(0, -4).replace(/./g, '*') + id.slice(-4);
};
</script>

<template>

    <Head title="Últimos Ganadores" />

    <AppLayout :breadcrumbs="breadcrumbs">
       <div class="mb-6 p-6 rounded-xl bg-gradient-to-r from-primary to-slate-900 text-white shadow-lg">
            <h2 class="text-2xl font-bold mb-4 text-center">¡Nuestros Premios a los más Fieles!</h2>

            <details class="group bg-black/20 rounded-lg overflow-hidden mb-3">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <h3 class="font-semibold text-lg">🏆 {{ prizeCategories.tier1.title }}</h3>
                    <span class="transition-transform duration-300 group-open:rotate-180">▼</span>
                </summary>
                <div class="p-4 bg-black/30">
                    <swiper-container slides-per-view="auto" :space-between="15" navigation="true">
                        <swiper-slide v-for="(prize, index) in prizeCategories.tier1.prizes" :key="index" class="!w-48">
                            <div @click="openPrizeModal(prize)" class="cursor-pointer group/slide">
                                <img :src="prize.imageUrl" :alt="prize.name"
                                    class="w-full h-32 object-cover rounded-md transition-transform duration-300 group-hover/slide:scale-105" />
                                <p class="mt-2 text-sm font-medium text-center">{{ prize.name }}</p>
                            </div>
                        </swiper-slide>
                    </swiper-container>
                </div>
            </details>

            <details class="group bg-black/20 rounded-lg overflow-hidden">
                <summary class="flex items-center justify-between p-4 cursor-pointer">
                    <h3 class="font-semibold text-lg">🚗 {{ prizeCategories.tier2.title }}</h3>
                    <span class="transition-transform duration-300 group-open:rotate-180">▼</span>
                </summary>
                <div class="p-4 bg-black/30">
                    <swiper-container slides-per-view="auto" :space-between="15" navigation="true">
                        <swiper-slide v-for="(prize, index) in prizeCategories.tier2.prizes" :key="index" class="!w-48">
                            <div @click="openPrizeModal(prize)" class="cursor-pointer group/slide">
                                <img :src="prize.imageUrl" :alt="prize.name"
                                    class="w-full h-32 object-cover rounded-md transition-transform duration-300 group-hover/slide:scale-105" />
                                <p class="mt-2 text-sm font-medium text-center">{{ prize.name }}</p>
                            </div>
                        </swiper-slide>
                    </swiper-container>
                </div>
            </details>
        </div>
        <div class="p-4 md:p-6 rounded-xl border bg-card text-card-foreground">
            <h1 class="text-3xl font-bold mb-6">Lista de Felices Ganadores</h1>

            <div v-if="winners.length === 0" class="text-center py-12 text-muted-foreground">
                <p>Aún no hay ganadores registrados. ¡El próximo podrías ser tú!</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="border-b">
                        <tr>
                            <th class="p-3 text-left">Foto</th>
                            <th class="p-3 text-left">Ganador</th>
                            <th class="p-3 text-left">Cédula</th>
                            <th class="p-3 text-left">Premio</th>
                            <th class="p-3 text-left">Ciudad</th>
                            <th class="p-3 text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="winner in winners" :key="winner.id" class="border-b">
                            <td class="p-3">
                                <img v-if="winner.photo_path" :src="`/storage/${winner.photo_path}`" alt="Foto"
                                    class="h-12 w-12 rounded-full object-cover cursor-pointer hover:ring-2 hover:ring-primary"
                                    @click="openPhotoModal(`/storage/${winner.photo_path}`)">
                            </td>
                            <td class="p-3 font-medium">{{ winner.user.nombres }} {{ winner.user.apellidos }}</td>
                            <td class="p-3 font-mono text-muted-foreground">{{
                                maskIdentification(winner.user.identification_number) }}</td>
                            <td class="p-3">{{ winner.prize }}</td>
                            <td class="p-3 text-muted-foreground">{{ winner.city }}</td>
                            <td class="p-3 text-right text-muted-foreground">{{ formatDate(winner.win_date) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>

    <Dialog :open="isPhotoModalOpen" @update:open="isPhotoModalOpen = false">
        <DialogContent class="p-2 sm:max-w-xl">
            <img :src="selectedPhotoUrl" alt="Foto del Ganador" class="w-full rounded-lg">
        </DialogContent>
    </Dialog>

    <Dialog :open="isPrizeModalOpen" @update:open="isPrizeModalOpen = false">
        <DialogContent class="p-2 sm:max-w-xl">
            <DialogTitle class="p-4 text-lg font-semibold">{{ selectedPrize?.name }}</DialogTitle>
            <img v-if="selectedPrize" :src="selectedPrize.imageUrl" alt="Premio" class="w-full rounded-b-lg">
        </DialogContent>
    </Dialog>
</template>