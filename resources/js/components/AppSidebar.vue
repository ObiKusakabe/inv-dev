<script setup lang="ts">
// import NavFooter from '@/components/NavFooter.vue';
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
import  products  from '@/routes/products';
import  category  from '@/routes/category';
import  stockManagement  from '@/routes/stockManagement';
import  pos  from '@/routes/pos';
import  invoices  from '@/routes/invoices';
import  supplierData  from '@/routes/supplierData';
import  summary  from '@/routes/summary';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { ChartPie, Shirt, LayoutList, Layers, BadgeDollarSign, ScrollText, FileBox, NotebookTabs  } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

// const mainNavItems: NavItem[] = [
//     {
//         title: 'Dashboard',
//         href: dashboard(),
//         icon: ChartPie,
//     },
//     {
//         title: 'Pakaian',
//         href: products.index(),
//         icon: Shirt,
//     },
//     {
//         title: 'Kategori',
//         href: category.index(),
//         icon: LayoutList,
//     },
//     {
//         title: 'Manajemen Stok',
//         href: stockManagement.index(),
//         icon: Layers,
//     },
//     {
//         title: 'POS',
//         href: pos.index(),
//         icon: BadgeDollarSign,
//     },
//     {
//         title: 'Transaksi & Tagihan',
//         href: invoices.index(),
//         icon: ScrollText,
//     },
//     {
//         title: 'Data Supplier',
//         href: supplierData.index(),
//         icon: FileBox,
//     },
//     {
//         title: 'Laporan',
//         href: summary.index(),
//         icon: NotebookTabs,
//     },
// ];

type NavGroup = {
    label: string;
    items: NavItem[];
};

const mainNavGroups: NavGroup[] = [
    {
        label: 'Analitik',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: ChartPie,
            },
        ],
    },
    {
        label: 'Inventaris',
        items: [
            {
                title: 'Pakaian',
                href: products.index(),
                icon: Shirt,
            },
            {
                title: 'Manajemen Stok',
                href: stockManagement.index(),
                icon: Layers,
            },
            {
                title: 'Kategori',
                href: category.index(),
                icon: LayoutList,
            },
            {
                title: 'Data Supplier',
                href: supplierData.index(),
                icon: FileBox,
            },
        ],
    },
    {
        label: 'Penjualan',
        items: [
            {
                title: 'POS',
                href: pos.index(),
                icon: BadgeDollarSign,
            },
            {
                title: 'Transaksi & Tagihan',
                href: invoices.index(),
                icon: ScrollText,
            },
        ],
    },
    {
        label: 'Laporan',
        items: [
            {
                title: 'Laporan',
                href: summary.index(),
                icon: NotebookTabs,
            },
        ],
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
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
