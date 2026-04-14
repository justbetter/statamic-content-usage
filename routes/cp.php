<?php

use Illuminate\Support\Facades\Route;
use JustBetter\StatamicContentUsage\Http\Controllers\CP\AssetUsageController;
use JustBetter\StatamicContentUsage\Http\Controllers\CP\EntryUsageController;

Route::get('content-usage/export-assets', [AssetUsageController::class, 'exportAssets'])
    ->name('content-usage.export-assets');

Route::get('content-usage/export-unused-assets', [AssetUsageController::class, 'exportUnusedAssets'])
    ->name('content-usage.export-unused-assets');

Route::get('content-usage/export-entry-usage', [EntryUsageController::class, 'exportEntryUsage'])
    ->name('content-usage.export-entry-usage');
