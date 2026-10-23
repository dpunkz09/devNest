<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingBag } from '@lucide/vue';
import { computed, reactive, watch } from 'vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { dashboard } from '@/routes';
import {
    index as ordersIndex,
    show as orderShow,
} from '@/routes/dashboard/orders';
import type { Order, Paginated } from '@/types';

type StatusOption = {
    value: string;
    label: string;
};

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

const props = defineProps<{
    orders: Paginated<Order>;
    filters: {
        status: string | null;
    };
    statuses: StatusOption[];
}>();

const filters = reactive({ ...props.filters });

watch(
    () => props.filters,
    (value) => {
        Object.assign(filters, value);
    },
);

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    paid: 'default',
    pending: 'secondary',
    refunded: 'outline',
    cancelled: 'outline',
};

const hasFilters = computed(() => Boolean(filters.status));

function apply(status: string | null): void {
    router.get(
        ordersIndex().url,
        { status },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}
</script>

<template>
    <Head title="Sales" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Your sales</h1>
            <p class="text-sm text-muted-foreground">
                Every order placed for the apps you publish.
            </p>
        </div>

        <Card>
            <CardContent class="space-y-4">
                <div class="flex flex-wrap items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :class="
                            !filters.status
                                ? 'border-primary bg-primary text-primary-foreground'
                                : ''
                        "
                        @click="apply(null)"
                    >
                        All orders
                    </Button>
                    <Button
                        v-for="status in statuses"
                        :key="status.value"
                        variant="outline"
                        size="sm"
                        :class="
                            filters.status === status.value
                                ? 'border-primary bg-primary text-primary-foreground'
                                : ''
                        "
                        @click="apply(status.value)"
                    >
                        {{ status.label }}
                    </Button>
                </div>

                <p v-if="hasFilters" class="text-sm text-muted-foreground">
                    Showing
                    <span class="font-medium text-foreground">
                        {{
                            statuses.find(
                                (status) => status.value === filters.status,
                            )?.label ?? filters.status
                        }}
                    </span>
                    orders.
                </p>
            </CardContent>
        </Card>

        <div v-if="orders.data.length" class="grid gap-4">
            <Card v-for="order in orders.data" :key="order.id">
                <CardContent class="flex flex-wrap items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="orderShow({ order: order.id })"
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
                            {{ order.customer.name }} ·
                            <span class="font-mono text-xs">
                                {{ order.reference }}
                            </span>
                        </p>

                        <p
                            v-if="order.created_at"
                            class="text-xs text-muted-foreground"
                        >
                            {{
                                new Date(order.created_at).toLocaleDateString()
                            }}
                        </p>
                    </div>

                    <span class="shrink-0 text-sm font-semibold">
                        {{ order.amount_formatted }}
                    </span>
                </CardContent>
            </Card>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-16 text-center"
        >
            <ShoppingBag class="size-10 text-muted-foreground" />
            <p class="font-medium">No orders found</p>
            <p class="text-sm text-muted-foreground">
                Once someone buys one of your apps it will show up here.
            </p>
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
