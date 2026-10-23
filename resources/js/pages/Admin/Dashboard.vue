<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Package, Receipt, Users as UsersIcon } from '@lucide/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { index as productsIndex } from '@/routes/admin/products';
import { index as ordersIndex } from '@/routes/admin/orders';
import type { Order, Product, User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Admin', href: adminDashboard() }],
    },
});

defineProps<{
    stats: {
        users: number;
        admins: number;
        products: number;
        published: number;
        orders: number;
        paid_orders: number;
        revenue: number;
        revenue_formatted: string;
    };
    recentUsers: User[];
    recentOrders: Order[];
    topProducts: Product[];
}>();
</script>

<template>
    <Head title="Admin dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Admin dashboard
            </h1>
            <p class="text-sm text-muted-foreground">
                A view over the whole storefront.
            </p>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">Users</CardTitle>
                    <UsersIcon class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ stats.users }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ stats.admins }} administrators
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">Apps</CardTitle>
                    <Package class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ stats.products }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ stats.published }} published
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">Orders</CardTitle>
                    <Receipt class="size-4 text-muted-foreground" />
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ stats.orders }}</p>
                    <p class="text-xs text-muted-foreground">
                        {{ stats.paid_orders }} paid
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader
                    class="flex flex-row items-center justify-between pb-2"
                >
                    <CardTitle class="text-sm font-medium">Revenue</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">
                        {{ stats.revenue_formatted }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        from paid orders
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Recent users</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="divide-y">
                        <li
                            v-for="user in recentUsers"
                            :key="user.id"
                            class="flex items-center justify-between py-2 text-sm"
                        >
                            <div>
                                <p class="font-medium">{{ user.name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ user.email }}
                                </p>
                            </div>
                            <span
                                v-if="user.is_admin"
                                class="text-xs font-medium text-primary"
                            >
                                admin
                            </span>
                        </li>
                    </ul>
                    <Link
                        :href="usersIndex()"
                        class="mt-3 inline-block text-sm text-primary hover:underline"
                    >
                        View all users
                    </Link>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Recent orders</CardTitle>
                </CardHeader>
                <CardContent>
                    <ul class="divide-y">
                        <li
                            v-for="order in recentOrders"
                            :key="order.id"
                            class="flex items-center justify-between py-2 text-sm"
                        >
                            <div>
                                <p class="font-medium">
                                    {{ order.reference }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.product?.name }} ·
                                    {{ order.customer.name }}
                                </p>
                            </div>
                            <span class="text-xs font-medium">
                                {{ order.amount_formatted }}
                            </span>
                        </li>
                    </ul>
                    <Link
                        :href="ordersIndex()"
                        class="mt-3 inline-block text-sm text-primary hover:underline"
                    >
                        View all orders
                    </Link>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
