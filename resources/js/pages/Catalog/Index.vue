<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { PackageSearch, Search, SlidersHorizontal } from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';
import PlatformIcon from '@/components/PlatformIcon.vue';
import ProductCard from '@/components/ProductCard.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { index as appsIndex } from '@/routes/apps';
import type { Paginated, Product, Tag } from '@/types';

type PlatformOption = {
    value: string;
    label: string;
    short_label: string;
    icon: string;
};

const props = defineProps<{
    products: Paginated<Product>;
    filters: {
        q: string | null;
        platform: string | null;
        tag: string | null;
        sort: string;
    };
    platforms: PlatformOption[];
    tags: Tag[];
}>();

const filters = reactive({ ...props.filters });
const search = ref(props.filters.q ?? '');

watch(
    () => props.filters,
    (value) => {
        Object.assign(filters, value);
        search.value = value.q ?? '';
    },
);

const sortOptions = [
    { value: 'newest', label: 'Newest first' },
    { value: 'popular', label: 'Most popular' },
    { value: 'price_low', label: 'Price: low to high' },
    { value: 'price_high', label: 'Price: high to low' },
    { value: 'name', label: 'Name (A–Z)' },
];

const activePlatform = computed(() =>
    props.platforms.find((platform) => platform.value === filters.platform),
);

function apply(partial: Record<string, string | null> = {}): void {
    router.get(
        appsIndex().url,
        { ...filters, ...partial },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
}

function submitSearch(): void {
    apply({ q: search.value.trim() || null });
}

function clearFilters(): void {
    router.get(
        appsIndex().url,
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head title="Browse apps" />

    <div class="mx-auto max-w-7xl px-4 py-12 md:px-6">
        <header class="mb-8 space-y-2">
            <h1 class="text-3xl font-bold tracking-tight">Browse apps</h1>
            <p class="text-muted-foreground">
                Every app I have published, across web, desktop and mobile.
            </p>
        </header>

        <div class="mb-8 space-y-4">
            <div class="flex flex-col gap-3 sm:flex-row">
                <form class="relative flex-1" @submit.prevent="submitSearch">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        v-model="search"
                        type="search"
                        placeholder="Search apps, tags or descriptions..."
                        class="h-10 pl-9"
                    />
                </form>

                <select
                    :value="filters.sort"
                    class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                    @change="
                        apply({
                            sort: ($event.target as HTMLSelectElement).value,
                        })
                    "
                >
                    <option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <Link
                    :href="
                        appsIndex({
                            query: { sort: filters.sort },
                        })
                    "
                    :class="[
                        'rounded-full border px-3 py-1.5 text-sm transition-colors',
                        !filters.platform
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'hover:bg-accent',
                    ]"
                >
                    All platforms
                </Link>

                <Link
                    v-for="platform in platforms"
                    :key="platform.value"
                    :href="
                        appsIndex({
                            query: {
                                platform: platform.value,
                                sort: filters.sort,
                            },
                        })
                    "
                    :class="[
                        'flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-sm transition-colors',
                        filters.platform === platform.value
                            ? 'border-primary bg-primary text-primary-foreground'
                            : 'hover:bg-accent',
                    ]"
                >
                    <PlatformIcon :platform="platform.icon" />
                    {{ platform.short_label }}
                </Link>
            </div>

            <div
                v-if="filters.q || filters.platform || filters.tag"
                class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
            >
                <SlidersHorizontal class="size-4" />
                <span>Filtered by</span>
                <Badge v-if="filters.q" variant="secondary">
                    “{{ filters.q }}”
                </Badge>
                <Badge v-if="activePlatform" variant="secondary">
                    {{ activePlatform.label }}
                </Badge>
                <Badge v-if="filters.tag" variant="secondary">
                    {{ filters.tag }}
                </Badge>
                <Button variant="link" size="sm" @click="clearFilters">
                    Clear
                </Button>
            </div>
        </div>

        <div
            v-if="products.data.length"
            class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <ProductCard
                v-for="product in products.data"
                :key="product.id"
                :product="product"
            />
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-20 text-center"
        >
            <PackageSearch class="size-10 text-muted-foreground" />
            <p class="font-medium">No apps match those filters</p>
            <p class="text-sm text-muted-foreground">
                Try a different search term or clear the filters.
            </p>
            <Button variant="outline" @click="clearFilters">
                Clear filters
            </Button>
        </div>

        <nav
            v-if="products.links.length > 3"
            class="mt-10 flex flex-wrap items-center justify-center gap-1"
            aria-label="Pagination"
        >
            <template v-for="link in products.links" :key="link.label">
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
