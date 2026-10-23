<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue';

const props = defineProps<{
    data: Record<string, unknown>;
}>();

let script: HTMLScriptElement | null = null;

const render = () => {
    if (script) {
        script.textContent = JSON.stringify(props.data);
        return;
    }
    script = document.createElement('script');
    script.type = 'application/ld+json';
    script.textContent = JSON.stringify(props.data);
    document.head.appendChild(script);
};

onMounted(render);
watch(() => props.data, render, { deep: true });

onUnmounted(() => {
    script?.remove();
    script = null;
});
</script>

<template>
    <!-- JSON-LD is injected into the document head. -->
</template>
