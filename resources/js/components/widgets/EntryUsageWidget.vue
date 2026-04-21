<template>
    <Widget :title="t('statamic-content-usage::widgets.entry-usage.heading')">
        <div class="p-4 flex flex-col gap-4">
            <Text>
                {{ t('statamic-content-usage::widgets.entry-usage.description') }}
            </Text>

            <div class="space-y-2">
                <Text class="font-medium">{{ t('statamic-content-usage::widgets.entry-usage.collection_label') }}</Text>

                <Select
                    v-model="selectedCollection"
                    :options="collectionOptions"
                    :placeholder="t('statamic-content-usage::widgets.entry-usage.select_collection')"
                />
            </div>

            <Select
                v-model="exportType"
                :options="exportTypeOptions"
            />

            <Button
                variant="primary"
                :disabled="!selectedCollection"
                @click="exportEntries"
            >
                {{ t('statamic-content-usage::widgets.entry-usage.export_button') }}
            </Button>
        </div>
    </Widget>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Button, Select, Text, Widget } from '@statamic/cms/ui';

const {
    exportUrl,
    collections,
} = defineProps({
    exportUrl: {
        type: String,
        required: true,
    },
    collections: {
        type: Array,
        required: true,
    },
});

const selectedCollection = ref(null);
const exportType = ref('used');

const t = (key) => typeof window.__ === 'function' ? window.__(key) : key;

const collectionOptions = computed(() => collections.map((collection) => ({
    value: collection.handle,
    label: collection.title,
})));

const exportTypeOptions = computed(() => [
    { value: 'used', label: t('statamic-content-usage::widgets.entry-usage.export_used_button') },
    { value: 'unused', label: t('statamic-content-usage::widgets.entry-usage.export_unused_button') },
]);

const exportEntries = () => {
    if (!selectedCollection.value) {
        return;
    }

    const query = new URLSearchParams();
    query.set('collection', selectedCollection.value);
    query.set('export_type', exportType.value);

    window.location.assign(`${exportUrl}?${query.toString()}`);
};
</script>
