<?php

namespace JustBetter\StatamicContentUsage\Data;

use Illuminate\Support\Collection;

class EntryUsageData
{
    /**
     * @param  Collection<int, EntryPageUsageData>  $pages
     */
    public function __construct(
        public string $entryId,
        public string $entryTitle,
        public string $entryUrl,
        public string $entryCollection,
        public Collection $pages,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'entry_id' => $this->entryId,
            'entry_title' => $this->entryTitle,
            'entry_url' => $this->entryUrl,
            'entry_collection' => $this->entryCollection,
            'pages' => $this->pages->map(fn (EntryPageUsageData $page) => $page->toArray())->all(),
        ];
    }
}
