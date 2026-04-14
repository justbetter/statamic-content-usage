import AssetUsageWidget from './components/widgets/AssetUsageWidget.vue';
import EntryUsageWidget from './components/widgets/EntryUsageWidget.vue';

Statamic.booting(() => {
    Statamic.$components.register('content-usage-asset-widget', AssetUsageWidget);
    Statamic.$components.register('content-usage-entry-widget', EntryUsageWidget);
});
