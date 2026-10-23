<script setup lang="ts">
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Check,
    Code2,
    Download,
    ExternalLink,
    Eye,
    Play,
    Settings,
    Tag as TagIcon,
} from '@lucide/vue';
import { computed } from 'vue';
import JsonLd from '@/components/JsonLd.vue';
import PlatformIcon from '@/components/PlatformIcon.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { login } from '@/routes';
import { index as appsIndex, purchase } from '@/routes/apps';
import { edit as productEdit } from '@/routes/dashboard/products';
import { index as purchasesIndex } from '@/routes/purchases';
import type { Product } from '@/types';

const props = defineProps<{
    product: Product;
    related: Product[];
    owned: boolean;
}>();

const page = usePage();
const isAuthenticated = computed(() => Boolean(page.props.auth.user));
const isOwner = computed(
    () => page.props.auth.user?.id === props.product.user_id,
);

const initials = computed(() =>
    props.product.name
        .split(' ')
        .slice(0, 2)
        .map((word) => word.charAt(0))
        .join('')
        .toUpperCase(),
);
</script>

<template>
    <Head :title="product.name" />
    <JsonLd
        :data="{
            '@context': 'https://schema.org',
            '@type': 'Product',
            name: product.name,
            description: product.tagline,
            image: product.cover_image_url,
            offers: {
                '@type': 'Offer',
                price: (product.price / 100).toFixed(2),
                priceCurrency: product.currency,
                availability: 'https://schema.org/InStock',
            },
            brand: {
                '@type': 'Brand',
                name: product.seller.name,
            },
        }"
    />

    <div class="mx-auto max-w-7xl px-4 py-10 md:px-6">
        <Link
            :href="appsIndex()"
            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            All apps
        </Link>

        <div class="mt-6 grid gap-10 lg:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-10">
                <div class="space-y-5">
                    <div class="flex flex-wrap items-center gap-2">
                        <Badge variant="secondary" class="gap-1">
                            <PlatformIcon :platform="product.platform_icon" />
                            {{ product.platform_label }}
                        </Badge>
                        <Badge
                            v-for="tag in product.tags"
                            :key="tag.id"
                            variant="outline"
                            class="font-normal"
                        >
                            {{ tag.name }}
                        </Badge>
                    </div>

                    <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">
                        {{ product.name }}
                    </h1>

                    <p class="text-lg text-pretty text-muted-foreground">
                        {{ product.tagline }}
                    </p>

                    <p class="text-sm text-muted-foreground">
                        Built by
                        <span class="font-medium text-foreground">
                            {{ product.seller.name }}
                        </span>
                        <span class="mx-2">·</span>
                        <span class="inline-flex items-center gap-1">
                            <Eye class="size-3.5" />
                            {{ product.views }} views
                        </span>
                        <span class="mx-2">·</span>
                        <span class="inline-flex items-center gap-1">
                            <Download class="size-3.5" />
                            {{ product.sales }} sold
                        </span>
                    </p>
                </div>

                <div class="overflow-hidden rounded-xl border bg-muted/40">
                    <img
                        v-if="product.cover_image_url"
                        :src="product.cover_image_url"
                        :alt="product.name"
                        class="w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-neutral-200 to-neutral-300 text-6xl font-semibold text-neutral-500 dark:from-neutral-800 dark:to-neutral-950 dark:text-neutral-400"
                    >
                        {{ initials }}
                    </div>
                </div>

                <div
                    v-if="product.screenshots?.length"
                    class="grid gap-4 sm:grid-cols-2"
                >
                    <figure
                        v-for="screenshot in product.screenshots"
                        :key="screenshot.id"
                        class="overflow-hidden rounded-xl border"
                    >
                        <img
                            :src="screenshot.url"
                            :alt="screenshot.caption ?? product.name"
                            class="w-full object-cover"
                        />
                        <figcaption
                            v-if="screenshot.caption"
                            class="bg-muted/60 px-3 py-2 text-xs text-muted-foreground"
                        >
                            {{ screenshot.caption }}
                        </figcaption>
                    </figure>
                </div>

                <section class="space-y-3">
                    <h2 class="text-xl font-semibold tracking-tight">
                        About this app
                    </h2>
                    <p
                        class="leading-relaxed whitespace-pre-line text-muted-foreground"
                    >
                        {{ product.description }}
                    </p>
                </section>

                <section v-if="product.features?.length" class="space-y-4">
                    <h2 class="text-xl font-semibold tracking-tight">
                        What you get
                    </h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div
                            v-for="feature in product.features"
                            :key="feature.id"
                            class="rounded-lg border p-4"
                        >
                            <div class="flex items-start gap-2">
                                <Check
                                    class="mt-0.5 size-4 shrink-0 text-primary"
                                />
                                <div>
                                    <p class="font-medium">
                                        {{ feature.title }}
                                    </p>
                                    <p
                                        v-if="feature.description"
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        {{ feature.description }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="space-y-4">
                    <h2 class="text-xl font-semibold tracking-tight">
                        Details
                    </h2>
                    <dl class="grid gap-x-8 gap-y-3 sm:grid-cols-2">
                        <div class="flex justify-between gap-4 border-b pb-2">
                            <dt class="text-sm text-muted-foreground">
                                Platform
                            </dt>
                            <dd class="text-sm font-medium">
                                {{ product.platform_label }}
                            </dd>
                        </div>
                        <div
                            v-if="product.version"
                            class="flex justify-between gap-4 border-b pb-2"
                        >
                            <dt class="text-sm text-muted-foreground">
                                Version
                            </dt>
                            <dd class="text-sm font-medium">
                                {{ product.version }}
                            </dd>
                        </div>
                        <div
                            v-if="product.requirements"
                            class="flex justify-between gap-4 border-b pb-2"
                        >
                            <dt class="text-sm text-muted-foreground">
                                Requirements
                            </dt>
                            <dd class="text-sm font-medium">
                                {{ product.requirements }}
                            </dd>
                        </div>
                        <div
                            v-if="product.published_at"
                            class="flex justify-between gap-4 border-b pb-2"
                        >
                            <dt class="text-sm text-muted-foreground">
                                Released
                            </dt>
                            <dd class="text-sm font-medium">
                                {{
                                    new Date(
                                        product.published_at,
                                    ).toLocaleDateString()
                                }}
                            </dd>
                        </div>
                    </dl>
                </section>
            </div>

            <aside class="lg:sticky lg:top-24 lg:self-start">
                <Card>
                    <CardHeader>
                        <CardTitle class="text-2xl">
                            {{ product.price_formatted }}
                        </CardTitle>
                        <p class="text-sm text-muted-foreground">
                            One time payment, lifetime updates.
                        </p>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <Button
                            v-if="isOwner"
                            size="lg"
                            class="w-full"
                            as-child
                        >
                            <Link
                                :href="productEdit.url({ product: product.id })"
                            >
                                <Settings />
                                Manage this app
                            </Link>
                        </Button>

                        <Form
                            v-else-if="isAuthenticated && !owned"
                            :action="purchase.url({ slug: product.slug })"
                            method="post"
                        >
                            <Button type="submit" size="lg" class="w-full">
                                <Download />
                                {{
                                    product.is_free
                                        ? 'Get for free'
                                        : `Buy now for ${product.price_formatted}`
                                }}
                            </Button>
                        </Form>

                        <Button
                            v-else-if="owned"
                            size="lg"
                            class="w-full"
                            as-child
                        >
                            <Link :href="purchasesIndex()">
                                <Check />
                                You own this app
                            </Link>
                        </Button>

                        <Button v-else size="lg" class="w-full" as-child>
                            <Link :href="login()">
                                <Download />
                                Log in to buy
                            </Link>
                        </Button>

                        <Button
                            v-if="product.demo_url"
                            variant="outline"
                            class="w-full"
                            as-child
                        >
                            <a
                                :href="product.demo_url"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <Play />
                                Try the demo
                                <ExternalLink />
                            </a>
                        </Button>

                        <Separator />

                        <ul class="space-y-2 text-sm text-muted-foreground">
                            <li class="flex items-center gap-2">
                                <Check class="size-4 text-primary" />
                                Instant download after purchase
                            </li>
                            <li class="flex items-center gap-2">
                                <Check class="size-4 text-primary" />
                                License key issued instantly
                            </li>
                            <li
                                v-if="product.repository_url"
                                class="flex items-center gap-2"
                            >
                                <Code2 class="size-4 text-primary" />
                                <a
                                    :href="product.repository_url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="underline underline-offset-4 hover:text-foreground"
                                >
                                    View the source
                                </a>
                            </li>
                            <li
                                v-if="product.tags?.length"
                                class="flex items-center gap-2"
                            >
                                <TagIcon class="size-4 text-primary" />
                                {{
                                    product.tags
                                        .map((tag) => tag.name)
                                        .join(', ')
                                }}
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </aside>
        </div>

        <section v-if="related.length" class="mt-16 space-y-6">
            <h2 class="text-2xl font-semibold tracking-tight">
                More {{ product.platform_label.toLowerCase() }}s
            </h2>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ProductCard
                    v-for="item in related"
                    :key="item.id"
                    :product="item"
                />
            </div>
        </section>
    </div>
</template>
