<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type Rank, type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import RankForm from './Partials/RankForm.vue';

const props = defineProps<{
    rank: Rank;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Rangos', href: route('admin.ranks.index') },
    { title: `Editar: ${props.rank.name}`, href: route('admin.ranks.edit', props.rank.id) },
];
</script>

<template>
    <Head :title="`Editar Rango: ${rank.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs" :is-admin="true">
        <div class="max-w-2xl mx-auto">
            <Card>
                <CardHeader>
                    <CardTitle>Editar Rango: {{ rank.name }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <RankForm :rank="rank" />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>