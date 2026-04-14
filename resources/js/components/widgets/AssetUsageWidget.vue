<script setup>
import { computed, ref } from 'vue';
import { Button, Select, Text, Widget } from '@statamic/cms/ui';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        required: true,
    },
    containersLabel: {
        type: String,
        required: true,
    },
    containersInstructions: {
        type: String,
        required: true,
    },
    noContainersMessage: {
        type: String,
        required: true,
    },
    exportButtonLabel: {
        type: String,
        required: true,
    },
    exportUnusedButtonLabel: {
        type: String,
        required: true,
    },
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

const selectedContainers = ref(props.containers.map((container) => container.handle));

const hasContainers = props.containers.length > 0;

const containerOptions = computed(() => props.containers.map((container) => ({
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

    window.location.assign(`${props.exportUrl}?${query.toString()}`);
};

const exportUnused = () => {
    if (!hasContainers) {
        return;
    }

    const query = new URLSearchParams();
    selectedContainers.value.forEach((handle) => {
        query.append('containers[]', handle);
    });

    window.location.assign(`${props.exportUnusedUrl}?${query.toString()}`);
};
</script>

<template>
    <Widget :title="title">
        <div class="p-4 flex flex-col gap-4">
            <Text>
                {{ description }}
            </Text>

            <div class="space-y-2">
                <Text class="font-medium">{{ containersLabel }}</Text>

                <Text v-if="!hasContainers">
                    {{ noContainersMessage }}
                </Text>

                <template v-else>
                    <Text>
                        {{ containersInstructions }}
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
                    {{ exportButtonLabel }}
                </Button>

                <Button
                    variant="secondary"
                    :disabled="!hasContainers"
                    @click="exportUnused"
                >
                    {{ exportUnusedButtonLabel }}
                </Button>
            </div>
        </div>
    </Widget>
</template>
