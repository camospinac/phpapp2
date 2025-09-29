<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Button } from '@/components/ui/button';
import { Download } from 'lucide-vue-next';

// 1. Usamos una referencia para guardar el "permiso" de instalación que nos da el navegador.
const installPromptEvent = ref<Event | null>(null);

// 2. Esta función se ejecutará cuando el usuario haga clic en nuestro botón.
const triggerInstall = async () => {
    if (!installPromptEvent.value) {
        return;
    }
    // Mostramos el diálogo de instalación nativo del navegador.
    (installPromptEvent.value as any).prompt();

    // Esperamos la decisión del usuario.
    const choice = await (installPromptEvent.value as any).userChoice;

    // Limpiamos el evento, ya que solo se puede usar una vez.
    installPromptEvent.value = null;

    if (choice.outcome === 'accepted') {
        console.log('¡Usuario aceptó instalar la PWA!');
    } else {
        console.log('Usuario canceló la instalación.');
    }
};

// 3. Al montar el componente, escuchamos el evento del navegador.
onMounted(() => {
    // Condición extra que pediste: Si la app ya se está ejecutando en modo "standalone" (instalada),
    // no hacemos nada.
    if (window.matchMedia('(display-mode: standalone)').matches) {
        return;
    }

    window.addEventListener('beforeinstallprompt', (e) => {
        // Prevenimos que el navegador muestre su mini-banner por defecto.
        e.preventDefault();
        // Guardamos el evento para usarlo después.
        installPromptEvent.value = e;
    });
});
</script>

<template>
    <div v-if="installPromptEvent" class="fixed bottom-4 right-4 z-50">
        <Button @click="triggerInstall" size="lg">
            <Download class="mr-2 h-5 w-5" />
            Instalar Aplicación
        </Button>
    </div>
</template>