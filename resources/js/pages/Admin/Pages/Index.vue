<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { FileText, Plus, Trash2 } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as pagesIndex } from '@/routes/admin/pages';
import { create as pageCreate } from '@/routes/admin/pages';
import { edit as pageEdit } from '@/routes/admin/pages';
import { destroy as pageDestroy } from '@/routes/admin/pages';
import type { Page, Paginated } from '@/types';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Pages', href: pagesIndex() },
        ],
    },
});

defineProps<{
    pages: Paginated<Page>;
}>();

const remove = (page: Page) => {
    if (!confirm(`Delete ${page.title}?`)) return;
    router.delete(pageDestroy.url({ page: page.slug }));
};
</script>

<template>
    <Head title="Pages" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold tracking-tight">Pages</h1>
                <p class="text-sm text-muted-foreground">
                    Custom pages shown in the header or footer.
                </p>
            </div>
            <Button as-child>
                <Link :href="pageCreate()">
                    <Plus class="size-4" />
                    New page
                </Link>
            </Button>
        </div>

        <Card>
            <CardHeader>
                <CardTitle>All pages</CardTitle>
            </CardHeader>
            <CardContent>
                <ul class="divide-y">
                    <li
                        v-for="page in pages.data"
                        :key="page.id"
                        class="flex flex-wrap items-center justify-between gap-3 py-3"
                    >
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <FileText
                                    class="size-4 text-muted-foreground"
                                />
                                <Link
                                    :href="pageEdit.url({ page: page.slug })"
                                    class="font-medium hover:underline"
                                >
                                    {{ page.title }}
                                </Link>
                                <Badge variant="outline">
                                    {{ page.location }}
                                </Badge>
                                <Badge
                                    :variant="
                                        page.published ? 'default' : 'secondary'
                                    "
                                >
                                    {{ page.published ? 'published' : 'draft' }}
                                </Badge>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                /pages/{{ page.slug }}
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="icon-sm"
                                @click="remove(page)"
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
