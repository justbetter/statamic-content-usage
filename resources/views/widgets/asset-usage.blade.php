<content-usage-asset-widget
    export-url="{{ $exportUrl }}"
    export-unused-url="{{ $exportUnusedUrl }}"
    :containers='{{ json_encode($containers->values()) }}'
></content-usage-asset-widget>
