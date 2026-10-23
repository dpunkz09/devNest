<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, ShieldCheck, ShieldOff, Trash2, UserCog } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { show as userShow } from '@/routes/admin/users';
import { destroy as userDestroy } from '@/routes/admin/users';
import { update as userUpdate } from '@/routes/admin/users';
import { verify as userVerify } from '@/routes/admin/users';
import { unverify as userUnverify } from '@/routes/admin/users';
import { sendResetLink as userSendResetLink } from '@/routes/admin/users';
import { resetTwoFactor as userResetTwoFactor } from '@/routes/admin/users';
import { suspend as userSuspend } from '@/routes/admin/users';
import { unsuspend as userUnsuspend } from '@/routes/admin/users';
import type { Paginated, User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Users', href: usersIndex() },
        ],
    },
});

const props = defineProps<{
    users: Paginated<User>;
    filters: {
        search?: string | null;
        admin?: string | null;
        verified?: string | null;
        suspended?: string | null;
    };
}>();

const search = (props.filters.search ?? '') as string;

const applyFilter = (key: string, value: string | null) => {
    router.get(
        usersIndex.url(),
        {
            search: props.filters.search ?? undefined,
            admin: key === 'admin' ? value : (props.filters.admin ?? undefined),
            verified:
                key === 'verified'
                    ? value
                    : (props.filters.verified ?? undefined),
            suspended:
                key === 'suspended'
                    ? value
                    : (props.filters.suspended ?? undefined),
        },
        { preserveState: true, replace: true },
    );
};

const doSearch = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    router.get(
        usersIndex.url(),
        {
            search: value || undefined,
            admin: props.filters.admin ?? undefined,
            verified: props.filters.verified ?? undefined,
            suspended: props.filters.suspended ?? undefined,
        },
        { preserveState: true, replace: true },
    );
};

const toggleAdmin = (user: User) => {
    router.patch(userUpdate.url({ user: user.id }), {
        is_admin: !user.is_admin,
    });
};

const verify = (user: User) => router.post(userVerify.url({ user: user.id }));
const unverify = (user: User) =>
    router.post(userUnverify.url({ user: user.id }));
const sendResetLink = (user: User) =>
    router.post(userSendResetLink.url({ user: user.id }));
const resetTwoFactor = (user: User) =>
    router.post(userResetTwoFactor.url({ user: user.id }));
const suspend = (user: User) => router.post(userSuspend.url({ user: user.id }));
const unsuspend = (user: User) =>
    router.post(userUnsuspend.url({ user: user.id }));

const remove = (user: User) => {
    if (!confirm(`Delete ${user.name}?`)) return;
    router.delete(userDestroy.url({ user: user.id }));
};
</script>

<template>
    <Head title="Users" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Users</h1>
            <p class="text-sm text-muted-foreground">
                Every account on the storefront.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <div class="relative min-w-[200px] flex-1">
                <Search
                    class="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    :model-value="search"
                    type="search"
                    placeholder="Search by name or email"
                    class="pl-9"
                    @change="doSearch"
                />
            </div>

            <div class="flex flex-wrap gap-2">
                <Button
                    :variant="
                        props.filters.admin === '1' ? 'default' : 'outline'
                    "
                    size="sm"
                    @click="applyFilter('admin', '1')"
                >
                    Admins
                </Button>
                <Button
                    :variant="
                        props.filters.verified === '0' ? 'default' : 'outline'
                    "
                    size="sm"
                    @click="applyFilter('verified', '0')"
                >
                    Unverified
                </Button>
                <Button
                    :variant="
                        props.filters.suspended === '1' ? 'default' : 'outline'
                    "
                    size="sm"
                    @click="applyFilter('suspended', '1')"
                >
                    Suspended
                </Button>
                <Button
                    variant="ghost"
                    size="sm"
                    @click="router.get(usersIndex.url(), {}, { replace: true })"
                >
                    Clear
                </Button>
            </div>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>All users</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="user in users.data"
                        :key="user.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="userShow.url({ user: user.id })"
                                    class="font-medium hover:underline"
                                >
                                    {{ user.name }}
                                </Link>
                                <Badge v-if="user.is_admin" variant="default">
                                    admin
                                </Badge>
                                <Badge
                                    v-if="user.suspended"
                                    variant="destructive"
                                >
                                    suspended
                                </Badge>
                                <Badge
                                    v-if="!user.email_verified_at"
                                    variant="outline"
                                >
                                    unverified
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                {{ user.email }} · joined
                                {{
                                    user.created_at
                                        ? new Date(
                                              user.created_at,
                                          ).toLocaleDateString()
                                        : ''
                                }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="toggleAdmin(user)"
                            >
                                <UserCog class="size-4" />
                                {{ user.is_admin ? 'Demote' : 'Make admin' }}
                            </Button>

                            <Button
                                v-if="user.email_verified_at"
                                variant="outline"
                                size="sm"
                                @click="unverify(user)"
                            >
                                <ShieldOff class="size-4" />
                                Unverify
                            </Button>
                            <Button
                                v-else
                                variant="outline"
                                size="sm"
                                @click="verify(user)"
                            >
                                <ShieldCheck class="size-4" />
                                Verify
                            </Button>

                            <Button
                                variant="outline"
                                size="sm"
                                @click="sendResetLink(user)"
                            >
                                Reset password
                            </Button>

                            <Button
                                variant="outline"
                                size="sm"
                                @click="resetTwoFactor(user)"
                            >
                                Reset 2FA
                            </Button>

                            <Button
                                v-if="!user.suspended"
                                variant="outline"
                                size="sm"
                                @click="suspend(user)"
                            >
                                Suspend
                            </Button>
                            <Button
                                v-else
                                variant="outline"
                                size="sm"
                                @click="unsuspend(user)"
                            >
                                Unsuspend
                            </Button>

                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="remove(user)"
                            >
                                <Trash2 class="size-4" />
                            </Button>
                        </div>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
