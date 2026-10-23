<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as auditLogIndex } from '@/routes/admin/audit-log';
import type { Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Audit log', href: auditLogIndex() },
        ],
    },
});

defineProps<{
    logs: Paginated<{
        id: number;
        action: string;
        subject_type: string | null;
        subject_id: number | null;
        properties: Record<string, unknown> | null;
        ip_address: string | null;
        created_at: string;
        user: { id: number; name: string; email: string } | null;
    }>;
}>();
</script>

<template>
    <Head title="Audit log" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Audit log</h1>
            <p class="text-sm text-muted-foreground">
                A record of the actions taken in the admin area.
            </p>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>Recent activity</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="log in logs.data"
                        :key="log.id"
                        class="flex flex-wrap items-start justify-between gap-3 py-3 text-sm"
                    >
                        <div class="min-w-0">
                            <p class="font-medium">
                                {{ log.action }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                <template v-if="log.user">
                                    {{ log.user.name }}
                                </template>
                                <template v-else> System </template>
                                ·
                                {{ new Date(log.created_at).toLocaleString() }}
                                <template v-if="log.subject_type">
                                    · {{ log.subject_type }} #{{
                                        log.subject_id
                                    }}
                                </template>
                            </p>
                            <pre
                                v-if="log.properties"
                                class="mt-1 overflow-x-auto rounded bg-muted p-2 text-xs"
                                >{{
                                    JSON.stringify(log.properties, null, 2)
                                }}</pre>
                        </div>
                        <Badge variant="outline" class="shrink-0">
                            {{ log.ip_address ?? '—' }}
                        </Badge>
                    </li>
                </ul>
            </CardContent>
        </Card>
    </div>
</template>
