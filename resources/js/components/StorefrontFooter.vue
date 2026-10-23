<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { dashboard, home, login, register } from '@/routes';
import { index as appsIndex } from '@/routes/apps';
import { index as purchasesIndex } from '@/routes/purchases';

const page = usePage();
const auth = computed(() => page.props.auth);
const name = computed(() => page.props.name);
const footerPages = computed(
    () =>
        (page.props.footerPages ?? []) as Array<{
            title: string;
            href: string;
        }>,
);
const year = new Date().getFullYear();
</script>

<template>
    <footer class="border-t bg-muted/30">
        <div
            class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 md:flex-row md:items-start md:justify-between md:px-6"
        >
            <div class="max-w-sm space-y-3">
                <Link :href="home()" class="flex items-center gap-2">
                    <AppLogoIcon
                        class="size-6 fill-current text-black dark:text-white"
                    />
                    <span class="font-semibold tracking-tight">{{ name }}</span>
                </Link>
                <p class="text-sm text-muted-foreground">
                    A marketplace where independent developers sell web, desktop
                    and mobile software.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-8 text-sm sm:gap-16">
                <div class="space-y-3">
                    <p class="font-medium">Store</p>
                    <ul class="space-y-2 text-muted-foreground">
                        <li>
                            <Link
                                :href="appsIndex()"
                                class="transition-colors hover:text-foreground"
                            >
                                Browse all apps
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="
                                    appsIndex({ query: { sort: 'popular' } })
                                "
                                class="transition-colors hover:text-foreground"
                            >
                                Most popular
                            </Link>
                        </li>
                        <li>
                            <Link
                                :href="appsIndex({ query: { sort: 'newest' } })"
                                class="transition-colors hover:text-foreground"
                            >
                                Latest releases
                            </Link>
                        </li>
                    </ul>
                </div>

                <div class="space-y-3">
                    <p class="font-medium">Account</p>
                    <ul class="space-y-2 text-muted-foreground">
                        <template v-if="auth.user">
                            <li>
                                <Link
                                    :href="dashboard()"
                                    class="transition-colors hover:text-foreground"
                                >
                                    Dashboard
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="purchasesIndex()"
                                    class="transition-colors hover:text-foreground"
                                >
                                    My purchases
                                </Link>
                            </li>
                        </template>
                        <template v-else>
                            <li>
                                <Link
                                    :href="login()"
                                    class="transition-colors hover:text-foreground"
                                >
                                    Log in
                                </Link>
                            </li>
                            <li>
                                <Link
                                    :href="register()"
                                    class="transition-colors hover:text-foreground"
                                >
                                    Create account
                                </Link>
                            </li>
                        </template>
                    </ul>
                </div>

                <div v-if="footerPages.length" class="space-y-3">
                    <p class="font-medium">Legal</p>
                    <ul class="space-y-2 text-muted-foreground">
                        <li v-for="item in footerPages" :key="item.title">
                            <Link
                                :href="item.href"
                                class="transition-colors hover:text-foreground"
                            >
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div
            class="border-t px-4 py-4 text-center text-xs text-muted-foreground md:px-6"
        >
            &copy; {{ year }} {{ name }}. All rights reserved.
        </div>
    </footer>
</template>
