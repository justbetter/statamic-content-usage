<?php

namespace JustBetter\StatamicContentUsage\Services\ContentSources;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use Statamic\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class EntrySourceCollector
{
    /**
     * @return Collection<int, ContentSourceData>
     */
    public function collect(): Collection
    {
        /** @var Collection<int, Entry> $entries */
        $entries = EntryFacade::all();

        return $entries->map(function (Entry $entry): ContentSourceData {
            return new ContentSourceData(
                id: $entry->id(),
                title: (string) $entry->get('title', ''),
                url: (string) ($entry->url() ?? ''),
                collection: $entry->collection()->handle(),
                data: $entry->data()->all(),
            );
        })->values();
    }
}
