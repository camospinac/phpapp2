import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface Rank {
    id: number;
    name: string;
    
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
};

export interface Rank {
    id: number;
    name: string;
    required_referrals: number;
    reward_description: string;
    reward_percentage: number;
    is_active: boolean;
}

export interface User {
    id: string;
    nombres: string;
    apellidos: string;
    celular: string;
    referral_code: string | null;
    email: string;
    rol: 'admin' | 'usuario' | 'asesor';
    rank: Rank | null;
    referral_count: number;
    next_rank: {
        name: string;
        required_referrals: number;
    } | null;
    // No incluimos password por seguridad
}

export interface Transaction {
    id: string;
    id_user: string;
    tipo: 'abono' | 'retiro';
    monto: number;
    observacion: string;
    created_at: string;
    updated_at: string;
    user?: User;
}

export type BreadcrumbItemType = BreadcrumbItem;
