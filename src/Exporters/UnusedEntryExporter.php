<?php

namespace JustBetter\StatamicContentUsage\Exporters;

use Illuminate\Support\Collection;
use League\Csv\Writer;
use Statamic\Entries\Entry;

class UnusedEntryExporter
{
    /**
     * @param  Collection<int, Entry>  $entries
     */
    public function exportToCsv(Collection $entries): string
    {
        $writer = Writer::createFromString('');
        $writer->setDelimiter(',');

        $headers = [
            'Entry ID',
            'Entry Title',
            'Entry URL',
            'Collection',
        ];

        $writer->insertOne($headers);

        foreach ($entries as $entry) {
            /** @var string $entryId */
            $entryId = $entry->id();
            /** @var string $entryTitle */
            $entryTitle = $entry->get('title', '');
            /** @var string $entryUrl */
            $entryUrl = $entry->url() ?? '';
            /** @var string $collectionHandle */
            $collectionHandle = $entry->collection()->handle();

            $writer->insertOne([
                $entryId,
                $entryTitle,
                $entryUrl,
                $collectionHandle,
            ]);
        }

        return (string) $writer;
    }
}
