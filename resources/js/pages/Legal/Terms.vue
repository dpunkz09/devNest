<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft } from '@lucide/vue';
import { dashboard } from '@/routes';
import { home } from '@/routes';

defineProps<{
    content: string | null;
}>();

const paragraphs = (content: string | null) =>
    (content ?? '')
        .split(/\n\s*\n/)
        .map((p) => p.trim())
        .filter(Boolean);
</script>

<template>
    <Head title="Terms of service" />

    <div class="mx-auto w-full max-w-3xl px-4 py-12 md:px-6">
        <Link
            :href="home()"
            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Back to the storefront
        </Link>

        <h1 class="mt-6 text-3xl font-semibold tracking-tight">
            Terms of service
        </h1>

        <div
            class="mt-6 space-y-4 text-sm leading-relaxed text-muted-foreground"
        >
            <p v-for="(paragraph, index) in paragraphs(content)" :key="index">
                {{ paragraph }}
            </p>
            <p v-if="paragraphs(content).length === 0" class="italic">
                The terms of service have not been published yet.
            </p>
        </div>
    </div>
</template>
