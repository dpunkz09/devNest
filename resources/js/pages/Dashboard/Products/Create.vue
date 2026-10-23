<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import ProductForm from '@/components/ProductForm.vue';
import { dashboard } from '@/routes';
import {
    create as productCreate,
    index as productsIndex,
    store as productStore,
} from '@/routes/dashboard/products';

type PlatformOption = {
    value: string;
    label: string;
    short_label: string;
    icon: string;
};

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
            {
                title: 'New app',
                href: productCreate(),
            },
        ],
    },
});

defineProps<{
    platforms: PlatformOption[];
    statuses: StatusOption[];
}>();
</script>

<template>
    <Head title="New app" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="space-y-1">
            <Link
                :href="productsIndex()"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to your apps
            </Link>
            <h1 class="text-2xl font-semibold tracking-tight">
                Publish a new app
            </h1>
            <p class="text-sm text-muted-foreground">
                Describe what you built, add a few screenshots and choose a
                price. You can keep it as a draft and publish it later.
            </p>
        </div>

        <ProductForm
            :action="productStore.form().action"
            method="post"
            :platforms="platforms"
            :statuses="statuses"
            submit-label="Create app"
        />
    </div>
</template>
