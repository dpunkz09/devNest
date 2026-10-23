<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { KeyRound, ShoppingBag } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { index as appsIndex } from '@/routes/apps';
import {
    index as purchasesIndex,
    show as purchaseShow,
} from '@/routes/purchases';
import type { Order, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'My purchases',
                href: purchasesIndex(),
            },
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
    <Head title="My purchases" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Your library</h1>
            <p class="text-sm text-muted-foreground">
                Every app you bought, with its license key and download link.
            </p>
        </div>

        <div v-if="orders.data.length" class="grid gap-4">
            <Card v-for="order in orders.data" :key="order.id">
                <CardContent class="flex flex-wrap items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="purchaseShow({ order: order.id })"
                                class="truncate font-medium hover:underline"
                            >
                                {{ order.product?.name ?? 'Removed app' }}
                            </Link>
                            <Badge
                                :variant="
                                    statusVariant[order.status] ?? 'outline'
                                "
                            >
                                {{ order.status_label }}
                            </Badge>
                        </div>

                        <p class="text-sm text-muted-foreground">
                            {{
                                order.product?.platform_label ??
                                'Unknown platform'
                            }}
                            ·
                            <span class="font-mono text-xs">
                                {{ order.reference }}
                            </span>
                        </p>

                        <p
                            v-if="order.license"
                            class="mt-1 flex items-center gap-1.5 font-mono text-xs text-muted-foreground"
                        >
                            <KeyRound class="size-3.5" />
                            {{ order.license.key }}
                        </p>
                    </div>

                    <div class="flex shrink-0 items-center gap-3">
                        <span class="text-sm font-semibold">
                            {{ order.amount_formatted }}
                        </span>
                        <Button variant="outline" size="sm" as-child>
                            <Link :href="purchaseShow({ order: order.id })">
                                Details
                            </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-16 text-center"
        >
            <ShoppingBag class="size-10 text-muted-foreground" />
            <p class="font-medium">You have not bought anything yet</p>
            <p class="text-sm text-muted-foreground">
                Browse the catalogue and pick up an app for your next project.
            </p>
            <Button variant="outline" as-child>
                <Link :href="appsIndex()">Browse apps</Link>
            </Button>
        </div>

        <nav
            v-if="orders.links.length > 3"
            class="flex flex-wrap items-center justify-center gap-1"
            aria-label="Pagination"
        >
            <template v-for="link in orders.links" :key="link.label">
                <span
                    v-if="!link.url"
                    class="rounded-md px-3 py-2 text-sm text-muted-foreground"
                    v-html="link.label"
                />
                <Link
                    v-else
                    :href="link.url"
                    :class="[
                        'rounded-md px-3 py-2 text-sm transition-colors',
                        link.active
                            ? 'bg-primary text-primary-foreground'
                            : 'hover:bg-accent',
                    ]"
                    v-html="link.label"
                />
            </template>
        </nav>
    </div>
</template>
