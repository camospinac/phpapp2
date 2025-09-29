<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next'; 

interface Props {
    token: string;
    email: string;
}

const props = defineProps<Props>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <AuthLayout title="Recuperar contraseña" description="Por favor escriba su nueva contraseña.">
        <Head title="Recuperar contraseña" />

        <form @submit.prevent="submit">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Correo</Label>
                    <Input id="email" type="email" name="email" autocomplete="email" v-model="form.email" class="mt-1 block w-full" readonly />
                    <InputError :message="form.errors.email" class="mt-2" />
                </div>

                                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            v-model="form.password"
                            class="pr-10"
                            required
                            autofocus
                            placeholder="Contraseña"
                        />
                        <button 
                            type="button" 
                            @click="showPassword = !showPassword" 
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground"
                        >
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            :type="showPasswordConfirmation ? 'text' : 'password'"
                            v-model="form.password_confirmation"
                            class="pr-10"
                            required
                            placeholder="Confirmar contraseña"
                        />
                         <button 
                            type="button" 
                            @click="showPasswordConfirmation = !showPasswordConfirmation" 
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground"
                        >
                            <Eye v-if="!showPasswordConfirmation" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>


                <Button type="submit" class="mt-4 w-full" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Recuperar contraseña
                </Button>
            </div>
        </form>
    </AuthLayout>
</template>
