<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import RankForm from './Partials/RankForm.vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: route('admin.dashboard') },
    { title: 'Rangos', href: route('admin.ranks.index') },
    { title: 'Crear Nuevo', href: route('admin.ranks.create') },
];
</script>

<template>
    <Head title="Crear Rango" />

    <AppLayout :breadcrumbs="breadcrumbs" :is-admin="true">
        <div class="max-w-2xl mx-auto">
            <Card>
                <CardHeader>
                    <CardTitle>Crear Nuevo Rango</CardTitle>
                </CardHeader>
                <CardContent>
                    <RankForm />
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>