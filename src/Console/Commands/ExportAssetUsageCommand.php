<?php

namespace JustBetter\StatamicContentUsage\Console\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicContentUsage\Exporters\AssetUsageExporter;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;

class ExportAssetUsageCommand extends Command
{
    protected $signature = 'content-usage:export-assets
                            {--output= : Output file path (optional)}';

    protected $description = 'Export asset usage to CSV';

    public function handle(AssetUsageService $service, AssetUsageExporter $exporter): int
    {
        $this->info('Scanning entries for asset usage...');

        $usage = $service->findAssetUsage();

        if ($usage->isEmpty()) {
            $this->warn('No asset usage found.');

            return self::SUCCESS;
        }

        $this->info("Found {$usage->count()} unique assets.");

        $outputPath = $this->option('output');
        if (! is_string($outputPath)) {
            $outputPath = $this->getDefaultOutputPath();
        }

        $content = $exporter->exportToCsv($usage);
        file_put_contents($outputPath, $content);
        $this->info("Asset usage exported to: {$outputPath}");

        return self::SUCCESS;
    }

    protected function getDefaultOutputPath(): string
    {
        $filename = 'asset_usage_'.now()->format('Y-m-d_His').'.csv';

        return storage_path('app/'.$filename);
    }
}
