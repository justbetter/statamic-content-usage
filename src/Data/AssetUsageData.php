<?php

namespace JustBetter\StatamicContentUsage\Data;

use Illuminate\Support\Collection;

class AssetUsageData
{
    /**
     * @param  Collection<int, PageUsageData>  $pages
     */
    public function __construct(
        public string $assetId,
        public string $assetPath,
        public string $assetUrl,
        public string $assetBasename,
        public Collection $pages,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'asset_id' => $this->assetId,
            'asset_path' => $this->assetPath,
            'asset_url' => $this->assetUrl,
            'asset_basename' => $this->assetBasename,
            'pages' => $this->pages->map(fn (PageUsageData $page) => $page->toArray())->all(),
        ];
    }
}
