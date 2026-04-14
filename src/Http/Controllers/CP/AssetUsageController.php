<?php

namespace JustBetter\StatamicContentUsage\Http\Controllers\CP;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use JustBetter\StatamicContentUsage\Exporters\AssetUsageExporter;
use JustBetter\StatamicContentUsage\Exporters\UnusedAssetExporter;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;
use Statamic\Http\Controllers\CP\CpController;

class AssetUsageController extends CpController
{
    public function exportAssets(Request $request, AssetUsageService $service, AssetUsageExporter $exporter): Response|RedirectResponse
    {
        $containersInput = $request->input('containers');
        /** @var array<int, string>|null $containerHandles */
        $containerHandles = is_array($containersInput) ? $containersInput : null;
        $usage = $service->findAssetUsage($containerHandles);

        if ($usage->isEmpty()) {
            return redirect()->back()->with('error', 'No asset usage found.');
        }

        $filename = 'asset_usage_'.now()->format('Y-m-d_His').'.csv';
        $content = $exporter->exportToCsv($usage);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportUnusedAssets(Request $request, AssetUsageService $service, UnusedAssetExporter $exporter): Response|RedirectResponse
    {
        $containersInput = $request->input('containers');
        /** @var array<int, string>|null $containerHandles */
        $containerHandles = is_array($containersInput) ? $containersInput : null;
        $unusedAssets = $service->findUnusedAssets($containerHandles);

        if ($unusedAssets->isEmpty()) {
            return redirect()->back()->with('error', 'No unused assets found.');
        }

        $filename = 'unused_assets_'.now()->format('Y-m-d_His').'.csv';
        $content = $exporter->exportToCsv($unusedAssets);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
