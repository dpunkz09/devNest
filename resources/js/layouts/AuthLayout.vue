<script setup lang="ts">
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

const { title = '', description = '' } = defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <SeoHead />
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
</template>
