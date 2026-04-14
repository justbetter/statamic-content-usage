<?php

namespace JustBetter\StatamicContentUsage\Console\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicContentUsage\Exporters\UnusedAssetExporter;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;

class ExportUnusedAssetsCommand extends Command
{
    protected $signature = 'content-usage:export-unused-assets
                            {--containers= : Asset container handles, comma separated (optional)}
                            {--output= : Output file path (optional)}';

    protected $description = 'Export unused assets to CSV';

    public function handle(AssetUsageService $service, UnusedAssetExporter $exporter): int
    {
        $containersOption = $this->option('containers');
        /** @var list<string>|null $containerHandles */
        $containerHandles = is_string($containersOption) && $containersOption !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $containersOption))))
            : null;

        if ($containerHandles !== null) {
            $this->info('Scanning entries for unused assets in containers: '.implode(', ', $containerHandles));
        } else {
            $this->info('Scanning entries for unused assets in all containers...');
        }

        $unusedAssets = $service->findUnusedAssets($containerHandles);

        if ($unusedAssets->isEmpty()) {
            $this->warn('No unused assets found.');

            return self::SUCCESS;
        }

        $this->info("Found {$unusedAssets->count()} unused assets.");

        $outputPath = $this->option('output');
        if (! is_string($outputPath)) {
            $outputPath = $this->getDefaultOutputPath($containerHandles);
        }

        $content = $exporter->exportToCsv($unusedAssets);
        file_put_contents($outputPath, $content);
        $this->info("Unused assets exported to: {$outputPath}");

        return self::SUCCESS;
    }

    /**
     * @param  list<string>|null  $containerHandles
     */
    protected function getDefaultOutputPath(?array $containerHandles): string
    {
        $suffix = $containerHandles !== null ? '_'.implode('_', $containerHandles) : '';
        $filename = 'unused_assets'.$suffix.'_'.now()->format('Y-m-d_His').'.csv';

        return storage_path('app/'.$filename);
    }
}
