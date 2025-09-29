<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type User } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

const page = usePage();
const user = page.props.auth.user as User;

// --- AQUÍ ESTÁ EL CAMBIO ---
// Concatenamos 'nombres' y 'apellidos' para mostrar el nombre completo.
// 'user.email' ya viene desencriptado y listo para usar.
const form = useForm({
    name: `${user.nombres} ${user.apellidos}`,
    email: user.email,
});

// Ya no necesitamos la función submit porque el botón está oculto,
// pero la dejamos por si la quieres reactivar en el futuro.
const submit = () => {
    form.patch(route('profile.update'), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Ajustes Usuario" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Información del Usuario" description="Revisa tu nombres y apellidos, para actualizarlos comuniquese con el administrador." />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid gap-2">
                        <Label for="name">Nombre completo</Label>
                        <Input id="name" class="mt-1 block w-full" v-model="form.name" required autocomplete="name"
                            placeholder="Full name" disabled />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Correo electrónico</Label>
                        <Input id="email" type="email" class="mt-1 block w-full" v-model="form.email" required
                            autocomplete="username" placeholder="Email address" disabled />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>


                    <!-- <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Your email address is unverified.
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            >
                                Click here to resend the verification email.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            A new verification link has been sent to your email address.
                        </div>
                    </div> -->

                    <!-- <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">Save</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">Saved.</p>
                        </Transition>
                    </div> -->
                </form>
            </div>

            <!-- <DeleteUser /> -->
        </SettingsLayout>
    </AppLayout>
</template>
