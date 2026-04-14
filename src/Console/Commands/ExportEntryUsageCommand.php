<?php

namespace JustBetter\StatamicContentUsage\Console\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicContentUsage\Exporters\EntryUsageExporter;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;

class ExportEntryUsageCommand extends Command
{
    protected $signature = 'content-usage:export-entries
                            {--collection= : Collection handle to track (required)}
                            {--output= : Output file path (optional)}';

    protected $description = 'Export entry usage to CSV';

    public function handle(EntryUsageService $service, EntryUsageExporter $exporter): int
    {
        $collectionHandle = $this->option('collection');

        if (! is_string($collectionHandle) || empty($collectionHandle)) {
            $this->error('Collection handle is required. Use --collection=handle');

            return self::FAILURE;
        }

        $this->info("Scanning entries for usage of '{$collectionHandle}' collection entries...");

        $usage = $service->findEntryUsage($collectionHandle);

        if ($usage->isEmpty()) {
            $this->warn("No entry usage found for collection '{$collectionHandle}'.");

            return self::SUCCESS;
        }

        $this->info("Found {$usage->count()} unique entries from '{$collectionHandle}' collection.");

        $outputPath = $this->option('output');
        if (! is_string($outputPath)) {
            $outputPath = $this->getDefaultOutputPath($collectionHandle);
        }

        $content = $exporter->exportToCsv($usage);
        file_put_contents($outputPath, $content);
        $this->info("Entry usage exported to: {$outputPath}");

        return self::SUCCESS;
    }

    protected function getDefaultOutputPath(string $collectionHandle): string
    {
        $filename = 'entry_usage_'.$collectionHandle.'_'.now()->format('Y-m-d_His').'.csv';

        return storage_path('app/'.$filename);
    }
}
