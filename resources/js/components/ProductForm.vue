<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { Plus, Save, Trash2 } from '@lucide/vue';
import { reactive, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { Product } from '@/types';

type PlatformOption = {
    value: string;
    label: string;
    short_label: string;
    icon: string;
};

type StatusOption = {
    value: string;
    label: string;
};

type FeatureRow = {
    title: string;
    description: string;
};

type Props = {
    action: string;
    method?: 'get' | 'post' | 'put' | 'patch' | 'delete';
    platforms: PlatformOption[];
    statuses: StatusOption[];
    product?: Product | null;
    submitLabel?: string;
};

const props = withDefaults(defineProps<Props>(), {
    method: 'post',
    product: null,
    submitLabel: 'Save app',
});

const form = reactive({
    name: '',
    slug: '',
    tagline: '',
    description: '',
    platform: 'web',
    status: 'draft',
    price: '0',
    version: '',
    requirements: '',
    demo_url: '',
    download_url: '',
    repository_url: '',
    tags: '',
});

const features = ref<FeatureRow[]>([{ title: '', description: '' }]);
const screenshots = ref<string[]>([]);
const slugTouched = ref(false);
const formKey = ref(0);

function slugify(value: string): string {
    return value
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function fillForm(product: Product | null): void {
    form.name = product?.name ?? '';
    form.slug = product?.slug ?? '';
    form.tagline = product?.tagline ?? '';
    form.description = product?.description ?? '';
    form.platform = product?.platform ?? 'web';
    form.status = product?.status ?? 'draft';
    form.price = product ? String(product.price / 100) : '0';
    form.version = product?.version ?? '';
    form.requirements = product?.requirements ?? '';
    form.demo_url = product?.demo_url ?? '';
    form.download_url = product?.download_url ?? '';
    form.repository_url = product?.repository_url ?? '';
    form.tags = product?.tags?.map((tag) => tag.name).join(', ') ?? '';

    features.value = product?.features?.length
        ? product.features.map((feature) => ({
              title: feature.title,
              description: feature.description ?? '',
          }))
        : [{ title: '', description: '' }];

    slugTouched.value = Boolean(product?.slug);
}

fillForm(props.product);

watch(
    () => props.product,
    (product) => {
        fillForm(product);
        formKey.value += 1;
    },
);

function onNameInput(): void {
    if (!slugTouched.value) {
        form.slug = slugify(form.name);
    }
}

function onSlugInput(): void {
    slugTouched.value = true;
}

function addFeature(): void {
    features.value.push({ title: '', description: '' });
}

function removeFeature(index: number): void {
    features.value.splice(index, 1);

    if (features.value.length === 0) {
        features.value.push({ title: '', description: '' });
    }
}

function onScreenshotsChange(event: Event): void {
    const input = event.target as HTMLInputElement;

    screenshots.value = Array.from(input.files ?? []).map((file) => file.name);
}
</script>

<template>
    <Form
        :key="formKey"
        :action="action"
        :method="method"
        class="space-y-6"
        v-slot="{ errors, processing }"
    >
        <Card>
            <CardHeader>
                <CardTitle>Basics</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            v-model="form.name"
                            required
                            maxlength="120"
                            placeholder="Pixel Tracker"
                            @input="onNameInput"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="slug">URL slug</Label>
                        <Input
                            id="slug"
                            name="slug"
                            v-model="form.slug"
                            maxlength="140"
                            placeholder="pixel-tracker"
                            @input="onSlugInput"
                        />
                        <InputError :message="errors.slug" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="tagline">Tagline</Label>
                    <Input
                        id="tagline"
                        name="tagline"
                        v-model="form.tagline"
                        required
                        maxlength="180"
                        placeholder="Track every pixel of your screen in seconds"
                    />
                    <InputError :message="errors.tagline" />
                </div>

                <div class="grid gap-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        name="description"
                        v-model="form.description"
                        required
                        rows="6"
                        maxlength="5000"
                        placeholder="Explain what the app does, who it is for and what makes it special."
                    />
                    <InputError :message="errors.description" />
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Listing</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="platform">Platform</Label>
                        <select
                            id="platform"
                            name="platform"
                            v-model="form.platform"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option
                                v-for="platform in platforms"
                                :key="platform.value"
                                :value="platform.value"
                            >
                                {{ platform.label }}
                            </option>
                        </select>
                        <InputError :message="errors.platform" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="status">Status</Label>
                        <select
                            id="status"
                            name="status"
                            v-model="form.status"
                            class="h-9 w-full rounded-md border border-input bg-background px-3 text-sm shadow-xs outline-none focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50"
                        >
                            <option
                                v-for="status in statuses"
                                :key="status.value"
                                :value="status.value"
                            >
                                {{ status.label }}
                            </option>
                        </select>
                        <InputError :message="errors.status" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="price">Price (USD)</Label>
                        <Input
                            id="price"
                            name="price"
                            v-model="form.price"
                            type="number"
                            min="0"
                            max="100000"
                            step="0.01"
                            required
                        />
                        <InputError :message="errors.price" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="version">Version</Label>
                        <Input
                            id="version"
                            name="version"
                            v-model="form.version"
                            maxlength="40"
                            placeholder="1.4.0"
                        />
                        <InputError :message="errors.version" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="requirements">Requirements</Label>
                        <Input
                            id="requirements"
                            name="requirements"
                            v-model="form.requirements"
                            maxlength="255"
                            placeholder="Windows 11+, 4 GB RAM"
                        />
                        <InputError :message="errors.requirements" />
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="grid gap-2">
                        <Label for="demo_url">Demo URL</Label>
                        <Input
                            id="demo_url"
                            name="demo_url"
                            v-model="form.demo_url"
                            type="url"
                            placeholder="https://example.com/demo"
                        />
                        <InputError :message="errors.demo_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="download_url">Download URL</Label>
                        <Input
                            id="download_url"
                            name="download_url"
                            v-model="form.download_url"
                            type="url"
                            placeholder="https://example.com/app.zip"
                        />
                        <InputError :message="errors.download_url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="repository_url">Repository URL</Label>
                        <Input
                            id="repository_url"
                            name="repository_url"
                            v-model="form.repository_url"
                            type="url"
                            placeholder="https://github.com/you/app"
                        />
                        <InputError :message="errors.repository_url" />
                    </div>
                </div>

                <div class="grid gap-2">
                    <Label for="tags">Tags</Label>
                    <Input
                        id="tags"
                        name="tags"
                        v-model="form.tags"
                        maxlength="255"
                        placeholder="design, productivity, saas"
                    />
                    <p class="text-xs text-muted-foreground">
                        Separate tags with commas. New tags are created
                        automatically.
                    </p>
                    <InputError :message="errors.tags" />
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Media</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid gap-2">
                    <Label for="cover_image">Cover image</Label>
                    <input
                        id="cover_image"
                        name="cover_image"
                        type="file"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full rounded-md border border-input bg-background text-sm file:mr-4 file:border-0 file:bg-transparent file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-accent"
                    />
                    <InputError :message="errors.cover_image" />
                </div>

                <div class="grid gap-2">
                    <Label for="screenshots">Screenshots</Label>
                    <input
                        id="screenshots"
                        name="screenshots[]"
                        type="file"
                        multiple
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full rounded-md border border-input bg-background text-sm file:mr-4 file:border-0 file:bg-transparent file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-accent"
                        @change="onScreenshotsChange"
                    />
                    <InputError :message="errors['screenshots.0']" />
                </div>

                <div v-if="screenshots.length" class="space-y-3">
                    <p class="text-sm font-medium">Captions</p>
                    <div
                        v-for="(name, index) in screenshots"
                        :key="`${name}-${index}`"
                        class="grid gap-2"
                    >
                        <Label :for="`screenshot_caption_${index}`">
                            {{ name }}
                        </Label>
                        <Input
                            :id="`screenshot_caption_${index}`"
                            :name="`screenshot_captions[${index}]`"
                            maxlength="160"
                            placeholder="Optional caption"
                        />
                    </div>
                </div>

                <div v-if="product?.screenshots?.length" class="space-y-2">
                    <p class="text-sm font-medium">Published screenshots</p>
                    <ul class="space-y-1 text-sm text-muted-foreground">
                        <li
                            v-for="screenshot in product.screenshots"
                            :key="screenshot.id"
                        >
                            {{ screenshot.caption ?? 'Untitled screenshot' }}
                        </li>
                    </ul>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle>Features</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
                <div
                    v-for="(feature, index) in features"
                    :key="index"
                    class="space-y-2 rounded-lg border p-4"
                >
                    <div class="flex items-center justify-between gap-2">
                        <Label :for="`feature_title_${index}`">
                            Feature {{ index + 1 }}
                        </Label>
                        <Button
                            type="button"
                            variant="ghost"
                            size="icon-sm"
                            aria-label="Remove feature"
                            @click="removeFeature(index)"
                        >
                            <Trash2 />
                        </Button>
                    </div>

                    <Input
                        :id="`feature_title_${index}`"
                        :name="`features[${index}][title]`"
                        v-model="feature.title"
                        maxlength="120"
                        placeholder="Offline mode"
                    />
                    <InputError :message="errors[`features.${index}.title`]" />

                    <Textarea
                        :id="`feature_description_${index}`"
                        :name="`features[${index}][description]`"
                        v-model="feature.description"
                        rows="2"
                        maxlength="255"
                        placeholder="Describe what this feature does"
                    />
                    <InputError
                        :message="errors[`features.${index}.description`]"
                    />
                </div>

                <Button
                    type="button"
                    variant="outline"
                    size="sm"
                    @click="addFeature"
                >
                    <Plus />
                    Add feature
                </Button>
            </CardContent>
        </Card>

        <div class="flex items-center gap-4">
            <Button type="submit" :disabled="processing">
                <Save />
                {{ processing ? 'Saving...' : submitLabel }}
            </Button>
        </div>
    </Form>
</template>
