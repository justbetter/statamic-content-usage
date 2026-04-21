<template>
    <Widget :title="t('statamic-content-usage::widgets.asset-usage.heading')">
        <div class="p-4 flex flex-col gap-4">
            <Text>
                {{ t('statamic-content-usage::widgets.asset-usage.description') }}
            </Text>

            <div class="space-y-2">
                <Text class="font-medium">{{ t('statamic-content-usage::widgets.asset-usage.containers_label') }}</Text>

                <Text v-if="!hasContainers">
                    {{ t('statamic-content-usage::widgets.asset-usage.no_containers') }}
                </Text>

                <template v-else>
                    <Text>
                        {{ t('statamic-content-usage::widgets.asset-usage.containers_instructions') }}
                    </Text>

                    <Select
                        v-model="selectedContainers"
                        :options="containerOptions"
                        multiple
                    />
                </template>
            </div>

            <div class="flex flex-col gap-2 pt-1">
                <Button
                    variant="primary"
                    :disabled="!hasContainers"
                    @click="exportSelected"
                >
                    {{ t('statamic-content-usage::widgets.asset-usage.export_button') }}
                </Button>

                <Button
                    variant="secondary"
                    :disabled="!hasContainers"
                    @click="exportUnused"
                >
                    {{ t('statamic-content-usage::widgets.asset-usage.export_unused_button') }}
                </Button>
            </div>
        </div>
    </Widget>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Button, Select, Text, Widget } from '@statamic/cms/ui';

const {
    exportUrl,
    exportUnusedUrl,
    containers,
} = defineProps({
    exportUrl: {
        type: String,
        required: true,
    },
    exportUnusedUrl: {
        type: String,
        required: true,
    },
    containers: {
        type: Array,
        required: true,
    },
});

const selectedContainers = ref(containers.map((container) => container.handle));

const hasContainers = containers.length > 0;
const t = (key) => typeof window.__ === 'function' ? window.__(key) : key;

const containerOptions = computed(() => containers.map((container) => ({
    value: container.handle,
    label: container.title,
})));

const exportSelected = () => {
    if (!hasContainers) {
        return;
    }

    const query = new URLSearchParams();
    selectedContainers.value.forEach((handle) => {
        query.append('containers[]', handle);
    });

    window.location.assign(`${exportUrl}?${query.toString()}`);
};

const exportUnused = () => {
    if (!hasContainers) {
        return;
    }

    const query = new URLSearchParams();
    selectedContainers.value.forEach((handle) => {
        query.append('containers[]', handle);
    });

    window.location.assign(`${exportUnusedUrl}?${query.toString()}`);
};
</script>
