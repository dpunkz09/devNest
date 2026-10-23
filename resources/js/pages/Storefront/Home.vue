<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Boxes, Sparkles } from '@lucide/vue';
import PlatformIcon from '@/components/PlatformIcon.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';
import { index as appsIndex } from '@/routes/apps';
import type { Product, Tag } from '@/types';

type PlatformOption = {
    value: string;
    label: string;
    short_label: string;
    icon: string;
};

defineProps<{
    featured: Product[];
    latest: Product[];
    platforms: PlatformOption[];
    tags: Tag[];
    stats: { apps: number; platforms: number };
}>();
</script>

<template>
    <Head title="Discover apps from independent developers" />

    <section class="border-b bg-gradient-to-b from-muted/50 to-background">
        <div
            class="mx-auto grid max-w-7xl gap-12 px-4 py-16 md:px-6 md:py-24 lg:grid-cols-2 lg:items-center"
        >
            <div class="space-y-6">
                <Badge variant="outline" class="gap-1.5">
                    <Sparkles class="size-3.5" />
                    A marketplace for indie developers
                </Badge>

                <h1
                    class="text-4xl font-bold tracking-tight text-balance sm:text-5xl lg:text-6xl"
                >
                    Discover apps built by
                    <span class="text-muted-foreground">
                        independent developers.
                    </span>
                </h1>

                <p class="max-w-xl text-lg text-pretty text-muted-foreground">
                    Browse, try and buy web, desktop and mobile software from
                    developers around the world. Every purchase is a one-time
                    payment with a lifetime license and instant download.
                </p>

                <div class="flex flex-wrap gap-3">
                    <Button size="lg" as-child>
                        <Link :href="appsIndex()">
                            Browse the catalogue
                            <ArrowRight />
                        </Link>
                    </Button>
                    <Button size="lg" variant="outline" as-child>
                        <Link :href="dashboard()">Sell your apps</Link>
                    </Button>
                </div>

                <dl class="flex flex-wrap gap-8 pt-4">
                    <div>
                        <dt class="text-sm text-muted-foreground">
                            Published apps
                        </dt>
                        <dd class="text-2xl font-semibold">
                            {{ stats.apps }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted-foreground">Platforms</dt>
                        <dd class="text-2xl font-semibold">
                            {{ stats.platforms }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-muted-foreground">
                            Lifetime updates
                        </dt>
                        <dd class="text-2xl font-semibold">Included</dd>
                    </div>
                </dl>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-2">
                <Link
                    v-for="platform in platforms"
                    :key="platform.value"
                    :href="
                        appsIndex({
                            query: { platform: platform.value },
                        })
                    "
                    class="group rounded-xl border bg-card p-5 transition-all hover:-translate-y-0.5 hover:shadow-md"
                >
                    <div
                        class="flex size-10 items-center justify-center rounded-lg bg-muted transition-colors group-hover:bg-primary group-hover:text-primary-foreground"
                    >
                        <PlatformIcon :platform="platform.icon" />
                    </div>
                    <p class="mt-4 font-medium">{{ platform.label }}</p>
                    <p class="text-xs text-muted-foreground">
                        Explore {{ platform.short_label }} apps
                    </p>
                </Link>
            </div>
        </div>
    </section>

    <section
        v-if="featured.length"
        class="mx-auto max-w-7xl px-4 py-16 md:px-6"
    >
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight">
                    Featured apps
                </h2>
                <p class="text-sm text-muted-foreground">
                    The releases people love most right now.
                </p>
            </div>
            <Button variant="ghost" as-child>
                <Link :href="appsIndex()">
                    View all
                    <ArrowRight />
                </Link>
            </Button>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <ProductCard
                v-for="product in featured"
                :key="product.id"
                :product="product"
            />
        </div>
    </section>

    <section v-if="latest.length" class="border-y bg-muted/30">
        <div class="mx-auto max-w-7xl px-4 py-16 md:px-6">
            <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-semibold tracking-tight">
                        Latest releases
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Freshly shipped and ready to download.
                    </p>
                </div>
                <Button variant="ghost" as-child>
                    <Link
                        :href="
                            appsIndex({
                                query: { sort: 'newest' },
                            })
                        "
                    >
                        See everything new
                        <ArrowRight />
                    </Link>
                </Button>
            </div>

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <ProductCard
                    v-for="product in latest"
                    :key="product.id"
                    :product="product"
                />
            </div>
        </div>
    </section>

    <section v-if="tags.length" class="mx-auto max-w-7xl px-4 py-16 md:px-6">
        <div class="mb-6 flex items-center gap-2">
            <Boxes class="size-5" />
            <h2 class="text-2xl font-semibold tracking-tight">
                Browse by category
            </h2>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="tag in tags"
                :key="tag.id"
                variant="outline"
                size="sm"
                as-child
            >
                <Link
                    :href="
                        appsIndex({
                            query: { tag: tag.slug },
                        })
                    "
                >
                    {{ tag.name }}
                </Link>
            </Button>
        </div>
    </section>
</template>
