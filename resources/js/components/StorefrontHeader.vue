<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, Menu, Package, ShoppingBag } from '@lucide/vue';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { getInitials } from '@/composables/useInitials';
import { dashboard, home, login, register } from '@/routes';
import { index as appsIndex } from '@/routes/apps';
import { index as purchasesIndex } from '@/routes/purchases';

const page = usePage();
const auth = computed(() => page.props.auth);
const name = computed(() => page.props.name);
const headerPages = computed(
    () =>
        (page.props.headerPages ?? []) as Array<{
            title: string;
            href: string;
        }>,
);

const navigation = computed(() => [
    { title: 'Home', href: home() },
    { title: 'Browse apps', href: appsIndex() },
    ...headerPages.value,
]);
</script>

<template>
    <header class="sticky top-0 z-40 border-b bg-background/90 backdrop-blur">
        <div
            class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 md:px-6"
        >
            <div class="lg:hidden">
                <Sheet>
                    <SheetTrigger :as-child="true">
                        <Button variant="ghost" size="icon" class="size-9">
                            <Menu class="size-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-[280px] p-6">
                        <SheetHeader>
                            <SheetTitle class="flex items-center gap-2">
                                <AppLogoIcon
                                    class="size-6 fill-current text-black dark:text-white"
                                />
                                {{ name }}
                            </SheetTitle>
                        </SheetHeader>
                        <nav class="mt-6 space-y-1">
                            <Link
                                v-for="item in navigation"
                                :key="item.title"
                                :href="item.href"
                                class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                            >
                                {{ item.title }}
                            </Link>
                            <Link
                                v-if="auth.user"
                                :href="purchasesIndex()"
                                class="block rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                            >
                                My purchases
                            </Link>
                        </nav>
                    </SheetContent>
                </Sheet>
            </div>

            <Link :href="home()" class="flex items-center gap-2">
                <AppLogoIcon
                    class="size-7 fill-current text-black dark:text-white"
                />
                <span class="text-base font-semibold tracking-tight">
                    {{ name }}
                </span>
            </Link>

            <nav class="ml-6 hidden items-center gap-1 md:flex">
                <Link
                    v-for="item in navigation"
                    :key="item.title"
                    :href="item.href"
                    class="rounded-md px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                >
                    {{ item.title }}
                </Link>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <template v-if="auth.user">
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="hidden sm:inline-flex"
                    >
                        <Link :href="dashboard()">
                            <LayoutGrid />
                            Dashboard
                        </Link>
                    </Button>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-9 w-auto rounded-full p-1"
                            >
                                <Avatar
                                    class="size-7 overflow-hidden rounded-full"
                                >
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 text-xs font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <DropdownMenuLabel>
                                {{ auth.user?.name }}
                            </DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem as-child>
                                <Link :href="dashboard()">
                                    <LayoutGrid />
                                    Dashboard
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link :href="purchasesIndex()">
                                    <ShoppingBag />
                                    My purchases
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link :href="appsIndex()">
                                    <Package />
                                    Browse apps
                                </Link>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </template>

                <template v-else>
                    <Button variant="ghost" size="sm" as-child>
                        <Link :href="login()">Log in</Link>
                    </Button>
                    <Button size="sm" as-child>
                        <Link :href="register()">Get started</Link>
                    </Button>
                </template>
            </div>
        </div>
    </header>
</template>
