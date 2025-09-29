<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { Eye, EyeOff } from 'lucide-vue-next';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const showReferralInput = ref(false);
const form = useForm({
    nombres: '',
    apellidos: '',
    identification_type: '', // Valor por defecto
    identification_number: '',
    celular: '',
    email: '',
    password: '',
    password_confirmation: '',
    referral_code: '',
});


const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>


<template>
    <AuthBase title="Registrate ahora" description="Ingresa los datos para crear tu cuenta.">

        <Head title="Registro" />


        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="nombres">Nombres</Label>
                    <Input id="nombres" type="text" required autofocus :tabindex="1" autocomplete="given-name"
                        v-model="form.nombres" placeholder="Nombres" />
                    <InputError :message="form.errors.nombres" />
                </div>


                <div class="grid gap-2">
                    <Label for="apellidos">Apellidos</Label>
                    <Input id="apellidos" type="text" required :tabindex="2" autocomplete="family-name"
                        v-model="form.apellidos" placeholder="Apellidos" />
                    <InputError :message="form.errors.apellidos" />
                </div>

                <div class="grid gap-2">
                    <Label for="identification_type">Tipo de Documento</Label>
                    <select id="identification_type" v-model="form.identification_type"
                        class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm"
                        required>
                        <option value="" disabled>-- Selecciona un tipo --</option>

                        <option value="CEDULA CIUDANIA">Cédula de Ciudadanía</option>
                        <option value="TARJETA IDENTIDAD">Tarjeta de Identidad</option>
                        <option value="CEDULA EXTRANJERIA">Cédula de Extranjería</option>
                        <option value="PASAPORTE">Pasaporte</option>
                    </select>
                    <InputError :message="form.errors.identification_type" />
                </div>

                <div class="grid gap-2">
                    <Label for="identification_number">Número de Documento</Label>
                    <Input id="identification_number" type="text" v-model="form.identification_number" required
                        autocomplete="off" placeholder="Número de Documento" />
                    <InputError :message="form.errors.identification_number" />
                </div>


                <div class="grid gap-2">
                    <Label for="celular">Celular</Label>
                    <Input id="celular" type="text" required :tabindex="3" autocomplete="tel" v-model="form.celular"
                        placeholder="Número de celular" />
                    <InputError :message="form.errors.celular" />
                </div>


                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input id="email" type="email" required :tabindex="4" autocomplete="email" v-model="form.email"
                        placeholder="email@dominio.com" />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="flex items-center space-x-2">
                    <input type="checkbox" id="has-referral" v-model="showReferralInput" />
                    <Label for="has-referral" class="text-sm font-medium">¿Te invitó un amigo? Ingresa su código</Label>
                </div>

                <div v-if="showReferralInput" class="grid gap-2">
                    <Label for="referral_code">Código de Referido</Label>
                    <Input id="referral_code" type="text" v-model="form.referral_code" placeholder="EJEMPLO-123"
                        autocomplete="off" />
                    <InputError :message="form.errors.referral_code" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <div class="relative">
                        <Input id="password" :type="showPassword ? 'text' : 'password'" class="pr-10" required
                            v-model="form.password" placeholder="Contraseña" />
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <div class="relative">
                        <Input id="password_confirmation" :type="showPasswordConfirmation ? 'text' : 'password'"
                            class="pr-10" required v-model="form.password_confirmation"
                            placeholder="Confirmar contraseña" />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground">
                            <Eye v-if="!showPasswordConfirmation" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>


                <Button type="submit" class="mt-2 w-full" tabindex="7" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Crear cuenta
                </Button>
            </div>


            <div class="text-center text-sm text-muted-foreground">
                Ya tienes una cuenta?
                <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="8">Ingresa</TextLink>
            </div>
        </form>
    </AuthBase>
</template>