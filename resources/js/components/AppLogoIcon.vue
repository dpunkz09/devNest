<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

defineProps<Props>();
</script>

<template>
    <img
        src="/assets/light_logo.png"
        :class="className"
        class="block dark:hidden"
        v-bind="$attrs"
        alt="Logo"
    />
    <img
        src="/assets/dark_logo.png"
        :class="className"
        class="hidden dark:block"
        v-bind="$attrs"
        alt="Logo"
    />
</template>
