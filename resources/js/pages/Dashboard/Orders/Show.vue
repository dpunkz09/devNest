<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, KeyRound, Package } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { dashboard } from '@/routes';
import {
    index as ordersIndex,
    show as orderShow,
} from '@/routes/dashboard/orders';
import type { Order } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
            {
                title: 'Sales',
                href: ordersIndex(),
            },
        ],
    },
});

defineProps<{
    order: Order;
}>();

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    paid: 'default',
    pending: 'secondary',
    refunded: 'outline',
    cancelled: 'outline',
};
</script>

<template>
    <Head :title="`Order ${order.reference}`" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="space-y-1">
            <Link
                :href="ordersIndex()"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to sales
            </Link>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">
                    Order {{ order.reference }}
                </h1>
                <Badge :variant="statusVariant[order.status] ?? 'outline'">
                    {{ order.status_label }}
                </Badge>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <CardTitle>Order details</CardTitle>
                    <CardDescription>
                        Who bought the app and when.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Customer</dt>
                            <dd class="font-medium">
                                {{ order.customer.name }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Amount</dt>
                            <dd class="font-medium">
                                {{ order.amount_formatted }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Placed</dt>
                            <dd class="font-medium">
                                {{
                                    order.created_at
                                        ? new Date(
                                              order.created_at,
                                          ).toLocaleString()
                                        : '—'
                                }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-muted-foreground">Paid</dt>
                            <dd class="font-medium">
                                {{
                                    order.paid_at
                                        ? new Date(
                                              order.paid_at,
                                          ).toLocaleString()
                                        : '—'
                                }}
                            </dd>
                        </div>
                    </dl>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>App</CardTitle>
                    <CardDescription>
                        The listing this order belongs to.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="order.product" class="flex items-center gap-3">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-neutral-200 to-neutral-300 dark:from-neutral-800 dark:to-neutral-950"
                        >
                            <img
                                v-if="order.product.cover_image_url"
                                :src="order.product.cover_image_url"
                                :alt="order.product.name"
                                class="size-full object-cover"
                            />
                            <Package v-else class="size-5 text-neutral-500" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium">
                                {{ order.product.name }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                {{ order.product.platform_label }}
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <div v-if="order.license" class="space-y-2">
                        <p
                            class="flex items-center gap-1.5 text-sm font-medium"
                        >
                            <KeyRound class="size-4" />
                            License key
                        </p>
                        <code
                            class="block rounded-md bg-muted px-3 py-2 font-mono text-xs break-all"
                        >
                            {{ order.license.key }}
                        </code>
                        <p class="text-xs text-muted-foreground">
                            Downloaded
                            {{ order.license.downloads }}
                            {{
                                order.license.downloads === 1 ? 'time' : 'times'
                            }}
                        </p>
                    </div>

                    <p v-else class="text-sm text-muted-foreground">
                        No license has been issued for this order yet.
                    </p>

                    <Button variant="outline" size="sm" class="w-full" as-child>
                        <Link :href="orderShow({ order: order.id })">
                            Refresh
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
