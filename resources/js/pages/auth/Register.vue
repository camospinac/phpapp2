<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { ref, onMounted, computed, watch } from 'vue';
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

    location: '',
    temp_departamento: '',
    temp_ciudad: '',
});



onMounted(() => {
    // Lee los parámetros de la URL (ej: ?ref=CODE123)
    const urlParams = new URLSearchParams(window.location.search);

    // Busca un parámetro llamado 'ref'
    const referralCode = urlParams.get('ref');

    // Si SÍ lo encuentra...
    if (referralCode) {
        // Rellena el campo del formulario automáticamente
        form.referral_code = referralCode;

        // ¡Y marca el checkbox de "Te invitó un amigo"!
        showReferralInput.value = true;
    }
});



const submit = () => {

    form.location = `Colombia, ${selectedDept.value?.name}, ${selectedCityName.value}`;

    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const departments = ref<{ id: number; name: string }[]>([]);
const cities = ref<{ id: number; name: string }[]>([]);
const loadingCities = ref(false);

// Variables temporales para el UI
const selectedDept = ref<{ id: number; name: string } | null>(null);
const selectedCityName = ref('');

// 1. Cargar departamentos al iniciar
onMounted(async () => {
    try {
        const response = await fetch('https://api-colombia.com/api/v1/Department');
        departments.value = await response.json();
    } catch (error) {
        console.error("Error cargando departamentos", error);
    }
});

// 2. Cargar ciudades cuando cambie el departamento
const handleDeptChange = async () => {
    if (!selectedDept.value) return;
    
    cities.value = [];
    selectedCityName.value = '';
    loadingCities.value = true;
    
    try {
        const response = await fetch(`https://api-colombia.com/api/v1/Department/${selectedDept.value.id}/cities`);
        cities.value = await response.json();
    } catch (error) {
        console.error("Error cargando ciudades", error);
    } finally {
        loadingCities.value = false;
    }
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
                    <Input id="email" type="text" inputmode="email" required :tabindex="4" autocomplete="off"
                        autocorrect="off" autocapitalize="off" spellcheck="false" v-model="form.email"
                        placeholder="email@dominio.com" />
                    <InputError :message="form.errors.email" />
                </div>
<div class="grid gap-2">
    <Label>País</Label>
    <Input value="Colombia" readonly class="bg-muted cursor-not-allowed" tabindex="-1" />
</div>

<div class="grid gap-2">
    <Label for="dept">Departamento</Label>
    <select id="dept" v-model="selectedDept" @change="handleDeptChange"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary"
        required>
        <option :value="null" disabled>-- Selecciona Departamento --</option>
        <option v-for="dept in departments" :key="dept.id" :value="dept">
            {{ dept.name }}
        </option>
    </select>
</div>

<div class="grid gap-2">
    <Label for="city">Ciudad</Label>
    <select id="city" v-model="selectedCityName" :disabled="!selectedDept || loadingCities"
        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:ring-2 focus:ring-primary disabled:opacity-50"
        required>
        <option value="" disabled>
            {{ loadingCities ? 'Cargando ciudades...' : '-- Selecciona Ciudad --' }}
        </option>
        <option v-for="city in cities" :key="city.id" :value="city.name">
            {{ city.name }}
        </option>
    </select>
    <InputError :message="form.errors.location" />
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
                        <Input id="password" type="text" class="pr-10" :class="{ 'mask-text': !showPassword }" required
                            v-model="form.password" placeholder="*****" autocomplete="off" autocorrect="off"
                            autocapitalize="off" spellcheck="false" />

                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground z-10">
                            <Eye v-if="!showPassword" class="h-5 w-5" />
                            <EyeOff v-else class="h-5 w-5" />
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <div class="relative">
                        <Input id="password_confirmation" type="text" class="pr-10"
                            :class="{ 'mask-text': !showPasswordConfirmation }" required
                            v-model="form.password_confirmation" placeholder="*****" autocomplete="off"
                            autocorrect="off" autocapitalize="off" spellcheck="false" />
                        <button type="button" @click="showPasswordConfirmation = !showPasswordConfirmation"
                            class="absolute inset-y-0 right-0 flex items-center justify-center h-full px-3 text-muted-foreground z-10">
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

<style>
/* CSS para engañar al iPhone */
.mask-text {
    -webkit-text-security: disc !important;
    text-security: disc !important;
    font-family: text-security-disc !important;
    /* Fallback por si acaso */
}

/* Opcional: Ajuste para que el texto no salte al cambiar de clase */
input.mask-text {
    letter-spacing: 2px;
    /* A veces los puntos se ven muy pegados */
}
</style>