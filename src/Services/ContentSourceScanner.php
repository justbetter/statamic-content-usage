<?php

namespace JustBetter\StatamicContentUsage\Services;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use JustBetter\StatamicContentUsage\Services\ContentSources\EntrySourceCollector;
use JustBetter\StatamicContentUsage\Services\ContentSources\GlobalSourceCollector;
use JustBetter\StatamicContentUsage\Services\ContentSources\NavigationSourceCollector;
use JustBetter\StatamicContentUsage\Services\ContentSources\TermSourceCollector;

class ContentSourceScanner
{
    /**
     * @return Collection<int, ContentSourceData>
     */
    public function scan(): Collection
    {
        return collect()
            ->merge((new EntrySourceCollector)->collect())
            ->merge((new GlobalSourceCollector)->collect())
            ->merge((new NavigationSourceCollector)->collect())
            ->merge((new TermSourceCollector)->collect())
            ->values();
    }
}
