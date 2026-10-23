<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import DOMPurify from 'dompurify';
import { marked } from 'marked';
import { computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WysiwygEditor from '@/components/WysiwygEditor.vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard as adminDashboard } from '@/routes/admin';
import { index as pagesIndex } from '@/routes/admin/pages';
import { store as pageStore } from '@/routes/admin/pages';
import { update as pageUpdate } from '@/routes/admin/pages';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Pages', href: pagesIndex() },
        ],
    },
});

const props = defineProps<{
    page: {
        id: number;
        slug: string;
        title: string;
        format: 'html' | 'markdown';
        content: string | null;
        location: string;
        sort_order: number;
        published: boolean;
    } | null;
}>();

const form = useForm({
    title: props.page?.title ?? '',
    slug: props.page?.slug ?? '',
    format: props.page?.format ?? 'html',
    content: props.page?.content ?? '',
    location: props.page?.location ?? 'hidden',
    sort_order: props.page?.sort_order ?? 0,
    published: props.page?.published ?? false,
});

const markdownPreview = computed(() => {
    if (form.format !== 'markdown' || !form.content) return '';
    const html = marked.parse(form.content, {
        async: false,
        gfm: true,
        breaks: true,
    }) as string;
    return DOMPurify.sanitize(html);
});

const submit = () => {
    if (props.page) {
        form.patch(pageUpdate.url({ page: props.page.slug }));
    } else {
        form.post(pageStore.url());
    }
};
</script>

<template>
    <Head :title="props.page ? 'Edit page' : 'New page'" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <h1 class="text-2xl font-semibold tracking-tight">
            {{ props.page ? 'Edit page' : 'New page' }}
        </h1>

        <form class="max-w-2xl space-y-6" @submit.prevent="submit">
            <div class="space-y-2">
                <Label for="title">Title</Label>
                <Input id="title" v-model="form.title" type="text" />
            </div>

            <div class="space-y-2">
                <Label for="slug">Slug</Label>
                <Input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    placeholder="leave empty to generate from title"
                />
            </div>

            <div class="space-y-2">
                <Label for="location">Location</Label>
                <Select v-model="form.location">
                    <SelectTrigger id="location">
                        <SelectValue placeholder="Where to show" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="hidden">Hidden</SelectItem>
                        <SelectItem value="header">Header</SelectItem>
                        <SelectItem value="footer">Footer</SelectItem>
                        <SelectItem value="both">Header and footer</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="sort_order">Sort order</Label>
                <Input
                    id="sort_order"
                    v-model.number="form.sort_order"
                    type="number"
                    min="0"
                />
            </div>

            <div class="space-y-2">
                <Label for="format">Format</Label>
                <Select v-model="form.format">
                    <SelectTrigger id="format">
                        <SelectValue placeholder="Content format" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="html">Rich text (HTML)</SelectItem>
                        <SelectItem value="markdown">Markdown</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="space-y-2">
                <Label for="content">Content</Label>
                <WysiwygEditor
                    v-if="form.format === 'html'"
                    id="content"
                    v-model="form.content"
                />
                <div v-else class="grid gap-4 lg:grid-cols-2">
                    <Textarea
                        id="content"
                        v-model="form.content"
                        rows="20"
                        class="font-mono text-sm"
                    />
                    <div
                        class="prose prose-sm max-h-[32rem] max-w-none overflow-auto rounded-md border bg-muted/30 p-4 text-foreground dark:prose-invert"
                    >
                        <div v-if="markdownPreview" v-html="markdownPreview" />
                        <p v-else class="text-muted-foreground">
                            Preview appears here.
                        </p>
                    </div>
                </div>
                <p class="text-xs text-muted-foreground">
                    {{
                        form.format === 'markdown'
                            ? 'Markdown supported: headings, lists, links, tables, code blocks. Live preview on the right.'
                            : 'Use the toolbar to format. Switch format to Markdown to write raw markdown.'
                    }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <input
                    id="published"
                    v-model="form.published"
                    type="checkbox"
                    class="size-4 rounded border-input"
                />
                <Label for="published">Published</Label>
            </div>

            <div class="flex gap-3">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save' }}
                </Button>
                <Button variant="outline" as-child>
                    <a :href="pagesIndex.url()">Cancel</a>
                </Button>
            </div>
        </form>
    </div>
</template>
