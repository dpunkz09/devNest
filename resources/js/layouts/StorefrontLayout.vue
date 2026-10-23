<script setup lang="ts">
import SeoHead from '@/components/SeoHead.vue';
import StorefrontFooter from '@/components/StorefrontFooter.vue';
import StorefrontHeader from '@/components/StorefrontHeader.vue';
</script>

<template>
    <SeoHead />
    <div class="flex min-h-screen flex-col bg-background">
        <StorefrontHeader />
        <main class="flex-1">
            <slot />
        </main>
        <StorefrontFooter />
    </div>
</template>
