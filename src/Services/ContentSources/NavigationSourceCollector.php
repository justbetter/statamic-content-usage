<?php

namespace JustBetter\StatamicContentUsage\Services\ContentSources;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use Statamic\Facades\Nav as NavFacade;
use Statamic\Structures\Nav;
use Statamic\Structures\NavTree;

class NavigationSourceCollector
{
    /**
     * @return Collection<int, ContentSourceData>
     */
    public function collect(): Collection
    {
        /** @var Collection<int, ContentSourceData> $sources */
        $sources = collect();
        $navigations = NavFacade::all();

        foreach ($navigations as $navigation) {
            if (! $navigation instanceof Nav) {
                continue;
            }

            foreach ($navigation->trees() as $site => $tree) {
                if (! $tree instanceof NavTree) {
                    continue;
                }

                /** @var array<int, array<string, mixed>> $branches */
                $branches = $tree->tree();
                $this->collectBranches(
                    branches: $branches,
                    navHandle: $navigation->handle(),
                    site: (string) $site,
                    sources: $sources,
                );
            }
        }

        return $sources;
    }

    /**
     * @param  array<int, array<string, mixed>>  $branches
     * @param  Collection<int, ContentSourceData>  $sources
     */
    protected function collectBranches(array $branches, string $navHandle, string $site, Collection $sources): void
    {
        foreach ($branches as $branch) {
            $nodeRaw = $branch['id'] ?? $branch['entry'] ?? null;
            if (! is_string($nodeRaw) && ! is_int($nodeRaw)) {
                $nodeRaw = '';
            }

            $nodeId = (string) $nodeRaw;
            if ($nodeId !== '') {
                $titleRaw = $branch['title'] ?? "Navigation: {$navHandle}";
                $urlRaw = $branch['url'] ?? '';

                $sources->push(new ContentSourceData(
                    id: "navigation::{$navHandle}::{$site}::{$nodeId}",
                    title: is_string($titleRaw) ? $titleRaw : "Navigation: {$navHandle}",
                    url: is_string($urlRaw) ? $urlRaw : '',
                    collection: "navigation:{$navHandle}",
                    data: [
                        'entry' => $branch['entry'] ?? null,
                        'url' => $branch['url'] ?? null,
                        'title' => $branch['title'] ?? null,
                        'data' => $branch['data'] ?? [],
                    ],
                ));
            }

            $children = $branch['children'] ?? [];
            if (is_array($children) && $children !== []) {
                /** @var array<int, array<string, mixed>> $childrenBranches */
                $childrenBranches = $children;
                $this->collectBranches($childrenBranches, $navHandle, $site, $sources);
            }
        }
    }
}
