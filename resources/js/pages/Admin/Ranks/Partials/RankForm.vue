<script setup lang="ts">
import { h } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// Usaremos el elemento nativo <textarea> en lugar de un componente Textarea externo
import { Checkbox } from '@/components/ui/checkbox'; // Checkbox para 'is_active'
import { type Rank } from '@/types';

// Componente local de InputError (como en tu ejemplo)
const InputError = (props: { message?: string | string[] | null }) => {
    if (!props.message) return null;
    const text = Array.isArray(props.message) ? props.message.join(', ') : (props.message as string);
    return h('p', { class: 'text-sm text-red-600' }, text);
};

const props = defineProps<{
    rank?: Rank;
}>();

const form = useForm({
    _method: props.rank ? 'PATCH' : 'POST',
    name: props.rank?.name ?? '',
    required_referrals: props.rank?.required_referrals ?? 0,
    reward_description: props.rank?.reward_description ?? '',
    reward_amount: props.rank?.reward_amount ?? 0,
    is_active: props.rank?.is_active ?? true,
});

const submit = () => {
    const url = props.rank
        ? route('admin.ranks.update', props.rank.id)
        : route('admin.ranks.store');

    form.post(url);
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">

        <div class="grid gap-2">
            <Label for="name">Nombre del Rango</Label>
            <Input id="name" type="text" v-model="form.name" placeholder="Ej: Bronce" />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-2">
            <Label for="required_referrals">Referidos Requeridos</Label>
            <Input id="required_referrals" type="number" v-model="form.required_referrals" />
            <InputError :message="form.errors.required_referrals" />
        </div>

        <div class="grid gap-2">
            <Label for="reward_amount">Monto de Recompensa (Valor Fijo)</Label>
            <Input id="reward_amount" type="number" step="0.01" v-model="form.reward_amount" placeholder="Ej: 100000" />
            <InputError :message="form.errors.reward_amount" />
        </div>

        <div class="grid gap-2">
            <Label for="reward_description">Descripción</Label>
            <textarea id="reward_description" v-model="form.reward_description"
                placeholder="Ej: Bono del 10% sobre la inversión..."
                class="w-full rounded-md border bg-transparent px-3 py-2 text-sm focus:outline-none"></textarea>
            <InputError :message="form.errors.reward_description" />
        </div>

        <div class="flex items-center space-x-2">
            <Checkbox id="is_active" :checked="form.is_active" @update:checked="form.is_active = $event as boolean" />
            <Label for="is_active" class="font-normal">
                Rango Activo
            </Label>
            <InputError :message="form.errors.is_active" />
        </div>

        <div class="flex justify-end">
            <Button type="submit" :disabled="form.processing">
                {{ rank ? 'Actualizar' : 'Guardar' }} Rango
            </Button>
        </div>
    </form>
</template>