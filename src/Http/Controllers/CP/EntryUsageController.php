<?php

namespace JustBetter\StatamicContentUsage\Http\Controllers\CP;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use JustBetter\StatamicContentUsage\Exporters\EntryUsageExporter;
use JustBetter\StatamicContentUsage\Exporters\UnusedEntryExporter;
use JustBetter\StatamicContentUsage\Http\Requests\ExportEntryUsageRequest;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;
use Statamic\Http\Controllers\CP\CpController;

class EntryUsageController extends CpController
{
    public function exportEntryUsage(ExportEntryUsageRequest $request, EntryUsageService $service, EntryUsageExporter $usedEntryExporter, UnusedEntryExporter $unusedEntryExporter): Response|RedirectResponse
    {
        /** @var array{collection: string, export_type: string} $validated */
        $validated = $request->validated();
        $collectionHandle = $validated['collection'];
        $exportType = $validated['export_type'];

        if ($exportType === 'used') {
            $csvData = $service->findEntryUsage($collectionHandle);
            $exporter = $usedEntryExporter;
        } else {
            $csvData = $service->findUnusedEntries($collectionHandle);
            $exporter = $unusedEntryExporter;
        }

        if ($csvData->isEmpty()) {
            return redirect()->back()->with('error', "No {$exportType} entries found for collection '{$collectionHandle}'.");
        }

        $filename = 'entry_'.$exportType.'_'.$collectionHandle.'_'.now()->format('Y-m-d_His').'.csv';
        $content = $exporter->exportToCsv($csvData);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
