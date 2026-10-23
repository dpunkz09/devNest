<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ExternalLink, Trash2 } from '@lucide/vue';
import ProductForm from '@/components/ProductForm.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
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
import { dashboard } from '@/routes';
import { show as appShow } from '@/routes/apps';
import {
    destroy as productDestroy,
    index as productsIndex,
    update as productUpdate,
} from '@/routes/dashboard/products';
import { destroy as destroyScreenshot } from '@/routes/dashboard/products/screenshots';
import type { Product } from '@/types';

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
        ],
    },
});

const props = defineProps<{
    product: Product;
    platforms: PlatformOption[];
    statuses: StatusOption[];
}>();

function removeScreenshot(screenshotId: number): void {
    router.delete(
        destroyScreenshot.url({
            product: props.product.id,
            screenshot: screenshotId,
        }),
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head :title="product.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="space-y-1">
            <Link
                :href="productsIndex()"
                class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
            >
                <ArrowLeft class="size-4" />
                Back to your apps
            </Link>

            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ product.name }}
                </h1>
                <Badge variant="secondary">
                    {{ product.status_label }}
                </Badge>
            </div>

            <p class="text-sm text-muted-foreground">
                Update the listing, the media and the pricing of this app.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <Button variant="outline" size="sm" as-child>
                <Link :href="appShow({ slug: product.slug })" target="_blank">
                    <ExternalLink />
                    View listing
                </Link>
            </Button>

            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" size="sm">
                        <Trash2 />
                        Delete app
                    </Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            Delete “{{ product.name }}”?
                        </DialogTitle>
                        <DialogDescription>
                            The listing, its screenshots and its features will
                            be removed. This cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>
                        <Button
                            variant="destructive"
                            @click="
                                router.delete(
                                    productDestroy.url({
                                        product: product.id,
                                    }),
                                )
                            "
                        >
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>

        <ProductForm
            :action="productUpdate.form({ product: product.id }).action"
            method="post"
            :platforms="platforms"
            :statuses="statuses"
            :product="product"
            submit-label="Save changes"
        />

        <section v-if="product.screenshots?.length" class="space-y-3">
            <h2 class="text-lg font-semibold tracking-tight">Screenshots</h2>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <figure
                    v-for="screenshot in product.screenshots"
                    :key="screenshot.id"
                    class="overflow-hidden rounded-lg border"
                >
                    <img
                        :src="screenshot.url"
                        :alt="screenshot.caption ?? product.name"
                        class="aspect-video w-full object-cover"
                    />
                    <figcaption
                        class="flex items-center justify-between gap-2 px-3 py-2"
                    >
                        <span class="truncate text-xs text-muted-foreground">
                            {{ screenshot.caption ?? 'Untitled screenshot' }}
                        </span>
                        <Button
                            variant="ghost"
                            size="icon-sm"
                            aria-label="Remove screenshot"
                            @click="removeScreenshot(screenshot.id)"
                        >
                            <Trash2 />
                        </Button>
                    </figcaption>
                </figure>
            </div>
        </section>
    </div>
</template>
