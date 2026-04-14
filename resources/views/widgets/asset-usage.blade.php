<content-usage-asset-widget
    title="{{ __('statamic-content-usage::widgets.asset-usage.heading') }}"
    description="{{ __('statamic-content-usage::widgets.asset-usage.description') }}"
    containers-label="{{ __('statamic-content-usage::widgets.asset-usage.containers_label') }}"
    containers-instructions="{{ __('statamic-content-usage::widgets.asset-usage.containers_instructions') }}"
    no-containers-message="{{ __('statamic-content-usage::widgets.asset-usage.no_containers') }}"
    export-button-label="{{ __('statamic-content-usage::widgets.asset-usage.export_button') }}"
    export-unused-button-label="{{ __('statamic-content-usage::widgets.asset-usage.export_unused_button') }}"
    export-url="{{ $exportUrl }}"
    export-unused-url="{{ $exportUnusedUrl }}"
    :containers='{{ json_encode($containers->values()) }}'
></content-usage-asset-widget>
