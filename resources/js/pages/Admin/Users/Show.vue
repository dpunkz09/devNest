<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ArrowLeft,
    KeyRound,
    ShieldCheck,
    ShieldOff,
    UserCog,
} from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as usersIndex } from '@/routes/admin/users';
import { show as userShow } from '@/routes/admin/users';
import { update as userUpdate } from '@/routes/admin/users';
import { verify as userVerify } from '@/routes/admin/users';
import { unverify as userUnverify } from '@/routes/admin/users';
import { sendResetLink as userSendResetLink } from '@/routes/admin/users';
import { resetTwoFactor as userResetTwoFactor } from '@/routes/admin/users';
import { suspend as userSuspend } from '@/routes/admin/users';
import { unsuspend as userUnsuspend } from '@/routes/admin/users';
import type { User } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Users', href: usersIndex() },
        ],
    },
});

const props = defineProps<{
    user: User;
    stats: {
        products: number;
        orders: number;
        licenses: number;
        sales: number;
    };
}>();

const toggleAdmin = () =>
    router.patch(userUpdate.url({ user: props.user.id }), {
        is_admin: !props.user.is_admin,
    });
const verify = () => router.post(userVerify.url({ user: props.user.id }));
const unverify = () => router.post(userUnverify.url({ user: props.user.id }));
const sendResetLink = () =>
    router.post(userSendResetLink.url({ user: props.user.id }));
const resetTwoFactor = () =>
    router.post(userResetTwoFactor.url({ user: props.user.id }));
const suspend = () => router.post(userSuspend.url({ user: props.user.id }));
const unsuspend = () => router.post(userUnsuspend.url({ user: props.user.id }));
</script>

<template>
    <Head :title="props.user.name" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <Link
            :href="usersIndex()"
            class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
        >
            <ArrowLeft class="size-4" />
            Back to users
        </Link>

        <div class="flex flex-wrap items-center gap-3">
            <h1 class="text-2xl font-semibold tracking-tight">
                {{ props.user.name }}
            </h1>
            <Badge v-if="props.user.is_admin" variant="default"> admin </Badge>
            <Badge v-if="props.user.suspended" variant="destructive">
                suspended
            </Badge>
            <Badge v-if="!props.user.email_verified_at" variant="outline">
                unverified
            </Badge>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Apps</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ props.stats.products }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Orders</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ props.stats.orders }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Licenses</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ props.stats.licenses }}</p>
                </CardContent>
            </Card>
            <Card>
                <CardHeader class="pb-2">
                    <CardTitle class="text-sm font-medium">Sales</CardTitle>
                </CardHeader>
                <CardContent>
                    <p class="text-2xl font-bold">{{ props.stats.sales }}</p>
                </CardContent>
            </Card>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Account details</CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Email</span>
                    <span>{{ props.user.email }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Verified</span>
                    <span>
                        {{ props.user.email_verified_at ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-muted-foreground">Joined</span>
                    <span>
                        {{
                            props.user.created_at
                                ? new Date(
                                      props.user.created_at,
                                  ).toLocaleString()
                                : '—'
                        }}
                    </span>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Actions</CardTitle>
            </CardHeader>
            <CardContent class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" @click="toggleAdmin">
                    <UserCog class="size-4" />
                    {{ props.user.is_admin ? 'Demote' : 'Make admin' }}
                </Button>
                <Button
                    v-if="props.user.email_verified_at"
                    variant="outline"
                    size="sm"
                    @click="unverify"
                >
                    <ShieldOff class="size-4" />
                    Unverify email
                </Button>
                <Button v-else variant="outline" size="sm" @click="verify">
                    <ShieldCheck class="size-4" />
                    Verify email
                </Button>
                <Button variant="outline" size="sm" @click="sendResetLink">
                    <KeyRound class="size-4" />
                    Send reset link
                </Button>
                <Button variant="outline" size="sm" @click="resetTwoFactor">
                    Reset 2FA
                </Button>
                <Button
                    v-if="!props.user.suspended"
                    variant="outline"
                    size="sm"
                    @click="suspend"
                >
                    Suspend
                </Button>
                <Button v-else variant="outline" size="sm" @click="unsuspend">
                    Unsuspend
                </Button>
            </CardContent>
        </Card>
    </div>
</template>
