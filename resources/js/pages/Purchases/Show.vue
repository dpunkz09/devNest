<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    CreditCard,
    Download,
    KeyRound,
    Package,
} from '@lucide/vue';
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
import {
    download as purchaseDownload,
    index as purchasesIndex,
    pay as purchasePay,
} from '@/routes/purchases';
import type { Order } from '@/types';

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
                :href="purchasesIndex()"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to my purchases
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

        <div class="grid gap-6 lg:grid-cols-5">
            <Card class="lg:col-span-3">
                <CardHeader>
                    <CardTitle>{{ order.product?.name }}</CardTitle>
                    <CardDescription>
                        {{
                            order.product?.tagline ??
                            'Thanks for supporting an independent developer.'
                        }}
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
                        <div class="text-sm">
                            <p class="font-medium">
                                {{ order.product.platform_label }}
                            </p>
                            <p class="text-muted-foreground">
                                By {{ order.product.seller.name }}
                            </p>
                        </div>
                    </div>

                    <Separator />

                    <dl class="space-y-3 text-sm">
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
                    </dl>

                    <div
                        v-if="order.product?.features?.length"
                        class="space-y-2"
                    >
                        <p class="text-sm font-medium">What you get</p>
                        <ul class="space-y-1.5 text-sm text-muted-foreground">
                            <li
                                v-for="feature in order.product.features"
                                :key="feature.id"
                                class="flex items-start gap-2"
                            >
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-primary"
                                />
                                {{ feature.title }}
                            </li>
                        </ul>
                    </div>
                </CardContent>
            </Card>

            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>Your license</CardTitle>
                    <CardDescription>
                        The key and the download for this order.
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <template v-if="order.status === 'pending'">
                        <div
                            class="rounded-lg border border-dashed p-4 text-center"
                        >
                            <CreditCard
                                class="mx-auto size-6 text-muted-foreground"
                            />
                            <p class="mt-2 text-sm font-medium">
                                Payment pending
                            </p>
                            <p class="mt-1 text-sm text-muted-foreground">
                                Pay with PayPal to receive your license key.
                            </p>
                        </div>

                        <Form
                            :action="purchasePay.url({ order: order.id })"
                            method="post"
                            v-slot="{ processing }"
                        >
                            <Button
                                type="submit"
                                class="w-full"
                                :disabled="processing"
                            >
                                <CreditCard />
                                {{
                                    processing
                                        ? 'Redirecting to PayPal...'
                                        : `Pay ${order.amount_formatted} with PayPal`
                                }}
                            </Button>
                        </Form>
                    </template>

                    <template v-else-if="order.status === 'paid'">
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
                                    order.license.downloads === 1
                                        ? 'time'
                                        : 'times'
                                }}
                            </p>
                        </div>

                        <Button class="w-full" as-child>
                            <a
                                :href="
                                    purchaseDownload.url({
                                        order: order.id,
                                    })
                                "
                            >
                                <Download />
                                Download the app
                            </a>
                        </Button>
                    </template>

                    <div
                        v-else
                        class="rounded-lg border border-dashed p-4 text-center"
                    >
                        <p class="text-sm font-medium">
                            <template v-if="order.refunded">
                                This order was refunded
                            </template>
                            <template v-else>
                                This order is
                                {{ order.status_label.toLowerCase() }}
                            </template>
                        </p>
                        <p class="mt-1 text-sm text-muted-foreground">
                            <template v-if="order.refunded">
                                The payment was returned and the license was
                                revoked.
                            </template>
                            <template v-else>
                                No license is attached to this order.
                            </template>
                        </p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
