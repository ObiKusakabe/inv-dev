<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index } from '@/routes/products';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, LayoutGrid, Shirt, LayoutList, Layers, BadgeDollarSign, Phone  } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Pakaian',
        href: index(),
        icon: Shirt,
    },
    {
        title: 'Kategori',
        href: '/Category',
        icon: LayoutList,
    },
    {
        title: 'Manajemen Stok',
        href: '/ManageStock',
        icon: Layers,
    },
    {
        title: 'POS',
        href: '/POS',
        icon: BadgeDollarSign,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Kontak Bantuan',
        href: 'https://wa.me/089506495890?text=Halo%20Kak,%20Saya ingin meminta bantuan mengenai fitur ...',
        icon: Phone,
    },
    {
        title: 'Panduan Web',
        href: 'https://github.com/ObiKusakabe/inv-dev',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                        <Link :href="index()">
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
