<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    ClipboardList,
    FileText,
    LayoutGrid,
    Package,
    Receipt,
    Settings,
    Users,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
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
import { dashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { index as productsIndex } from '@/routes/admin/products';
import { index as auditLogIndex } from '@/routes/admin/audit-log';
import { index as ordersIndex } from '@/routes/admin/orders';
import { index as pagesIndex } from '@/routes/admin/pages';
import { edit as settingsEdit } from '@/routes/admin/settings';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', href: dashboard(), icon: LayoutGrid },
    { title: 'Users', href: usersIndex(), icon: Users },
    { title: 'Apps', href: productsIndex(), icon: Package },
    { title: 'Orders', href: ordersIndex(), icon: Receipt },
    { title: 'Pages', href: pagesIndex(), icon: FileText },
    { title: 'Audit log', href: auditLogIndex(), icon: ClipboardList },
    { title: 'Settings', href: settingsEdit(), icon: Settings },
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
                            <span
                                class="text-xs font-medium text-muted-foreground"
                            >
                                Admin
                            </span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
