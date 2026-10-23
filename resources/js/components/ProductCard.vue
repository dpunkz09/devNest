<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Download, Eye } from '@lucide/vue';
import { computed } from 'vue';
import PlatformIcon from '@/components/PlatformIcon.vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardFooter,
    CardHeader,
} from '@/components/ui/card';
import { show as appShow } from '@/routes/apps';
import type { Product } from '@/types';

const props = defineProps<{
    product: Product;
}>();

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
    <Card
        class="group flex flex-col overflow-hidden transition-shadow hover:shadow-md"
    >
        <Link
            :href="appShow({ slug: product.slug })"
            class="relative block aspect-video overflow-hidden bg-gradient-to-br from-neutral-200 to-neutral-300 dark:from-neutral-800 dark:to-neutral-950"
        >
            <img
                v-if="product.cover_image_url"
                :src="product.cover_image_url"
                :alt="product.name"
                class="size-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="flex size-full items-center justify-center text-4xl font-semibold tracking-tight text-neutral-500 dark:text-neutral-400"
            >
                {{ initials }}
            </div>
            <Badge
                variant="secondary"
                class="absolute top-3 left-3 gap-1 bg-white/90 text-neutral-900 backdrop-blur dark:bg-neutral-950/80 dark:text-neutral-100"
            >
                <PlatformIcon :platform="product.platform_icon" />
                {{ product.platform_label }}
            </Badge>
        </Link>

        <CardHeader class="gap-1 pb-2">
            <Link
                :href="appShow({ slug: product.slug })"
                class="text-base leading-tight font-semibold hover:underline"
            >
                {{ product.name }}
            </Link>
            <p class="line-clamp-2 text-sm text-muted-foreground">
                {{ product.tagline }}
            </p>
        </CardHeader>

        <CardContent class="flex flex-wrap gap-1.5 pb-3">
            <Badge
                v-for="tag in product.tags?.slice(0, 3)"
                :key="tag.id"
                variant="outline"
                class="font-normal"
            >
                {{ tag.name }}
            </Badge>
        </CardContent>

        <CardFooter class="mt-auto items-center justify-between border-t pt-4">
            <span class="text-sm font-semibold">{{
                product.price_formatted
            }}</span>
            <span class="flex items-center gap-3 text-xs text-muted-foreground">
                <span class="flex items-center gap-1">
                    <Download class="size-3.5" />
                    {{ product.sales }}
                </span>
                <span class="flex items-center gap-1">
                    <Eye class="size-3.5" />
                    {{ product.views }}
                </span>
            </span>
        </CardFooter>
    </Card>
</template>
