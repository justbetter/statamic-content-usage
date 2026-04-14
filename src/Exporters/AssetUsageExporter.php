<?php

namespace JustBetter\StatamicContentUsage\Exporters;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use League\Csv\Writer;

class AssetUsageExporter
{
    /**
     * @param  Collection<int, AssetUsageData>  $usage
     */
    public function exportToCsv(Collection $usage): string
    {
        $writer = Writer::createFromString('');
        $writer->setDelimiter(',');

        $headers = [
            'Asset Path',
            'Asset URL',
            'Asset Basename',
            'Page Title',
            'Page URL',
            'Collection',
        ];

        $writer->insertOne($headers);

        foreach ($usage as $assetData) {
            /** @var PageUsageData $page */
            foreach ($assetData->pages as $page) {
                $writer->insertOne([
                    $assetData->assetPath,
                    $assetData->assetUrl,
                    $assetData->assetBasename,
                    $page->entryTitle,
                    $page->entryUrl,
                    $page->entryCollection,
                ]);
            }
        }

        return (string) $writer;
    }
}
