<?php

namespace JustBetter\StatamicContentUsage;

use JustBetter\StatamicContentUsage\Console\Commands\ExportAssetUsageCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportEntryUsageCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportUnusedAssetsCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportUnusedEntriesCommand;
use JustBetter\StatamicContentUsage\Widgets\AssetUsageWidget;
use JustBetter\StatamicContentUsage\Widgets\EntryUsageWidget;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    /** @phpstan-ignore-next-line */
    protected $vite = [
        'input' => [
            'resources/js/statamic-content-usage.js',
            'resources/css/statamic-content-usage.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    protected $commands = [
        ExportAssetUsageCommand::class,
        ExportUnusedAssetsCommand::class,
        ExportEntryUsageCommand::class,
        ExportUnusedEntriesCommand::class,
    ];

    protected $widgets = [
        AssetUsageWidget::class,
        EntryUsageWidget::class,
    ];

    protected $routes = [
        'cp' => __DIR__.'/../routes/cp.php',
    ];

    public function bootAddon(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-content-usage');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-content-usage');
    }
}
