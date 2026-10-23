<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { home } from '@/routes';

defineProps<{
    page: {
        id: number;
        slug: string;
        title: string;
        format: 'html' | 'markdown';
        content: string | null;
        rendered_content: string;
        location: string;
        sort_order: number;
    };
}>();
</script>

<template>
    <Head :title="page.title" />

    <div class="mx-auto w-full max-w-3xl px-4 py-12 md:px-6">
        <Link
            :href="home()"
            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Back to the storefront
        </Link>

        <h1 class="mt-6 text-3xl font-semibold tracking-tight">
            {{ page.title }}
        </h1>

        <div
            v-if="page.rendered_content"
            class="prose prose-sm mt-6 max-w-none text-muted-foreground dark:prose-invert"
            v-html="page.rendered_content"
        />

        <p v-else class="mt-6 text-sm text-muted-foreground italic">
            This page has no content yet.
        </p>
    </div>
</template>
