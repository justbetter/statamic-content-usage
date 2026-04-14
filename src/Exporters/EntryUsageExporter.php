<?php

namespace JustBetter\StatamicContentUsage\Exporters;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use League\Csv\Writer;

class EntryUsageExporter
{
    /**
     * @param  Collection<int, EntryUsageData>  $usage
     */
    public function exportToCsv(Collection $usage): string
    {
        $writer = Writer::createFromString('');
        $writer->setDelimiter(',');

        $headers = [
            'Entry Title',
            'Entry URL',
            'Entry Collection',
            'Page Title',
            'Page URL',
            'Page Collection',
        ];

        $writer->insertOne($headers);

        foreach ($usage as $entryData) {
            /** @var EntryPageUsageData $page */
            foreach ($entryData->pages as $page) {
                $writer->insertOne([
                    $entryData->entryTitle,
                    $entryData->entryUrl,
                    $entryData->entryCollection,
                    $page->entryTitle,
                    $page->entryUrl,
                    $page->entryCollection,
                ]);
            }
        }

        return (string) $writer;
    }
}
