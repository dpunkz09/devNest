<script setup lang="ts">
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Link from '@tiptap/extension-link';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    modelValue: string;
    id?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();

const showHtml = ref(false);
const htmlDraft = ref(props.modelValue || '');

const editor = useEditor({
    extensions: [
        StarterKit,
        Link.configure({
            openOnClick: false,
        }),
    ],
    content: props.modelValue || '',
    onUpdate: ({ editor: ed }) => {
        const html = ed.getHTML();
        emit('update:modelValue', html);
        if (!showHtml.value) {
            htmlDraft.value = html;
        }
    },
});

watch(
    () => props.modelValue,
    (value) => {
        const incoming = value || '';
        if (
            editor.value &&
            editor.value.getHTML() !== incoming &&
            !showHtml.value
        ) {
            editor.value.commands.setContent(incoming, { emitUpdate: false });
        }
        if (showHtml.value) {
            htmlDraft.value = incoming;
        }
    },
);

onBeforeUnmount(() => {
    editor.value?.destroy();
});

const toggleMode = () => {
    if (showHtml.value) {
        // Leaving HTML mode: push the edited HTML back into the editor.
        if (editor.value) {
            editor.value.commands.setContent(htmlDraft.value || '', {
                emitUpdate: false,
            });
        }
        emit('update:modelValue', htmlDraft.value || '');
    } else {
        // Entering HTML mode: seed the textarea with the current HTML.
        htmlDraft.value = editor.value?.getHTML() ?? props.modelValue ?? '';
    }
    showHtml.value = !showHtml.value;
};

const onHtmlInput = (event: Event) => {
    const value = (event.target as HTMLTextAreaElement).value;
    htmlDraft.value = value;
    emit('update:modelValue', value);
};

const isActive = (name: string, attrs?: Record<string, unknown>) =>
    editor.value?.isActive(name, attrs) ?? false;

const toggleBold = () => editor.value?.chain().focus().toggleBold().run();
const toggleItalic = () => editor.value?.chain().focus().toggleItalic().run();
const toggleStrike = () => editor.value?.chain().focus().toggleStrike().run();
const toggleH1 = () =>
    editor.value?.chain().focus().toggleHeading({ level: 1 }).run();
const toggleH2 = () =>
    editor.value?.chain().focus().toggleHeading({ level: 2 }).run();
const toggleBulletList = () =>
    editor.value?.chain().focus().toggleBulletList().run();
const toggleOrderedList = () =>
    editor.value?.chain().focus().toggleOrderedList().run();
const toggleBlockquote = () =>
    editor.value?.chain().focus().toggleBlockquote().run();
const setLink = () => {
    const url = window.prompt('Enter a URL');
    if (url === null) return;
    if (url === '') {
        editor.value?.chain().focus().unsetLink().run();
        return;
    }
    editor.value?.chain().focus().setLink({ href: url }).run();
};
</script>

<template>
    <div class="rounded-md border">
        <div
            class="flex flex-wrap gap-1 border-b bg-muted/40 p-2"
            role="toolbar"
        >
            <button
                v-if="!showHtml"
                type="button"
                class="rounded px-2 py-1 text-xs font-medium hover:bg-accent"
                :class="{ 'bg-accent': isActive('bold') }"
                @click="toggleBold"
            >
                B
            </button>
            <button
                v-if="!showHtml"
                type="button"
                class="rounded px-2 py-1 text-xs italic hover:bg-accent"
                :class="{ 'bg-accent': isActive('italic') }"
                @click="toggleItalic"
            >
                I
            </button>
            <button
                v-if="!showHtml"
                type="button"
                class="rounded px-2 py-1 text-xs line-through hover:bg-accent"
                :class="{ 'bg-accent': isActive('strike') }"
                @click="toggleStrike"
            >
                S
            </button>
            <template v-if="!showHtml">
                <span class="mx-1 w-px bg-border" />
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs font-medium hover:bg-accent"
                    :class="{ 'bg-accent': isActive('heading', { level: 1 }) }"
                    @click="toggleH1"
                >
                    H1
                </button>
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs font-medium hover:bg-accent"
                    :class="{ 'bg-accent': isActive('heading', { level: 2 }) }"
                    @click="toggleH2"
                >
                    H2
                </button>
                <span class="mx-1 w-px bg-border" />
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs hover:bg-accent"
                    :class="{ 'bg-accent': isActive('bulletList') }"
                    @click="toggleBulletList"
                >
                    • List
                </button>
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs hover:bg-accent"
                    :class="{ 'bg-accent': isActive('orderedList') }"
                    @click="toggleOrderedList"
                >
                    1. List
                </button>
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs hover:bg-accent"
                    :class="{ 'bg-accent': isActive('blockquote') }"
                    @click="toggleBlockquote"
                >
                    Quote
                </button>
                <button
                    type="button"
                    class="rounded px-2 py-1 text-xs hover:bg-accent"
                    @click="setLink"
                >
                    Link
                </button>
            </template>
            <span class="mx-1 w-px bg-border" />
            <button
                type="button"
                class="rounded px-2 py-1 text-xs font-medium hover:bg-accent"
                :class="{ 'bg-accent': showHtml }"
                @click="toggleMode"
            >
                {{ showHtml ? 'Visual' : 'HTML' }}
            </button>
        </div>

        <EditorContent
            v-if="!showHtml"
            :id="props.id"
            :editor="editor"
            class="min-h-[200px] px-3 py-2 text-sm focus:outline-none [&_.ProseMirror]:min-h-[200px] [&_.ProseMirror]:outline-none"
        />

        <textarea
            v-else
            :value="htmlDraft"
            class="min-h-[200px] w-full resize-y px-3 py-2 font-mono text-xs focus:outline-none"
            @input="onHtmlInput"
        />
    </div>
</template>
