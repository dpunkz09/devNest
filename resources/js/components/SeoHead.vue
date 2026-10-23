<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const seo = computed(
    () => (page.props.seo ?? {}) as Record<string, string | null | undefined>,
);
</script>

<template>
    <Head>
        <meta
            v-if="seo.description"
            name="description"
            :content="seo.description"
        />
        <meta v-if="seo.keywords" name="keywords" :content="seo.keywords" />
        <meta v-if="seo.robots" name="robots" :content="seo.robots" />
        <link
            v-if="seo.canonical_url"
            rel="canonical"
            :href="seo.canonical_url"
        />
        <meta
            v-if="seo.og_title || seo.title"
            property="og:title"
            :content="seo.og_title ?? seo.title ?? undefined"
        />
        <meta
            v-if="seo.og_description || seo.description"
            property="og:description"
            :content="seo.og_description ?? seo.description ?? undefined"
        />
        <meta v-if="seo.og_image" property="og:image" :content="seo.og_image" />
        <meta v-if="seo.og_type" property="og:type" :content="seo.og_type" />
        <meta
            v-if="seo.twitter_card"
            name="twitter:card"
            :content="seo.twitter_card"
        />
        <meta
            v-if="seo.twitter_site"
            name="twitter:site"
            :content="seo.twitter_site"
        />
        <meta
            v-if="seo.twitter_creator"
            name="twitter:creator"
            :content="seo.twitter_creator"
        />
    </Head>
</template>
