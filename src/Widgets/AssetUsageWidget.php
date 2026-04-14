<?php

namespace JustBetter\StatamicContentUsage\Widgets;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Widgets\Widget;

class AssetUsageWidget extends Widget
{
    /** @var string */
    protected static $handle = 'asset_usage';

    public function html(): string|View
    {
        $exportUrl = cp_route('content-usage.export-assets');
        $exportUnusedUrl = cp_route('content-usage.export-unused-assets');
        /** @var Collection<int, AssetContainer> $allContainers */
        $allContainers = AssetContainerFacade::all();
        $containers = $allContainers->map(function (AssetContainer $container) {
            return [
                'handle' => $container->handle(),
                'title' => $container->title(),
            ];
        })->sortBy('title')->values();

        return view('statamic-content-usage::widgets.asset-usage', [ // @phpstan-ignore-line
            'exportUrl' => $exportUrl,
            'exportUnusedUrl' => $exportUnusedUrl,
            'containers' => $containers,
        ])->render();
    }

    public static function title(): string
    {
        return __('statamic-content-usage::widgets.asset-usage.title');
    }
}
