<?php

namespace JustBetter\StatamicContentUsage\Widgets;

use Illuminate\Support\Collection;
use Illuminate\View\View;
use Statamic\Entries\Collection as EntryCollection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Widgets\Widget;

class EntryUsageWidget extends Widget
{
    /** @var string */
    protected static $handle = 'entry_usage';

    public function html(): string|View
    {
        $exportUrl = cp_route('content-usage.export-entry-usage');
        /** @var Collection<int, EntryCollection> $allCollections */
        $allCollections = CollectionFacade::all();
        $collections = $allCollections->map(function (EntryCollection $collection) {
            return [
                'handle' => $collection->handle(),
                'title' => $collection->title(),
            ];
        })->sortBy('title')->values();

        return view('statamic-content-usage::widgets.entry-usage', [ // @phpstan-ignore-line
            'exportUrl' => $exportUrl,
            'collections' => $collections,
        ])->render();
    }

    public static function title(): string
    {
        return __('statamic-content-usage::widgets.entry-usage.title');
    }
}
