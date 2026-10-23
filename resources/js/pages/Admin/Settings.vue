<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { dashboard as adminDashboard } from '@/routes/admin';
import { edit as settingsEdit } from '@/routes/admin/settings';
import { update as settingsUpdate } from '@/routes/admin/settings';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: adminDashboard() },
            { title: 'Settings', href: settingsEdit() },
        ],
    },
});

const props = defineProps<{
    settings: {
        app_name: string;
        allow_registration: boolean;
        support_email: string | null;
        seo: {
            title: string | null;
            description: string | null;
            keywords: string | null;
            robots: string | null;
            canonical_url: string | null;
            og_title: string | null;
            og_description: string | null;
            og_image: string | null;
            og_type: string | null;
            twitter_card: string | null;
            twitter_site: string | null;
            twitter_creator: string | null;
        };
    };
}>();

const form = useForm({
    app_name: props.settings.app_name,
    allow_registration: props.settings.allow_registration,
    support_email: props.settings.support_email ?? '',
    seo: {
        title: props.settings.seo.title ?? '',
        description: props.settings.seo.description ?? '',
        keywords: props.settings.seo.keywords ?? '',
        robots: props.settings.seo.robots ?? '',
        canonical_url: props.settings.seo.canonical_url ?? '',
        og_title: props.settings.seo.og_title ?? '',
        og_description: props.settings.seo.og_description ?? '',
        og_image: props.settings.seo.og_image ?? '',
        og_type: props.settings.seo.og_type ?? '',
        twitter_card: props.settings.seo.twitter_card ?? '',
        twitter_site: props.settings.seo.twitter_site ?? '',
        twitter_creator: props.settings.seo.twitter_creator ?? '',
    },
});

const submit = () =>
    form
        .transform((data) => ({
            ...data,
            support_email: data.support_email || null,
            seo: Object.fromEntries(
                Object.entries(data.seo).map(([key, value]) => [
                    key,
                    value === '' ? null : value,
                ]),
            ),
        }))
        .patch(settingsUpdate.url());
</script>

<template>
    <Head title="Settings" />

    <div class="flex flex-1 flex-col gap-6 p-4 md:p-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Settings</h1>
            <p class="text-sm text-muted-foreground">
                Site wide configuration, SEO and legal pages.
            </p>
        </div>

        <form class="max-w-2xl space-y-8" @submit.prevent="submit">
            <section class="space-y-4">
                <h2 class="text-lg font-medium">General</h2>

                <div class="space-y-2">
                    <Label for="app_name">App name</Label>
                    <Input id="app_name" v-model="form.app_name" type="text" />
                </div>

                <div class="space-y-2">
                    <Label for="support_email">Support email</Label>
                    <Input
                        id="support_email"
                        v-model="form.support_email"
                        type="email"
                    />
                </div>

                <div class="flex items-center gap-3">
                    <input
                        id="allow_registration"
                        v-model="form.allow_registration"
                        type="checkbox"
                        class="size-4 rounded border-input"
                    />
                    <Label for="allow_registration">
                        Allow new registrations
                    </Label>
                </div>
            </section>

            <section class="space-y-4">
                <h2 class="text-lg font-medium">SEO</h2>

                <div class="space-y-2">
                    <Label for="seo_title">Title</Label>
                    <Input
                        id="seo_title"
                        v-model="form.seo.title"
                        type="text"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="seo_description">Description</Label>
                    <Textarea
                        id="seo_description"
                        v-model="form.seo.description"
                        rows="3"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="seo_keywords">Keywords</Label>
                    <Input
                        id="seo_keywords"
                        v-model="form.seo.keywords"
                        type="text"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="seo_robots">Robots</Label>
                        <Input
                            id="seo_robots"
                            v-model="form.seo.robots"
                            type="text"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_canonical_url">Canonical URL</Label>
                        <Input
                            id="seo_canonical_url"
                            v-model="form.seo.canonical_url"
                            type="url"
                        />
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="seo_og_title">Open Graph title</Label>
                    <Input
                        id="seo_og_title"
                        v-model="form.seo.og_title"
                        type="text"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="seo_og_description">
                        Open Graph description
                    </Label>
                    <Textarea
                        id="seo_og_description"
                        v-model="form.seo.og_description"
                        rows="2"
                    />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="seo_og_image">Open Graph image URL</Label>
                        <Input
                            id="seo_og_image"
                            v-model="form.seo.og_image"
                            type="url"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_og_type">Open Graph type</Label>
                        <Input
                            id="seo_og_type"
                            v-model="form.seo.og_type"
                            type="text"
                        />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-2">
                        <Label for="seo_twitter_card">Twitter card</Label>
                        <Input
                            id="seo_twitter_card"
                            v-model="form.seo.twitter_card"
                            type="text"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_twitter_site">Twitter site</Label>
                        <Input
                            id="seo_twitter_site"
                            v-model="form.seo.twitter_site"
                            type="text"
                        />
                    </div>
                    <div class="space-y-2">
                        <Label for="seo_twitter_creator">
                            Twitter creator
                        </Label>
                        <Input
                            id="seo_twitter_creator"
                            v-model="form.seo.twitter_creator"
                            type="text"
                        />
                    </div>
                </div>
            </section>

            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Saving...' : 'Save settings' }}
            </Button>
        </form>
    </div>
</template>
