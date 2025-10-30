<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Dialog, DialogContent } from '@/components/ui/dialog';
import { register } from 'swiper/element/bundle';
register();


// --- INTERFACES & PROPS ---

interface Winner {
    id: number;
    prize: string;
    win_date: string;
    city: string;
    photo_path: string;
    nombre_completo: string;
    cedula: string;
}
const props = defineProps<{
    winners: Winner[];
}>();

const prizeCategories = ref({
    tier1: {
        title: 'Inversiones de $200.000 a $1.000.000',
        prizes: [
            { name: 'iPhone 16 Pro', imageUrl: '/img/premios/iphone16.png' },
            { name: 'Smart TV 55"', imageUrl: '/img/premios/televisor.jpg' },
            { name: 'Portátil', imageUrl: '/img/premios/computador.jpg' },
            { name: 'Tablet', imageUrl: '/img/premios/tablet.jpeg' },
            { name: 'Patineta eléctrica', imageUrl: '/img/premios/patineta.jpg' },
            { name: 'Apple Watch', imageUrl: '/img/premios/watch.jpg' },
            { name: 'PlayStation 5', imageUrl: '/img/premios/play5.jpg' },
            { name: 'Xbox One', imageUrl: '/img/premios/xbox.jpg' },
            { name: 'Nintendo Switch', imageUrl: '/img/premios/nintendo.jpg' },
            { name: 'Parlante Bosé S1 Pro', imageUrl: '/img/premios/bose.jpg' },
        ]
    },
    tier2: {
        title: 'Inversiones Superiores a $1.000.000',
        prizes: [
            { name: 'Moto MMax', imageUrl: '/img/premios/nmax.jpg' },
            { name: 'Moto MT09', imageUrl: '/img/premios/mt09.jpg' },
            { name: 'iPhone 17 Pro Max', imageUrl: '/img/premios/iphone17.png' },
            { name: 'MacBook Pro', imageUrl: '/img/premios/macbook.jpg' },
            { name: 'Moto eléctrica', imageUrl: '/img/premios/motoelectrica.png' },
            { name: 'Viaje a Aruba', imageUrl: '/img/premios/aruba.jpg' },
            { name: 'Viaje a Punta Cana', imageUrl: '/img/premios/puntacana.png' },
            { name: 'Crucero', imageUrl: '/img/premios/crucero.jpg' },
            { name: 'Carro 0km CX5', imageUrl: '/img/premios/mazda.png' },
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
                            <td class="p-3 font-medium">{{ winner.nombre_completo }}</td>
                            <td class="p-3 font-mono text-muted-foreground">{{
                                maskIdentification(winner.cedula) }}</td>
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