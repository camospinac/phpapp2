<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
//import InputError from '@/components/ui/input-error';

const props = defineProps<{
    users: any[];
    winner?: any;
}>();

const form = useForm({
    _method: props.winner ? 'PATCH' : 'POST',
    user_id: props.winner?.user_id ?? '',
    win_date: props.winner?.win_date ?? '',
    prize: props.winner?.prize ?? '',
    city: props.winner?.city ?? '',
    photo: null as File | null,
});

const submit = () => {
    const url = props.winner 
        ? route('admin.winners.update', props.winner.id) 
        : route('admin.winners.store');
    form.post(url);
};
</script>
<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
            <Label for="user_id">Usuario Ganador</Label>
            <select v-model="form.user_id" id="user_id" class="flex h-10 w-full rounded-md border ...">
                <option value="">Selecciona un usuario</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                    {{ user.nombres }} {{ user.apellidos }}
                </option>
            </select>
            <InputError :message="form.errors.user_id" />
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
            <Input id="photo" type="file" @input="form.photo = $event.target.files[0]" />
            <InputError :message="form.errors.photo" />
        </div>
        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ winner ? 'Actualizar' : 'Guardar' }} Ganador
            </Button>
        </div>
    </form>
</template>