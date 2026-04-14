<?php

namespace JustBetter\StatamicContentUsage\Console\Commands;

use Illuminate\Console\Command;
use JustBetter\StatamicContentUsage\Exporters\UnusedEntryExporter;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;

class ExportUnusedEntriesCommand extends Command
{
    protected $signature = 'content-usage:export-unused-entries
                            {collection : The handle of the collection to find unused entries for}
                            {--output= : Output file path (optional)}';

    protected $description = 'Export unused entries to CSV for a specific collection';

    public function handle(EntryUsageService $service, UnusedEntryExporter $exporter): int
    {
        /** @var string $collectionHandle */
        $collectionHandle = $this->argument('collection');

        $this->info("Scanning entries for unused entries in collection '{$collectionHandle}'...");

        $unusedEntries = $service->findUnusedEntries($collectionHandle);

        if ($unusedEntries->isEmpty()) {
            $this->warn("No unused entries found for collection '{$collectionHandle}'.");

            return self::SUCCESS;
        }

        $this->info("Found {$unusedEntries->count()} unused entries.");

        $outputPath = $this->option('output');
        if (! is_string($outputPath)) {
            $outputPath = $this->getDefaultOutputPath($collectionHandle);
        }

        $content = $exporter->exportToCsv($unusedEntries);
        file_put_contents($outputPath, $content);
        $this->info("Unused entries exported to: {$outputPath}");

        return self::SUCCESS;
    }

    protected function getDefaultOutputPath(string $collectionHandle): string
    {
        $filename = 'unused_entries_'.$collectionHandle.'_'.now()->format('Y-m-d_His').'.csv';

        return storage_path('app/'.$filename);
    }
}
