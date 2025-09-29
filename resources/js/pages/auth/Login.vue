<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Eye, EyeOff, LoaderCircle } from 'lucide-vue-next';
import { Link } from '@inertiajs/vue3';
import { ref, computed} from 'vue';
import Multiselect from '@vueform/multiselect';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';


const selectedCountryCode = ref('CO'); 

const isCountryModalOpen = ref(false);
const countries = [
  // --- América del Norte ---
  { value: 'CA', name: 'Canada' },
  { value: 'US', name: 'United States' },
  { value: 'MX', name: 'México' },

  // --- América Central ---
  { value: 'BZ', name: 'Belice' },
  { value: 'CR', name: 'Costa Rica' },
  { value: 'SV', name: 'El Salvador' },
  { value: 'GT', name: 'Guatemala' },
  { value: 'HN', name: 'Honduras' },
  { value: 'NI', name: 'Nicaragua' },
  { value: 'PA', name: 'Panamá' },

  // --- Caribe ---
  { value: 'CU', name: 'Cuba' },
  { value: 'DO', name: 'República Dominicana' },
  { value: 'HT', name: 'Haití' },
  { value: 'JM', name: 'Jamaica' },
  { value: 'TT', name: 'Trinidad y Tobago' },
  { value: 'BB', name: 'Barbados' },
  { value: 'BS', name: 'Bahamas' },
  { value: 'AG', name: 'Antigua y Barbuda' },
  { value: 'DM', name: 'Dominica' },
  { value: 'GD', name: 'Granada' },
  { value: 'KN', name: 'San Cristóbal y Nieves' },
  { value: 'LC', name: 'Santa Lucía' },
  { value: 'VC', name: 'San Vicente y las Granadinas' },

  // --- América del Sur ---
  { value: 'AR', name: 'Argentina' },
  { value: 'BO', name: 'Bolivia' },
  { value: 'BR', name: 'Brasil' },
  { value: 'CL', name: 'Chile' },
  { value: 'CO', name: 'Colombia' },
  { value: 'EC', name: 'Ecuador' },
  { value: 'GY', name: 'Guyana' },
  { value: 'PY', name: 'Paraguay' },
  { value: 'PE', name: 'Perú' },
  { value: 'SR', name: 'Surinam' },
  { value: 'UY', name: 'Uruguay' },
  { value: 'VE', name: 'Venezuela' },

  // --- Europa del Este ---
  { value: 'RU', name: 'Rusia' },
  { value: 'UA', name: 'Ucrania' },
  { value: 'BY', name: 'Bielorrusia' },
  { value: 'PL', name: 'Polonia' },
  { value: 'CZ', name: 'Chequia' },
  { value: 'SK', name: 'Eslovaquia' },
  { value: 'HU', name: 'Hungría' },

  // --- Europa del Norte ---
  { value: 'EE', name: 'Estonia' },
  { value: 'LV', name: 'Letonia' },
  { value: 'LT', name: 'Lituania' },
  { value: 'FI', name: 'Finlandia' },
  { value: 'SE', name: 'Suecia' },
  { value: 'NO', name: 'Noruega' },
  { value: 'DK', name: 'Dinamarca' },
  { value: 'IS', name: 'Islandia' },

  // --- Europa del Sur ---
  { value: 'ES', name: 'España' },
  { value: 'IT', name: 'Italia' },
  { value: 'PT', name: 'Portugal' },
  { value: 'GR', name: 'Grecia' },
  { value: 'HR', name: 'Croacia' },
  { value: 'SI', name: 'Eslovenia' },
  { value: 'RS', name: 'Serbia' },
  { value: 'ME', name: 'Montenegro' },
  { value: 'MK', name: 'Macedonia del Norte' },
  { value: 'AL', name: 'Albania' },
  { value: 'BG', name: 'Bulgaria' },
  { value: 'RO', name: 'Rumanía' },
  { value: 'FR', name: 'Francia' },
  { value: 'DE', name: 'Alemania' },
  { value: 'BE', name: 'Bélgica' },
  { value: 'NL', name: 'Países Bajos' },
  { value: 'LU', name: 'Luxemburgo' },
  { value: 'CH', name: 'Suiza' },
  { value: 'AT', name: 'Austria' },
  { value: 'LI', name: 'Liechtenstein' },
  { value: 'MC', name: 'Mónaco' },
];


const getCountryFlag = (code: string) => {
    return `https://flagcdn.com/w40/${code.toLowerCase()}.png`;
};

const getSelectedCountry = computed(() => {
    return countries.find(c => c.value === selectedCountryCode.value);
});

const showPassword = ref(false);

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <AuthBase title="Inicia sesión"
        description="Ingresa tu correo electrónico y contraseña a continuación.">

        <Head title="Ingresar" />

        <div class="absolute top-4 right-4">
            <Button variant="outline" @click="isCountryModalOpen = true" class="flex items-center gap-2">
                <img :src="getCountryFlag(selectedCountryCode)" alt="Bandera" class="h-4 w-6">
                <span>{{ selectedCountryCode }}</span>
            </Button>
        </div>

        <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="flex flex-col gap-6">
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input id="email" type="email" required autofocus :tabindex="1" autocomplete="email"
                        v-model="form.email" placeholder="tuemail@dominio.com" />
                    <InputError :message="form.errors.email" />
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


                <div class="text-right">
                        <Link :href="route('password.request')"
                            class="text-sm text-muted-foreground hover:text-primary underline">
                        ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing">
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                    Ingresar
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Aún no estás registrado?
                <TextLink :href="route('register')" :tabindex="5">Registrate</TextLink>
            </div>
        </form>
    </AuthBase>

    <Dialog :open="isCountryModalOpen" @update:open="isCountryModalOpen = false">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <DialogTitle>Selecciona tu país</DialogTitle>
            </DialogHeader>
            
            <Multiselect
                v-model="selectedCountryCode"
                :options="countries"
                :searchable="true"
                placeholder="Busca un país..."
                @select="isCountryModalOpen = false"
            >
                <template #option="{ option }">
                    <div class="flex items-center gap-3">
                        <img :src="getCountryFlag(option.value)" alt="Bandera" class="h-5 w-7">
                        <span>{{ option.name }}</span>
                    </div>
                </template>
                <template #singlelabel="{ value }">
                     <div class="flex items-center gap-2">
                        <img :src="getCountryFlag(value.value)" alt="Bandera" class="h-4 w-6">
                        <span>{{ value.name }}</span>
                    </div>
                </template>
            </Multiselect>

        </DialogContent>
    </Dialog>
</template>
