<script setup lang="ts">
import {
    Apple,
    Globe,
    Laptop,
    Monitor,
    Smartphone,
    Terminal,
} from '@lucide/vue';
import { computed, type Component } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    platform: string;
    class?: string;
}>();

const icons: Record<string, Component> = {
    globe: Globe,
    smartphone: Smartphone,
    monitor: Monitor,
    apple: Apple,
    laptop: Laptop,
    terminal: Terminal,
};

const icon = computed(() => icons[props.platform] ?? Globe);
</script>

<template>
    <component
        :is="icon"
        :class="cn('size-4', props.class)"
        aria-hidden="true"
    />
</template>
