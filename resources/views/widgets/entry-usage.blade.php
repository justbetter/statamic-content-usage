<content-usage-entry-widget
    export-url="{{ $exportUrl }}"
    :collections='{{ json_encode($collections->values()) }}'
></content-usage-entry-widget>
