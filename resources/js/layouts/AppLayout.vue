<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import SeoHead from '@/components/SeoHead.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [] } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
}>();
</script>

<template>
    <SeoHead />
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
</template>
