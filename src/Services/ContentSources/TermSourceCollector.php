<?php

namespace JustBetter\StatamicContentUsage\Services\ContentSources;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use Statamic\Facades\Term as TermFacade;
use Statamic\Taxonomies\Term;

class TermSourceCollector
{
    /**
     * @return Collection<int, ContentSourceData>
     */
    public function collect(): Collection
    {
        /** @var Collection<int, ContentSourceData> $sources */
        $sources = collect();
        $terms = TermFacade::all();

        foreach ($terms as $term) {
            if (! $term instanceof Term) {
                continue;
            }

            foreach ($term->localizations() as $site => $localizedTerm) {
                $termId = $term->id();

                $sources->push(new ContentSourceData(
                    id: "term::{$termId}::{$site}",
                    title: "Term: {$term->taxonomyHandle()} / {$term->slug()} ({$site})",
                    url: '',
                    collection: "taxonomy:{$term->taxonomyHandle()}",
                    data: $localizedTerm->data()->all(),
                ));
            }
        }

        return $sources;
    }
}
