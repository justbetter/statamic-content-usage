<?php

namespace JustBetter\StatamicContentUsage\Services;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\ContentSourceData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class AssetUsageService
{
    /**
     * @param  array<int, string>|null  $containerHandles
     * @return Collection<int, AssetUsageData>
     */
    public function findAssetUsage(?array $containerHandles = null): Collection
    {
        /** @var Collection<string, AssetUsageData> $usage */
        $usage = collect();

        $sources = (new ContentSourceScanner)->scan();

        foreach ($sources as $source) {
            $this->processSource($source, $usage);
        }

        $result = $usage->map(function (AssetUsageData $item) {
            $item->pages = $item->pages->unique(fn (PageUsageData $page) => $page->entryId)->values();

            return $item;
        })->values();

        if ($containerHandles !== null && $containerHandles !== []) {
            $result = $result->filter(function (AssetUsageData $item) use ($containerHandles): bool {
                /** @var ?Asset $asset */
                $asset = AssetFacade::find($item->assetId);
                if (! $asset) {
                    return false;
                }

                return in_array($asset->container()->handle(), $containerHandles, true);
            });
        }

        return $result;
    }

    /**
     * @param  Collection<string, AssetUsageData>  $usage
     */
    protected function processSource(ContentSourceData $source, Collection $usage): void
    {
        $assets = $this->extractAssetsFromDataArray($source->data);

        foreach ($assets as $assetId) {
            $this->processAsset($assetId, $source->id, $source->title, $source->url, $source->collection, $usage);
        }
    }

    /**
     * @param  Collection<string, AssetUsageData>  $usage
     */
    protected function processAsset(
        string $assetId,
        string $entryId,
        string $entryTitle,
        string $entryUrl,
        string $entryCollection,
        Collection $usage
    ): void {
        /** @var ?Asset $asset */
        $asset = AssetFacade::find($assetId);

        if (! $asset) {
            return;
        }

        $assetData = $this->getOrCreateAssetUsageData($asset, $assetId, $usage);
        $this->addPageUsageToAsset($assetData, $entryId, $entryTitle, $entryUrl, $entryCollection);
    }

    /**
     * @param  Collection<string, AssetUsageData>  $usage
     */
    protected function getOrCreateAssetUsageData(Asset $asset, string $assetId, Collection $usage): AssetUsageData
    {
        if ($usage->has($assetId)) {
            /** @var AssetUsageData $assetData */
            $assetData = $usage->get($assetId);

            return $assetData;
        }

        /** @var string $assetPath */
        $assetPath = $asset->path();
        /** @var string $assetUrl */
        $assetUrl = $asset->url();
        /** @var string $assetBasename */
        $assetBasename = $asset->basename();

        $assetData = new AssetUsageData(
            assetId: $assetId,
            assetPath: $assetPath,
            assetUrl: $assetUrl,
            assetBasename: $assetBasename,
            pages: collect(),
        );

        $usage->put($assetId, $assetData);

        return $assetData;
    }

    protected function addPageUsageToAsset(
        AssetUsageData $assetData,
        string $entryId,
        string $entryTitle,
        string $entryUrl,
        string $entryCollection
    ): void {
        $assetData->pages->push(new PageUsageData(
            entryId: $entryId,
            entryTitle: $entryTitle,
            entryUrl: $entryUrl,
            entryCollection: $entryCollection,
        ));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, string>
     */
    protected function extractAssetsFromDataArray(array $data): Collection
    {
        /** @var Collection<int, string> $assets */
        $assets = collect();

        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return $assets;
        }

        // Find assets:: prefixed references
        preg_match_all('/assets::[^"\'\\s}]+/', $json, $assetMatches);

        if (! empty($assetMatches[0])) {
            foreach ($assetMatches[0] as $match) {
                $assets->push($match);
            }
        }

        // Find plain asset paths (format: "fieldname":"path/to/file.ext")
        preg_match_all('/"([^"]+)"\s*:\s*"([^"]*\/[^"]*\.[a-zA-Z0-9]{2,5})"/', $json, $pathMatches);

        if (! empty($pathMatches[2])) {
            foreach ($pathMatches[2] as $path) {
                // Try to resolve the path to an asset ID
                $assetId = $this->resolvePathToAssetId($path);
                if ($assetId !== null) {
                    $assets->push($assetId);
                }
            }
        }

        return $assets->unique()->values();
    }

    protected function resolvePathToAssetId(string $path): ?string
    {
        $path = trim($path, '/');

        /** @var Collection<int, AssetContainer> $containers */
        $containers = AssetContainerFacade::all();

        foreach ($containers as $container) {
            /** @var ?Asset $asset */
            $asset = AssetFacade::find("{$container->handle()}::{$path}");

            if ($asset) {
                return $asset->id();
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>|null  $containerHandles
     * @return Collection<int, Asset>
     */
    public function findUnusedAssets(?array $containerHandles = null): Collection
    {
        $usedAssetIds = $this->findAssetUsage()->pluck('assetId');

        /** @var Collection<int, Asset> $unusedAssets */
        $unusedAssets = collect();

        /** @var Collection<int, AssetContainer> $containers */
        $containers = AssetContainerFacade::all();

        if ($containerHandles !== null && $containerHandles !== []) {
            $containers = $containers->filter(function (AssetContainer $container) use ($containerHandles): bool {
                return in_array($container->handle(), $containerHandles, true);
            });
        }

        foreach ($containers as $container) {
            /** @var Collection<int, Asset> $assets */
            $assets = $container->assets();

            foreach ($assets as $asset) {
                if (! $usedAssetIds->contains($asset->id())) {
                    $unusedAssets->push($asset);
                }
            }
        }

        return $unusedAssets;
    }
}
