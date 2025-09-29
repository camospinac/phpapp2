// resources/js/types/inertia.d.ts

import { User } from '@/types'; // Asumo que tienes una interfaz de User en 'resources/js/types/index.d.ts'

declare module '@inertiajs/core' {
  interface PageProps {
    // Aquí defines tus mensajes flash
    flash: {
      success?: string;
      error?: string;
      withdrawal_code?: string; // <-- La propiedad que causa el error
    };
    // También es buena práctica definir el usuario autenticado
    auth: {
      user: User | null;
    };
  }
}

// Esto es necesario para que el archivo sea tratado como un módulo.
export {};