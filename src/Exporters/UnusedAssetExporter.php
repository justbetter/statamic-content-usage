<?php

namespace JustBetter\StatamicContentUsage\Exporters;

use Illuminate\Support\Collection;
use League\Csv\Writer;
use Statamic\Assets\Asset;

class UnusedAssetExporter
{
    /**
     * @param  Collection<int, Asset>  $assets
     */
    public function exportToCsv(Collection $assets): string
    {
        $writer = Writer::createFromString('');
        $writer->setDelimiter(',');

        $headers = [
            'Asset Path',
            'Asset URL',
            'Asset Basename',
            'Container',
        ];

        $writer->insertOne($headers);

        foreach ($assets as $asset) {
            /** @var string $assetPath */
            $assetPath = $asset->path();
            /** @var string $assetUrl */
            $assetUrl = $asset->url();
            /** @var string $assetBasename */
            $assetBasename = $asset->basename();
            /** @var string $containerHandle */
            $containerHandle = $asset->container()->handle();

            $writer->insertOne([
                $assetPath,
                $assetUrl,
                $assetBasename,
                $containerHandle,
            ]);
        }

        return (string) $writer;
    }
}
