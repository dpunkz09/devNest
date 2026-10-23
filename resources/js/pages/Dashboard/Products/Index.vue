<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArchiveRestore,
    Eye,
    Package,
    Pencil,
    Plus,
    Search,
    Trash2,
} from '@lucide/vue';
import { computed, reactive, ref, watch } from 'vue';
import PlatformIcon from '@/components/PlatformIcon.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { dashboard } from '@/routes';
import {
    create as productCreate,
    destroy as productDestroy,
    edit as productEdit,
    index as productsIndex,
    restore as productRestore,
} from '@/routes/dashboard/products';
import type { Paginated, Product } from '@/types';

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
                title: 'Apps',
                href: productsIndex(),
            },
        ],
    },
});

const props = defineProps<{
    products: Paginated<Product>;
    filters: {
        q: string | null;
        status: string | null;
    };
    statuses: StatusOption[];
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

const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
    published: 'default',
    draft: 'secondary',
    archived: 'outline',
};

const hasFilters = computed(
    () => Boolean(filters.q) || Boolean(filters.status),
);

function apply(partial: Record<string, string | null> = {}): void {
    router.get(
        productsIndex().url,
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
        productsIndex().url,
        {},
        { preserveState: true, preserveScroll: true, replace: true },
    );
}
</script>

<template>
    <Head title="Your apps" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Your apps</h1>
                <p class="text-sm text-muted-foreground">
                    Publish, update and archive the apps you have built.
                </p>
            </div>

            <Button as-child>
                <Link :href="productCreate()">
                    <Plus />
                    New app
                </Link>
            </Button>
        </div>

        <Card>
            <CardContent class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row">
                    <form
                        class="relative flex-1"
                        @submit.prevent="submitSearch"
                    >
                        <Search
                            class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                        />
                        <Input
                            v-model="search"
                            type="search"
                            placeholder="Search your apps..."
                            class="h-10 pl-9"
                        />
                    </form>

                    <select
                        :value="filters.status ?? ''"
                        class="h-10 rounded-md border border-input bg-background px-3 text-sm"
                        @change="
                            apply({
                                status:
                                    ($event.target as HTMLSelectElement)
                                        .value || null,
                            })
                        "
                    >
                        <option value="">All statuses</option>
                        <option
                            v-for="status in statuses"
                            :key="status.value"
                            :value="status.value"
                        >
                            {{ status.label }}
                        </option>
                    </select>
                </div>

                <div
                    v-if="hasFilters"
                    class="flex items-center gap-2 text-sm text-muted-foreground"
                >
                    <span>Filtered by</span>
                    <Badge v-if="filters.q" variant="secondary">
                        “{{ filters.q }}”
                    </Badge>
                    <Badge v-if="filters.status" variant="secondary">
                        {{
                            statuses.find(
                                (status) => status.value === filters.status,
                            )?.label ?? filters.status
                        }}
                    </Badge>
                    <Button variant="link" size="sm" @click="clearFilters">
                        Clear
                    </Button>
                </div>
            </CardContent>
        </Card>

        <div v-if="products.data.length" class="grid gap-4">
            <Card v-for="product in products.data" :key="product.id">
                <CardContent class="flex flex-wrap items-center gap-4">
                    <div
                        class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gradient-to-br from-neutral-200 to-neutral-300 text-lg font-semibold text-neutral-500 dark:from-neutral-800 dark:to-neutral-950 dark:text-neutral-400"
                    >
                        <img
                            v-if="product.cover_image_url"
                            :src="product.cover_image_url"
                            :alt="product.name"
                            class="size-full object-cover"
                        />
                        <Package v-else class="size-6" />
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <Link
                                :href="productEdit({ product: product.id })"
                                class="truncate font-medium hover:underline"
                            >
                                {{ product.name }}
                            </Link>
                            <Badge
                                :variant="
                                    statusVariant[product.status] ?? 'outline'
                                "
                            >
                                {{ product.status_label }}
                            </Badge>
                            <Badge v-if="product.archived" variant="outline">
                                Archived
                            </Badge>
                        </div>

                        <p class="truncate text-sm text-muted-foreground">
                            {{ product.tagline }}
                        </p>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground"
                        >
                            <span class="inline-flex items-center gap-1">
                                <PlatformIcon
                                    :platform="product.platform_icon"
                                    class="size-3.5"
                                />
                                {{ product.platform_label }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <Eye class="size-3.5" />
                                {{ product.views }} views
                            </span>
                            <span>{{ product.sales }} sold</span>
                            <span class="font-medium text-foreground">
                                {{ product.price_formatted }}
                            </span>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-2">
                        <Button
                            v-if="product.archived"
                            variant="outline"
                            size="sm"
                            @click="
                                router.post(
                                    productRestore.url({
                                        product: product.id,
                                    }),
                                    {},
                                    { preserveScroll: true },
                                )
                            "
                        >
                            <ArchiveRestore />
                            Restore
                        </Button>

                        <template v-else>
                            <Button variant="outline" size="sm" as-child>
                                <Link
                                    :href="productEdit({ product: product.id })"
                                >
                                    <Pencil />
                                    Edit
                                </Link>
                            </Button>

                            <Dialog>
                                <DialogTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        aria-label="Archive app"
                                    >
                                        <Trash2 />
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>
                                            Archive “{{ product.name }}”?
                                        </DialogTitle>
                                        <DialogDescription>
                                            The listing disappears from the
                                            catalogue. The sales it made and the
                                            licenses of its buyers are kept, so
                                            you can restore it later.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter class="gap-2">
                                        <DialogClose as-child>
                                            <Button variant="secondary">
                                                Cancel
                                            </Button>
                                        </DialogClose>
                                        <Button
                                            variant="destructive"
                                            @click="
                                                router.delete(
                                                    productDestroy.url({
                                                        product: product.id,
                                                    }),
                                                    { preserveScroll: true },
                                                )
                                            "
                                        >
                                            Archive
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </template>
                    </div>
                </CardContent>
            </Card>
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-16 text-center"
        >
            <Package class="size-10 text-muted-foreground" />
            <p class="font-medium">You have no apps yet</p>
            <p class="text-sm text-muted-foreground">
                Publish your first app to start selling it.
            </p>
            <Button as-child>
                <Link :href="productCreate()">
                    <Plus />
                    New app
                </Link>
            </Button>
        </div>

        <nav
            v-if="products.links.length > 3"
            class="flex flex-wrap items-center justify-center gap-1"
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
