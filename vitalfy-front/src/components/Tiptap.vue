<template>
    <div v-if="editor" class="tiptap">
        <section class="flex items-center flex-wrap gap-3 p-2 pl-4 border border-slate-200 border-b-0 rounded-t-lg dark:border-gray-700">
            <button
                @click="editor.chain().focus().toggleHeading({ level: 1 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 1 }) }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <Heading1 :size="18" />
            </button>
            <button
                @click="editor.chain().focus().toggleHeading({ level: 2 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 2 }) }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <Heading2 :size="18" />
            </button>
            <button
                @click="editor.chain().focus().toggleHeading({ level: 3 }).run()"
                :class="{ 'is-active': editor.isActive('heading', { level: 3 }) }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <Heading3 :size="18" />
            </button>
            <button
                @click="editor.chain().focus().toggleBold().run()"
                :disabled="!editor?.can().chain().focus().toggleBold().run()"
                :class="{ 'is-active': editor.isActive('bold') }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <BoldIcon :size="18" />
            </button>
            <button
                @click="editor.chain().focus().toggleItalic().run()"
                :disabled="!editor?.can().chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor.isActive('italic') }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <ItalicIcon :size="18" />
            </button>
            <button
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'is-active': editor.isActive('bulletList') }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <List />
            </button>
            <button
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'is-active': editor.isActive('orderedList') }"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
            >
                <ListOrdered />
            </button>
            <button 
                @click="editor.chain().focus().undo().run()" 
                :disabled="!editor?.can().chain().focus().undo().run()"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
                :class="!editor.can().chain().focus().undo().run() ? 'text-gray-400' : 'text-gray-700'"
            >
                <Undo />
            </button>
            <button 
                @click="editor.chain().focus().redo().run()" 
                :disabled="!editor?.can().chain().focus().redo().run()"
                class="rounded-lg p-2 bg-white hover:bg-gray-100 duration-300 dark:bg-neutral-900 dark:hover:bg-gray-700 dark:text-white"
                :class="!editor.can().chain().focus().redo().run() ? 'text-gray-400' : 'text-gray-700'">
                <Redo />
            </button>
            <div class="flex items-center gap-3 ml-auto max-[540px]:ml-0 max-[540px]:w-full max-[540px]:flex-wrap">
                <button
                    v-if="allowRefine"
                    @click="$emit('open-refine-modal')"
                    class="flex items-center px-4 py-2 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full text-[13.5px] font-semibold text-white hover:opacity-90 transition max-[480px]:w-full max-[480px]:justify-center"
                    :class="hasUnsavedChanges ? '' : 'mr-3'"
                >
                    <Lock v-if="!isPro" :size="13" class="mr-1.5 opacity-90" />
                    <Sparkles v-else :size="16" class="mr-2" />
                    Refinar anamnese
                    <span v-if="!isPro" class="ml-2 px-1.5 py-0.5 text-[9px] font-bold bg-yellow-400 text-yellow-900 rounded-full leading-none">PRO</span>
                </button>
                <button
                    v-if="hasUnsavedChanges && allowRefine"
                    @click="handleSave"
                    :disabled="isSaving"
                    class="flex items-center px-4 py-2 mr-2 bg-green-700 rounded-full font-semibold text-[13.5px] text-white hover:opacity-90 transition disabled:opacity-50 disabled:cursor-not-allowed max-[480px]:w-full max-[480px]:justify-center"
                >
                    <Loader2 v-if="isSaving" :size="18" class="mr-2 animate-spin" />
                    <Save v-else :size="18" class="mr-2" />
                    {{ isSaving ? 'Salvando...' : 'Salvar' }}
                </button>
            </div>
        </section>
        <component :is="EditorContent" :editor="editor" />
    </div>
</template>

<script setup>
import { markRaw, onBeforeUnmount, onMounted, ref, shallowRef, watch, computed } from 'vue';
import { Heading1, Heading2, Heading3, Bold as BoldIcon, Italic as ItalicIcon, List, ListOrdered, Undo, Redo, Sparkles, Save, Loader2, Lock } from 'lucide-vue-next';
import DOMPurify from 'dompurify';

// FE-R10-01 (ai-vitalfy/risks.md#r10): allowlist espelha
// DocumentService::sanitizeClinicalHtml() no back-end (BE-R10-03) — mesmo
// conjunto de tags, sem atributos. Mudar uma lista exige mudar a outra.
const sanitizeClinicalHtml = (html) => DOMPurify.sanitize(html, {
    ALLOWED_TAGS: ['h1', 'h2', 'h3', 'p', 'ul', 'ol', 'li', 'strong', 'em', 'br'],
    ALLOWED_ATTR: []
});

const props = defineProps({
    content: {
        type: String,
        required: true
    },
    allowRefine: {
        type: Boolean,
        default: true
    },
    isPro: {
        type: Boolean,
        default: true
    },
    isSaving: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['open-refine-modal', 'update:content', 'save']);

const editor = ref(null);
const EditorContent = shallowRef(null);

const hasUnsavedChanges = computed(() => {
    if (!editor.value) return false;
    return editor.value.can().chain().focus().undo().run();
});

const handleSave = () => {
    if (editor.value) {
        emit('save', editor.value.getHTML());
    }
};

onMounted(() => {
    initEditor();
});

const initEditor = async () => {
    const { EditorContent: EC, Editor } = await import('@tiptap/vue-3');

    const Document = (await import('@tiptap/extension-document')).default;
    const Paragraph = (await import('@tiptap/extension-paragraph')).default;
    const Text = (await import('@tiptap/extension-text')).default;
    const Bold = (await import('@tiptap/extension-bold')).default;
    const Italic = (await import('@tiptap/extension-italic')).default;
    const Heading = (await import('@tiptap/extension-heading')).default;
    const BulletList = (await import('@tiptap/extension-bullet-list')).default;
    const OrderedList = (await import('@tiptap/extension-ordered-list')).default;
    const ListItem = (await import('@tiptap/extension-list-item')).default;
    const History = (await import('@tiptap/extension-history')).default;

    EditorContent.value = markRaw(EC);

    editor.value = new Editor({
        editorProps: {
            attributes: {
                class: 'border border-slate-200 rounded-b-lg p-4 min-h-[21rem] max-h-[37rem] overflow-y-auto outline-none dark:border-gray-700',
            },
        },
        content: sanitizeClinicalHtml(props.content),
        extensions: [
            Document,
            Paragraph,
            Text,
            Bold,
            Italic,
            Heading.configure({ levels: [1, 2, 3] }),
            BulletList,
            OrderedList,
            ListItem,
            History
        ],
        onUpdate({ editor }) {
            emit('update:content', editor.getHTML())
        }
    });
};

onBeforeUnmount(() => {
    if (editor.value) {
        editor.value.destroy();
        editor.value = null;
    }
});

watch(
    () => props.content,
    (newContent) => {
        if (!editor.value) return;

        const current = editor.value.getHTML();

        if (newContent !== current) {
            editor.value.commands.setContent(sanitizeClinicalHtml(newContent), false);
        }
    }
);
</script>

<style>
.is-active {
    @apply bg-blue-500;
    color: white;
}
.is-active:hover {
    @apply bg-blue-600;
}

.tiptap:first-child {
    margin-top: 0;
}

.tiptap ul,
.tiptap ol {
    padding: 0 1rem;
    margin: 0.6rem 1rem 0.5rem 0.6rem;
}

.tiptap ul li p,
.tiptap ol li p {
    margin-top: 0.25em;
    margin-bottom: 0.25em;
}

.tiptap ul {
    list-style-type: disc;
    list-style-position: initial;
}

.tiptap ol {
    list-style-type: decimal;
    list-style-position: initial;
}

.tiptap h1,
.tiptap h2,
.tiptap h3 {
    text-wrap: pretty;
}

.tiptap h1 {
    font-size: 1.6rem;
}

.tiptap h2 {
  font-size: 1.4rem;
}

.tiptap h3 {
    font-size: 1.2rem;
    margin-bottom: 0.1rem;
}

.tiptap p {
    margin-bottom: 0.1rem;
}
</style>