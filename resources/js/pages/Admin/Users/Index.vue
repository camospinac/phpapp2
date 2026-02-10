<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type User } from '@/types';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';

import { FileSpreadsheet } from 'lucide-vue-next';

import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { ShieldAlert } from 'lucide-vue-next';

import { router } from '@inertiajs/vue3';
import { watch } from 'vue';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    first_page_url: string;
    from: number;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number;
    total: number;
}

// --- ACTUALIZA TUS PROPS ---
const props = defineProps<{
    users: PaginatedUsers; // <-- Cambiamos User[] por PaginatedUsers
    filters: any;
}>();


// Inicializamos los valores del filtro con lo que venga del server
const filterForm = ref({
    search: props.filters.search || '',
    rol: props.filters.rol || '',
    location: props.filters.location || '',
});

// 💡 El "Truco Maestro": Cuando filterForm cambie, Inertia pide los datos
watch(filterForm, (value) => {
    router.get(route('admin.users.index'), value, {
        preserveState: true, // No pierde el foco del input
        replace: true,       // No llena el historial de flechitas "atrás"
        only: ['users', 'filters'], // Solo actualiza la data necesaria
    });
}, { deep: true });

const page = usePage();
// Asumiendo que tu objeto de usuario tiene un campo 'rol'
const isAdmin = computed(() => page.props.auth.user?.rol === 'admin');

const isModalOpen = ref(false);
const isEditMode = ref(false);

const form = useForm({
    id: '',
    nombres: '',
    apellidos: '',
    celular: '',
    email: '',
    location: '',
    rol: 'usuario',
    password: '',
    password_confirmation: '',
});

const openCreateModal = () => {
    isEditMode.value = false;
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};


const exportToExcel = () => {
    // Mandamos los mismos filtros que ya tenemos en filterForm
    const params = new URLSearchParams(filterForm.value).toString();
    window.location.href = route('admin.users.export') + '?' + params;
};

const openEditModal = (user: User) => {
    isEditMode.value = true;
    form.id = user.id;
    form.nombres = user.nombres;
    form.apellidos = user.apellidos;
    form.celular = user.celular;
    form.email = user.email;
    form.location = user.location;
    form.rol = user.rol;
    form.password = '';
    form.password_confirmation = '';
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const submitForm = () => {
    if (isEditMode.value) {
        form.patch(route('admin.users.update', form.id), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => closeModal(),
        });
    }
};

const deleteUser = (user: User) => {
    if (confirm(`¿Estás seguro de que quieres eliminar a ${user.nombres} ${user.apellidos}?`)) {
        useForm({}).delete(route('admin.users.destroy', user.id), {
            preserveScroll: true,
        });
    }
};

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Usuarios', href: route('admin.users.index') },
];

const isPanicModalOpen = ref(false);
const confirmationPhrase = 'BLOQUEAR A TODOS LOS USUARIOS';
const typedConfirmation = ref('');

const isConfirmationCorrect = computed(() => {
    return typedConfirmation.value === confirmationPhrase;
});

const panicForm = useForm({});
const executeBlockAll = () => {
    // The data goes directly as the first argument
    panicForm.post(route('admin.users.block-all', {
        confirmation: typedConfirmation.value
    }), {
        // The options (like onSuccess) go in the second argument
        onSuccess: () => {
            isPanicModalOpen.value = false;
            typedConfirmation.value = '';
        },
    });
};

</script>

<template>

    <Head title="Gestión de Usuarios" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 sm:p-6 lg:p-8">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-base font-semibold leading-6 text-foreground">Usuarios</h1>
                    <p class="mt-2 text-sm text-muted-foreground">
                        Una lista de todos los usuarios de la aplicación.
                    </p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none flex items-center gap-4">

                    <Button v-if="isAdmin" @click="isPanicModalOpen = true" variant="destructive"
                        class="flex items-center gap-2">
                        <ShieldAlert class="h-4 w-4" />
                        Bloqueo Masivo
                    </Button>

                    <Button v-if="isAdmin" @click="openCreateModal">
                        Añadir Usuario
                    </Button>

                    <Button @click="exportToExcel" variant="outline"
                        class="flex items-center gap-2 border-green-600 text-green-600 hover:bg-green-50">
                        <FileSpreadsheet class="h-4 w-4" />
                        Exportar Excel
                    </Button>

                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="grid gap-2">
                    <Label>Buscar usuario</Label>
                    <Input v-model="filterForm.search" placeholder="Nombre, email o celular..." />
                </div>

                <div class="grid gap-2">
                    <Label>Rol</Label>
                    <select v-model="filterForm.rol"
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                        <option value="">Todos los roles</option>
                        <option value="usuario">Usuario</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>

                <div class="grid gap-2">
                    <Label>Ubicación</Label>
                    <Input v-model="filterForm.location" placeholder="Ej: Girardot" />
                </div>
            </div>
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-border">
                            <thead>
                                <tr>
                                    <th scope="col"
                                        class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-foreground sm:pl-0">
                                        Nombre</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">
                                        Email</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">
                                        Celular</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">
                                        Ubicación</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-foreground">
                                        Rol</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">Edit</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td
                                        class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-foreground sm:pl-0">
                                        <div class="flex items-center gap-2">
                                            <Link :href="route('admin.users.show', user.id)"
                                                class="hover:text-primary hover:underline">
                                                {{ user.nombres }} {{ user.apellidos }}
                                            </Link>

                                            <span v-if="user.es_cuenta_prueba"
                                                class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700 border border-blue-200 shadow-sm">
                                                Prueba
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{ user.email
                                        }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{
                                        user.celular }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{
                                        user.location }}</td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-muted-foreground">{{ user.rol }}
                                    </td>
                                    <td
                                        class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                        <Button variant="ghost" @click="openEditModal(user)"
                                            class="mr-2">Editar</Button>
                                        <Button variant="destructive" @click="deleteUser(user)">Eliminar</Button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="mt-4 flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6">
                            <div class="flex flex-1 justify-between sm:hidden">
                                <Link :href="users.prev_page_url ?? ''" :disabled="!users.prev_page_url"
                                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !users.prev_page_url }">
                                    Anterior
                                </Link>

                                <Link :href="users.next_page_url ?? ''" :disabled="!users.next_page_url"
                                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50"
                                    :class="{ 'opacity-50 cursor-not-allowed pointer-events-none': !users.next_page_url }">
                                    Siguiente
                                </Link>
                            </div>
                            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700">
                                        Mostrando <span class="font-medium">{{ users.from }}</span> a <span
                                            class="font-medium">{{ users.to }}</span> de <span class="font-medium">{{
                                                users.total }}</span> resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm">
                                        <Link v-for="(link, k) in users.links" :key="k" :href="link.url ?? ''"
                                            v-html="link.label"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20"
                                            :class="[
                                                link.active
                                                    ? 'z-10 bg-primary text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                                                    : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:outline-offset-0',
                                                !link.url ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''
                                            ]" />
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <Dialog :open="isModalOpen" @update:open="isModalOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>{{ isEditMode ? 'Editar Usuario' : 'Crear Nuevo Usuario' }}</DialogTitle>
                    <DialogDescription>
                        Completa los detalles del usuario. La contraseña solo es requerida al crear uno nuevo.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitForm" class="grid gap-4 py-4">
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="nombres" class="text-right">Nombres</Label>
                        <div class="col-span-3">
                            <Input id="nombres" v-model="form.nombres" class="w-full" />
                            <InputError :message="form.errors.nombres" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="apellidos" class="text-right">Apellidos</Label>
                        <div class="col-span-3">
                            <Input id="apellidos" v-model="form.apellidos" class="w-full" />
                            <InputError :message="form.errors.apellidos" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="celular" class="text-right">Celular</Label>
                        <div class="col-span-3">
                            <Input id="celular" v-model="form.celular" class="w-full" />
                            <InputError :message="form.errors.celular" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="email" class="text-right">Email</Label>
                        <div class="col-span-3">
                            <Input id="email" type="email" v-model="form.email" class="w-full" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="rol" class="text-right">Rol</Label>
                        <div class="col-span-3">
                            <select id="rol" v-model="form.rol"
                                class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <option value="usuario">Usuario</option>
                                <option value="admin">Admin</option>
                            </select>
                            <InputError :message="form.errors.rol" class="mt-1" />
                        </div>
                    </div>

                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="password" class="text-right">Contraseña</Label>
                        <div class="col-span-3">
                            <Input id="password" type="password" v-model="form.password" class="w-full" />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-4 items-center gap-4">
                        <Label for="password_confirmation" class="text-right">Confirmar</Label>
                        <div class="col-span-3">
                            <Input id="password_confirmation" type="password" v-model="form.password_confirmation"
                                class="w-full" />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="secondary" @click="closeModal">Cancelar</Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ isEditMode ? 'Guardar Cambios' : 'Crear Usuario' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <Dialog :open="isPanicModalOpen" @update:open="isPanicModalOpen = $event">
            <DialogContent class="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle class="text-xl text-destructive">¡Acción de Emergencia!</DialogTitle>
                    <DialogDescription>
                        Estás a punto de bloquear las cuentas de TODOS los usuarios. Esta acción es irreversible a
                        través de la interfaz.
                        Para continuar, por favor escribe la siguiente frase exacta en el campo de abajo:
                    </DialogDescription>
                </DialogHeader>
                <div class="py-4 space-y-4">
                    <div class="p-3 bg-muted rounded-md text-center">
                        <p class="font-mono font-bold text-destructive">{{ confirmationPhrase }}</p>
                    </div>
                    <div class="grid gap-2">
                        <Label for="confirmation">Escribe la frase para confirmar</Label>
                        <Input id="confirmation" v-model="typedConfirmation" />
                    </div>
                </div>
                <div class="flex justify-end">
                    <Button @click="executeBlockAll" variant="destructive"
                        :disabled="!isConfirmationCorrect || panicForm.processing">
                        Confirmar Bloqueo Masivo
                    </Button>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>