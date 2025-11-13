<script setup lang="ts">
import { computed } from 'vue'; // Import computed
import NavMain from '@/components/NavMain.vue';
import { Sidebar, SidebarContent, SidebarHeader, SidebarMenu, SidebarMenuItem, SidebarMenuButton } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { 
    LayoutGrid, 
    Users, 
    ArrowLeftRight,
    AreaChart,
    FileText, 
    Wallet, 
    CheckCircle,
    LineChart,
    Settings,
    LogOut,
    Shield,
} from 'lucide-vue-next';

// 1. Obtenemos los datos de la página
const page = usePage();
const user = computed(() => page.props.auth.user);

// 2. Creamos una propiedad computada para el menú, que reacciona a los cambios
const mainNavItems = computed<NavItem[]>(() => {
    // 1. Primero, verificamos si el usuario existe
    if (!user.value) return [];

    // 2. Lógica para roles de Staff (Admin y Asesor)
    if (user.value.rol === 'admin' || user.value.rol === 'asesor') {
        
        // Este es el menú base que AMBOS (admin y asesor) pueden ver
        const staffMenu: NavItem[] = [
            { title: 'Dashboard', href: route('admin.dashboard'), icon: LayoutGrid },
            { title: 'Aprobar Suscripciones', href: route('admin.subscriptions.pending'), icon: CheckCircle },
            { title: 'Gestionar Retiros', href: route('admin.withdrawals.index'), icon: Wallet },
            { title: 'Reporte Suscripciones', href: route('admin.reports.subscriptions'), icon: FileText },
            { title: 'Reporte Pagos', href: route('admin.reports.payments'), icon: FileText },
            { title: 'Reporte Retiros', href: route('admin.reports.withdrawals'), icon: FileText },
        ];

        // 3. Si es SÓLO admin, le añadimos los enlaces extra
        if (user.value.rol === 'admin') {
            staffMenu.push(
                { title: 'Gestionar Usuarios', href: route('admin.users.index'), icon: Users },
                { title: 'Flujo de Caja', href: route('admin.reports.dashboard'), icon: AreaChart },
                { title: 'Métodos de Pago', href: route('admin.payment-methods.index'), icon: Wallet },
                { title: 'Ganadores', href: route('admin.winners.index'), icon: ArrowLeftRight },
                { title: 'Gestionar Rangos', href: route('admin.ranks.index'), icon: Shield }
                // { title: 'Gestionar Rangos', href: route('admin.ranks.index'), icon: Shield }, // <-- Aquí irá tu CRUD
            );
        }

        return staffMenu;
    }

    // 4. Menú para usuarios normales (si no es admin ni asesor)
    return [
        { title: 'Inicio', href: route('dashboard'), icon: LayoutGrid },
        { title: 'Mis Referidos', href: route('referrals.index'), icon: Users },
        { title: 'Mercado', href: route('market.index'), icon: LineChart },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
       <SidebarHeader class="p-4">
    <Link :href="route('dashboard')" class="flex flex-col items-center gap-2">
        
        <div class="flex h-18 w-18 items-center justify-center rounded-lg overflow-hidden bg-white shadow-sm">
            <img 
                src="/img/icons/icon-72x72.png" 
                alt="Mi Logo"
                class="w-18 h-18 object-contain" 
            />
        </div>

        <span class="text-2xl font-bold text-foreground">
            EON
        </span>
        <span class="text-base font-bold text-foreground">
            Grupo Empresarial
        </span>

    </Link>
</SidebarHeader>

        <SidebarContent class="flex flex-col">
            <NavMain :items="mainNavItems" class="flex-grow" />
            
            <div v-if="user" class="mt-auto p-2 border-t">
                <div class="flex items-center gap-3 px-2 py-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-full bg-muted">
                        <Users class="h-5 w-5 text-muted-foreground" />
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-sm font-medium text-foreground truncate">{{ user.nombres }} {{ user.apellidos }}</span>
                        <div v-if="user.rank" class="flex items-center gap-1.5">
                            <span class="text-xs">🥉</span>
                            <span class="text-xs font-semibold text-primary">{{ user.rank.name }}</span>
                        </div>
                    </div>
                </div>
                <nav class="grid gap-1">
                    <Link :href="route('profile.edit')" class="w-full">
                        <Button variant="ghost" class="w-full justify-start text-sm">
                            
                            Ajustes de Perfil
                        </Button>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="w-full">
                        <Button variant="ghost" class="w-full justify-start text-sm">
                           
                            Cerrar Sesión
                        </Button>
                    </Link>
                </nav>
            </div>
            </SidebarContent>
        
        <SidebarFooter></SidebarFooter>

    </Sidebar>
    <div class="flex min-h-screen flex-col">
        <slot />
    </div>
</template>