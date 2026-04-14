<?php

namespace JustBetter\StatamicContentUsage\Services;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use Statamic\Entries\Entry;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry as EntryFacade;

class EntryUsageService
{
    /**
     * @return Collection<int, EntryUsageData>
     */
    public function findEntryUsage(string $collectionHandle): Collection
    {
        /** @var ?\Statamic\Entries\Collection $sourceCollection */
        $sourceCollection = CollectionFacade::findByHandle($collectionHandle);

        if (! $sourceCollection) {
            return collect();
        }

        /** @var Collection<int, Entry> $sourceEntries */
        $sourceEntries = EntryFacade::whereCollection($collectionHandle);

        if ($sourceEntries->isEmpty()) {
            return collect();
        }

        /** @var Collection<string, EntryUsageData> $usage */
        $usage = collect();

        /** @var Collection<int, Entry> $allEntries */
        $allEntries = EntryFacade::all();

        foreach ($allEntries as $entry) {
            $this->processEntry($entry, $sourceEntries, $usage);
        }

        return $usage->map(function (EntryUsageData $item) {
            $item->pages = $item->pages->unique(fn (EntryPageUsageData $page) => $page->entryId)->values();

            return $item;
        })->values();
    }

    /**
     * @param  Collection<int, Entry>  $sourceEntries
     * @param  Collection<string, EntryUsageData>  $usage
     */
    protected function processEntry(Entry $entry, Collection $sourceEntries, Collection $usage): void
    {
        $entryId = $entry->id();
        $entryTitle = $entry->get('title', '');
        $entryUrl = $entry->url() ?? '';
        $entryCollection = $entry->collection()->handle();

        $referencedEntryIds = $this->extractEntryReferencesFromEntry($entry, $sourceEntries);

        foreach ($referencedEntryIds as $referencedEntryId) {
            $this->processReferencedEntry($referencedEntryId, $entryId, $entryTitle, $entryUrl, $entryCollection, $usage);
        }
    }

    /**
     * @param  Collection<string, EntryUsageData>  $usage
     */
    protected function processReferencedEntry(
        string $referencedEntryId,
        string $entryId,
        string $entryTitle,
        string $entryUrl,
        string $entryCollection,
        Collection $usage
    ): void {
        /** @var ?Entry $referencedEntry */
        $referencedEntry = EntryFacade::find($referencedEntryId);

        if (! $referencedEntry) {
            return;
        }

        $entryData = $this->getOrCreateEntryUsageData($referencedEntry, $referencedEntryId, $usage);
        $this->addPageUsageToEntry($entryData, $entryId, $entryTitle, $entryUrl, $entryCollection);
    }

    /**
     * @param  Collection<string, EntryUsageData>  $usage
     */
    protected function getOrCreateEntryUsageData(Entry $entry, string $entryId, Collection $usage): EntryUsageData
    {
        if ($usage->has($entryId)) {
            /** @var EntryUsageData $entryData */
            $entryData = $usage->get($entryId);

            return $entryData;
        }

        /** @var string $entryTitle */
        $entryTitle = $entry->get('title', '');
        /** @var string $entryUrl */
        $entryUrl = $entry->url() ?? '';
        /** @var string $entryCollection */
        $entryCollection = $entry->collection()->handle();

        $entryData = new EntryUsageData(
            entryId: $entryId,
            entryTitle: $entryTitle,
            entryUrl: $entryUrl,
            entryCollection: $entryCollection,
            pages: collect(),
        );

        $usage->put($entryId, $entryData);

        return $entryData;
    }

    protected function addPageUsageToEntry(
        EntryUsageData $entryData,
        string $entryId,
        string $entryTitle,
        string $entryUrl,
        string $entryCollection
    ): void {
        $entryData->pages->push(new EntryPageUsageData(
            entryId: $entryId,
            entryTitle: $entryTitle,
            entryUrl: $entryUrl,
            entryCollection: $entryCollection,
        ));
    }

    /**
     * @param  Collection<int, Entry>  $sourceEntries
     * @return Collection<int, string>
     */
    protected function extractEntryReferencesFromEntry(Entry $entry, Collection $sourceEntries): Collection
    {
        /** @var Collection<int, string> $entryIds */
        $entryIds = collect();
        /** @var array<string, mixed> $data */
        $data = $entry->data()->all();

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return $entryIds;
        }

        /** @var Collection<int, string> $sourceEntryIds */
        $sourceEntryIds = $sourceEntries->map(fn (Entry $entry) => $entry->id());

        // Find entry:: prefixed references (format: entry::collection::id)
        preg_match_all('/entry::[^"\'\\s}]+/', $json, $entryMatches);

        if (! empty($entryMatches[0])) {
            foreach ($entryMatches[0] as $match) {
                // Extract the ID from entry::collection::id format
                $parts = explode('::', $match);
                if (count($parts) === 3) {
                    $entryId = $parts[2];
                    if ($sourceEntryIds->contains($entryId)) {
                        $entryIds->push($entryId);
                    }
                }
            }
        }

        // Find plain entry IDs (UUIDs) - check if they match any source entry IDs
        preg_match_all('/"([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})"/i', $json, $uuidMatches);

        if (! empty($uuidMatches[1])) {
            foreach ($uuidMatches[1] as $uuid) {
                if ($sourceEntryIds->contains($uuid)) {
                    $entryIds->push($uuid);
                }
            }
        }

        return $entryIds->unique()->values();
    }

    /**
     * @return Collection<int, Entry>
     */
    public function findUnusedEntries(string $collectionHandle): Collection
    {
        /** @var ?\Statamic\Entries\Collection $sourceCollection */
        $sourceCollection = CollectionFacade::findByHandle($collectionHandle);

        if (! $sourceCollection) {
            return collect();
        }

        /** @var Collection<int, Entry> $sourceEntries */
        $sourceEntries = EntryFacade::whereCollection($collectionHandle);

        if ($sourceEntries->isEmpty()) {
            return collect();
        }

        $usedEntryIds = $this->findEntryUsage($collectionHandle)->pluck('entryId');

        /** @var Collection<int, Entry> $unusedEntries */
        $unusedEntries = collect();

        foreach ($sourceEntries as $entry) {
            if (! $usedEntryIds->contains($entry->id())) {
                $unusedEntries->push($entry);
            }
        }

        return $unusedEntries;
    }
}
