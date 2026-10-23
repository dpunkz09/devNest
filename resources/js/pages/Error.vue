<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { home } from '@/routes';

const props = defineProps<{
    status: number;
}>();

const copy = computed(() => {
    switch (props.status) {
        case 401:
            return {
                title: 'You need to sign in',
                description:
                    'This page belongs to a signed in visitor. Sign in and try again.',
            };
        case 403:
            return {
                title: 'Not allowed',
                description:
                    'This page is not yours to see. If you think it should be, get in touch.',
            };
        case 404:
            return {
                title: 'Page not found',
                description:
                    'The page you were looking for does not exist or has moved somewhere else.',
            };
        case 419:
            return {
                title: 'Your session expired',
                description:
                    'You were away for a while. Refresh the page and pick up where you left off.',
            };
        case 429:
            return {
                title: 'Too many requests',
                description:
                    'You have made a lot of requests in a short time. Give it a moment and try again.',
            };
        case 503:
            return {
                title: 'Back shortly',
                description:
                    'The storefront is down for maintenance. It will be up again in no time.',
            };
        default:
            return {
                title: 'Something went wrong',
                description:
                    'We ran into a problem on our side. The error has been logged, please try again.',
            };
    }
});

const goBack = () => window.history.back();
</script>

<template>
    <Head :title="copy.title" />

    <div
        class="flex min-h-svh flex-col items-center justify-center gap-8 bg-background p-6 md:p-10"
    >
        <Link
            :href="home()"
            class="flex flex-col items-center gap-2 font-medium"
        >
            <div
                class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
            >
                <AppLogoIcon
                    class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                />
            </div>
            <span class="sr-only">DevNest</span>
        </Link>

        <div class="w-full max-w-md text-center">
            <p
                class="text-[4rem] leading-none font-semibold tracking-tight text-muted-foreground/40 tabular-nums"
            >
                {{ status }}
            </p>

            <h1 class="mt-4 text-2xl font-medium tracking-tight">
                {{ copy.title }}
            </h1>

            <p class="mt-3 text-sm text-muted-foreground">
                {{ copy.description }}
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <Button as-child>
                    <Link :href="home()">Back to the storefront</Link>
                </Button>

                <Button variant="outline" @click="goBack">Go back</Button>
            </div>
        </div>
    </div>
</template>
