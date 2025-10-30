<script setup lang="ts">
import { h } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// Local InputError component to avoid missing module/typings
const InputError = (props: { message?: string | string[] | null }) => {
    if (!props.message) return null;
    const text = Array.isArray(props.message) ? props.message.join(', ') : (props.message as string);
    return h('p', { class: 'text-sm text-red-600' }, text);
};

// --- CAMBIOS EN PROPS ---
const props = defineProps<{
    winner?: any; // El prop 'users' ya no es necesario
}>();

// --- CAMBIOS EN EL FORMULARIO ---
const form = useForm({
    _method: props.winner ? 'PATCH' : 'POST',
    nombre_completo: props.winner?.nombre_completo ?? '', // NUEVO
    cedula: props.winner?.cedula ?? '',             // NUEVO
    win_date: props.winner?.win_date ?? '',
    prize: props.winner?.prize ?? '',
    city: props.winner?.city ?? '',
    photo: null as File | null,
});

const submit = () => {
    const url = props.winner 
        ? route('admin.winners.update', props.winner.id) 
        : route('admin.winners.store');
    
    // El post se queda igual, pero ahora envía 'nombre_completo' y 'cedula'
    form.post(url); 
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        
        <div class="grid gap-2">
            <Label for="nombre_completo">Nombre Completo</Label>
            <Input 
                id="nombre_completo" 
                type="text" 
                v-model="form.nombre_completo" 
                placeholder="Ej: Juan Pérez" 
            />
            <InputError :message="form.errors.nombre_completo" />
        </div>

        <div class="grid gap-2">
            <Label for="cedula">Cédula</Label>
            <Input 
                id="cedula" 
                type="text" 
                v-model="form.cedula" 
                placeholder="Ej: 123456789" 
            />
            <InputError :message="form.errors.cedula" />
        </div>
        <div class="grid gap-2">
            <Label for="win_date">Fecha del Premio</Label>
            <Input id="win_date" type="date" v-model="form.win_date" />
            <InputError :message="form.errors.win_date" />
        </div>

        <div class="grid gap-2">
            <Label for="prize">Premio</Label>
            <Input id="prize" type="text" v-model="form.prize" />
            <InputError :message="form.errors.prize" />
        </div>

        <div class="grid gap-2">
            <Label for="city">Ciudad</Label>
            <Input id="city" type="text" v-model="form.city" />
            <InputError :message="form.errors.city" />
        </div>

        <div class="grid gap-2">
            <Label for="photo">Foto del Ganador</Label>
            <Input id="photo" type="file" @input="form.photo = ($event.target as HTMLInputElement).files?.[0] ?? null" />
            <InputError :message="form.errors.photo" />
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ winner ? 'Actualizar' : 'Guardar' }} Ganador
            </Button>
        </div>
    </form>
</template>