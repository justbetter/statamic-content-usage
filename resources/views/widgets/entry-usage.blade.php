<content-usage-entry-widget
    title="{{ __('statamic-content-usage::widgets.entry-usage.heading') }}"
    description="{{ __('statamic-content-usage::widgets.entry-usage.description') }}"
    collection-label="{{ __('statamic-content-usage::widgets.entry-usage.collection_label') }}"
    select-collection-label="{{ __('statamic-content-usage::widgets.entry-usage.select_collection') }}"
    export-button-label="{{ __('statamic-content-usage::widgets.entry-usage.export_button') }}"
    export-used-label="{{ __('statamic-content-usage::widgets.entry-usage.export_used_button') }}"
    export-unused-label="{{ __('statamic-content-usage::widgets.entry-usage.export_unused_button') }}"
    export-url="{{ $exportUrl }}"
    :collections='{{ json_encode($collections->values()) }}'
></content-usage-entry-widget>
