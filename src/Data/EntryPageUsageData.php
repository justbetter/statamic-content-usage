<?php

namespace JustBetter\StatamicContentUsage\Data;

class EntryPageUsageData
{
    public function __construct(
        public string $entryId,
        public string $entryTitle,
        public string $entryUrl,
        public string $entryCollection,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'entry_id' => $this->entryId,
            'entry_title' => $this->entryTitle,
            'entry_url' => $this->entryUrl,
            'entry_collection' => $this->entryCollection,
        ];
    }
}
