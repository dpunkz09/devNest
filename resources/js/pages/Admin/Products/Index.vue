<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Archive, ArchiveRestore } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as productsIndex } from '@/routes/admin/products';
import { destroy as productDestroy } from '@/routes/admin/products';
import { restore as productRestore } from '@/routes/admin/products';
import type { Paginated, Product } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Apps', href: productsIndex() },
        ],
    },
});

defineProps<{
    products: Paginated<Product>;
}>();

const archive = (product: Product) => {
    if (!confirm(`Archive ${product.name}?`)) return;
    router.delete(productDestroy.url({ product: product.id }));
};

const restore = (product: Product) => {
    router.post(productRestore.url({ product: product.id }));
};
</script>

<template>
    <Head title="Apps" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Apps</h1>
            <p class="text-sm text-muted-foreground">
                Every app on the storefront, including archived ones.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>All apps</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="product in products.data"
                        :key="product.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">
                                    {{ product.name }}
                                </span>
                                <Badge
                                    :variant="
                                        product.archived
                                            ? 'outline'
                                            : 'secondary'
                                    "
                                >
                                    {{ product.status }}
                                </Badge>
                                <span
                                    v-if="product.archived"
                                    class="text-xs text-muted-foreground"
                                >
                                    archived
                                </span>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                by {{ product.seller.name }} ·
                                {{ product.sales }} sales
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                v-if="product.archived"
                                variant="outline"
                                size="sm"
                                @click="restore(product)"
                            >
                                <ArchiveRestore class="size-4" />
                                Restore
                            </Button>
                            <Button
                                v-else
                                variant="ghost"
                                size="icon-sm"
                                @click="archive(product)"
                            >
                                <Archive class="size-4" />
                            </Button>
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
