<template>
    <Widget :title="title">
        <div class="p-4 flex flex-col gap-4">
            <Text>
                {{ description }}
            </Text>

            <div class="space-y-2">
                <Text class="font-medium">{{ collectionLabel }}</Text>

                <Select
                    v-model="selectedCollection"
                    :options="collectionOptions"
                    :placeholder="selectCollectionLabel"
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
                {{ exportButtonLabel }}
            </Button>
        </div>
    </Widget>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Button, Select, Text, Widget } from '@statamic/cms/ui';

const {
    title,
    description,
    collectionLabel,
    selectCollectionLabel,
    exportButtonLabel,
    exportUsedLabel,
    exportUnusedLabel,
    exportUrl,
    collections,
} = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    collectionLabel: {
        type: String,
        required: true,
    },
    selectCollectionLabel: {
        type: String,
        required: true,
    },
    exportButtonLabel: {
        type: String,
        required: true,
    },
    exportUsedLabel: {
        type: String,
        required: true,
    },
    exportUnusedLabel: {
        type: String,
        required: true,
    },
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

const collectionOptions = computed(() => collections.map((collection) => ({
    value: collection.handle,
    label: collection.title,
})));

const exportTypeOptions = computed(() => [
    { value: 'used', label: exportUsedLabel },
    { value: 'unused', label: exportUnusedLabel },
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
