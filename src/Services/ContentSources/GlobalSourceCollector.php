<?php

namespace JustBetter\StatamicContentUsage\Services\ContentSources;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use Statamic\Facades\GlobalSet as GlobalSetFacade;
use Statamic\Globals\GlobalSet;
use Statamic\Globals\Variables;

class GlobalSourceCollector
{
    /**
     * @return Collection<int, ContentSourceData>
     */
    public function collect(): Collection
    {
        /** @var Collection<int, ContentSourceData> $sources */
        $sources = collect();
        /** @var Collection<int, GlobalSet> $globalSets */
        $globalSets = GlobalSetFacade::all();

        foreach ($globalSets as $globalSet) {
            foreach ($globalSet->localizations() as $variables) {
                if (! $variables instanceof Variables) {
                    continue;
                }

                $globalHandle = $variables->handle();
                $globalSite = $variables->locale();

                $sources->push(new ContentSourceData(
                    id: "global::{$globalHandle}::{$globalSite}",
                    title: "Global: {$globalHandle} ({$globalSite})",
                    url: $variables->editUrl(),
                    collection: 'globals',
                    data: $variables->data()->all(),
                ));
            }
        }

        return $sources;
    }
}
