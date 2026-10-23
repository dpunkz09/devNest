<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as ordersIndex } from '@/routes/admin/orders';
import type { Order, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Orders', href: ordersIndex() },
        ],
    },
});

defineProps<{
    orders: Paginated<Order>;
}>();

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    paid: 'default',
    pending: 'secondary',
    refunded: 'outline',
    cancelled: 'outline',
};
</script>

<template>
    <Head title="Orders" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Orders</h1>
            <p class="text-sm text-muted-foreground">
                Every order placed on the storefront.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>All orders</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="order in orders.data"
                        :key="order.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <p class="font-medium">
                                {{ order.reference }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ order.product?.name }} ·
                                {{ order.customer.name }} ·
                                {{
                                    order.created_at
                                        ? new Date(
                                              order.created_at,
                                          ).toLocaleDateString()
                                        : ''
                                }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium">
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
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
