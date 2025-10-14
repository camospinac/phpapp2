<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { ChevronUp, RefreshCw, ArrowLeft } from 'lucide-vue-next';

const isMenuOpen = ref(false);

const goBack = () => {
    history.back();
    isMenuOpen.value = false;
};

const refreshPage = () => {
    router.reload();
    isMenuOpen.value = false;
};
</script>

<template>
   <div class="fixed bottom-6 right-6 z-50 flex md:hidden">
        <div class="relative flex flex-col items-center gap-2">
            <transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-150"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
            >
                <div v-if="isMenuOpen" class="flex flex-col gap-2">
                    <Button @click="goBack" variant="secondary" size="icon" class="h-12 w-12 rounded-full shadow-lg" title="Atrás">
                        <ArrowLeft class="h-6 w-6" />
                    </Button>
                    <Button @click="refreshPage" variant="secondary" size="icon" class="h-12 w-12 rounded-full shadow-lg" title="Refrescar">
                        <RefreshCw class="h-6 w-6" />
                    </Button>
                </div>
            </transition>

            <Button @click="isMenuOpen = !isMenuOpen" size="icon" class="h-14 w-14 rounded-full shadow-2xl">
                <transition
                    enter-active-class="transition ease-out duration-300"
                    enter-from-class="transform rotate-0"
                    enter-to-class="transform rotate-180"
                    leave-active-class="transition ease-in duration-300"
                    leave-from-class="transform rotate-180"
                    leave-to-class="transform rotate-0"
                >
                    <ChevronUp :class="['h-7 w-7 transition-transform', { 'rotate-180': isMenuOpen }]" />
                </transition>
            </Button>
        </div>
    </div>
</template>