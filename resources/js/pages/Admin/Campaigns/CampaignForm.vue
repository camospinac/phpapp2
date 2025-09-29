<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

import InputError from '@/components/InputError.vue';

const props = defineProps<{ campaign?: any }>();
const form = useForm({
    _method: props.campaign ? 'PATCH' : 'POST',
    name: props.campaign?.name ?? '',
    title: props.campaign?.title ?? '',
    content: props.campaign?.content ?? '',
    is_active: props.campaign?.is_active ?? false,
    image: null as File | null,
});
const submit = () => {
    const url = props.campaign ? route('admin.campaigns.update', props.campaign.id) : route('admin.campaigns.store');
    form.post(url);
};
</script>
<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
            <Label for="name">Nombre Interno</Label>
            <Input id="name" v-model="form.name" />
            <InputError :message="form.errors.name" />
        </div>
        <div class="grid gap-2">
            <Label for="title">Título Visible</Label>
            <Input id="title" v-model="form.title" />
            <InputError :message="form.errors.title" />
        </div>
        <div class="grid gap-2">
            <Label for="content">Contenido</Label>
            <textarea 
                id="content" 
                v-model="form.content"
                rows="4"
                class="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
            ></textarea>
            <InputError :message="form.errors.content" />
        </div>
        <div class="grid gap-2">
            <Label for="image">Imagen (Opcional)</Label>
            <Input id="image" type="file" @input="form.image = $event.target.files[0]" />
            <InputError :message="form.errors.image" />
        </div>
        <div class="flex items-center space-x-3">
    <input 
        type="checkbox" 
        id="is_active" 
        :checked="form.is_active"
        @change="form.is_active = ($event.target as HTMLInputElement).checked"
        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary"
    />
    <Label for="is_active">Activar Campaña</Label>
    <InputError :message="form.errors.is_active" />
</div>
        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">{{ campaign ? 'Actualizar' : 'Crear' }} Campaña</Button>
        </div>
    </form>
</template>