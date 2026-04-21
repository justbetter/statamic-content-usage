<?php

namespace JustBetter\StatamicContentUsage\Data;

class ContentSourceData
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $url,
        public string $collection,
        public array $data,
    ) {}
}
