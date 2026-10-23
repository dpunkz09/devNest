<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, DollarSign, Eye, Package, ShoppingBag } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import {
    index as ordersIndex,
    show as orderShow,
} from '@/routes/dashboard/orders';
import {
    create as productCreate,
    edit as productEdit,
    index as productsIndex,
} from '@/routes/dashboard/products';
import type { Order, Product } from '@/types';

type Stats = {
    products: number;
    published: number;
    sales: number;
    revenue: number;
    revenue_formatted: string;
    views: number;
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

defineProps<{
    stats: Stats;
    recentOrders: Order[];
    topProducts: Product[];
}>();

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    paid: 'default',
    pending: 'secondary',
    refunded: 'outline',
    cancelled: 'outline',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Your storefront
                </h1>
                <p class="text-sm text-muted-foreground">
                    An overview of the apps you publish and the sales they
                    generate.
                </p>
            </div>

            <Button as-child>
                <Link :href="productCreate()">
                    <Package />
                    New app
                </Link>
            </Button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <Card>
                <CardHeader>
                    <CardDescription>Apps</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ stats.products }}
                    </CardTitle>
                </CardHeader>
                <CardContent class="text-sm text-muted-foreground">
                    {{ stats.published }} published
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription>Sales</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ stats.sales }}
                    </CardTitle>
                </CardHeader>
                <CardContent
                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                >
                    <ShoppingBag class="size-4" />
                    Lifetime orders
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription>Revenue</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ stats.revenue_formatted }}
                    </CardTitle>
                </CardHeader>
                <CardContent
                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                >
                    <DollarSign class="size-4" />
                    From paid orders
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardDescription>Views</CardDescription>
                    <CardTitle class="text-3xl">
                        {{ stats.views }}
                    </CardTitle>
                </CardHeader>
                <CardContent
                    class="flex items-center gap-1.5 text-sm text-muted-foreground"
                >
                    <Eye class="size-4" />
                    Across all listings
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-6 lg:grid-cols-5">
            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle>Recent sales</CardTitle>
                    <CardDescription>
                        The latest orders placed for your apps.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="recentOrders.length" class="divide-y">
                        <Link
                            v-for="order in recentOrders"
                            :key="order.id"
                            :href="orderShow({ order: order.id })"
                            class="flex items-center justify-between gap-4 py-3 transition-colors first:pt-0 last:pb-0 hover:opacity-80"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ order.product?.name ?? 'Removed app' }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ order.customer.name }} ·
                                    {{ order.reference }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span class="text-sm font-semibold">
                                    {{ order.amount_formatted }}
                                </span>
                                <Badge
                                    :variant="
                                        statusVariant[order.status] ?? 'outline'
                                    "
                                >
                                    {{ order.status_label }}
                                </Badge>
                            </div>
                        </Link>
                    </div>

                    <p
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        No sales yet. Publish an app to get started.
                    </p>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Top apps</CardTitle>
                    <CardDescription>
                        Your best performing listings.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="topProducts.length" class="divide-y">
                        <Link
                            v-for="product in topProducts"
                            :key="product.id"
                            :href="productEdit({ product: product.id })"
                            class="flex items-center justify-between gap-4 py-3 transition-colors first:pt-0 last:pb-0 hover:opacity-80"
                        >
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">
                                    {{ product.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    {{ product.platform_label }} ·
                                    {{ product.sales }} sold
                                </p>
                            </div>

                            <span class="shrink-0 text-sm font-semibold">
                                {{ product.price_formatted }}
                            </span>
                        </Link>
                    </div>

                    <p
                        v-else
                        class="py-6 text-center text-sm text-muted-foreground"
                    >
                        You have not published any app yet.
                    </p>

                    <Button
                        variant="outline"
                        size="sm"
                        class="mt-4 w-full"
                        as-child
                    >
                        <Link :href="productsIndex()">
                            Manage apps
                            <ArrowRight />
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
