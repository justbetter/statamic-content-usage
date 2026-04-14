<?php

namespace JustBetter\StatamicContentUsage\Tests\Services;

use JustBetter\StatamicContentUsage\Services\AssetUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Asset as AssetFacade;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Fields\Value;

class AssetUsageServiceTest extends TestCase
{
    /**
     * @param  array<string, mixed>  $data
     */
    protected function createEntryData(array $data): MockInterface
    {
        return $this->mock(Value::class, function (MockInterface $mock) use ($data): void {
            $mock->shouldReceive('all')->andReturn($data);
        });
    }

    protected function createPageCollection(): MockInterface
    {
        return $this->mock(Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('pages');
        });
    }

    protected function createEntry(MockInterface $entryData, MockInterface $collection, string $entryId = 'entry-123', string $title = 'Test Page', string $url = '/test-page'): MockInterface
    {
        return $this->mock(Entry::class, function (MockInterface $mock) use ($entryData, $collection, $entryId, $title, $url): void {
            $mock->shouldReceive('id')->andReturn($entryId);
            $mock->shouldReceive('get')->with('title', '')->andReturn($title);
            $mock->shouldReceive('url')->andReturn($url);
            $mock->shouldReceive('collection')->andReturn($collection);
            $mock->shouldReceive('data')->andReturn($entryData);
        });
    }

    protected function createAsset(string $assetId, string $path = '', string $url = '', string $basename = ''): MockInterface
    {
        return $this->mock(Asset::class, function (MockInterface $mock) use ($assetId, $path, $url, $basename): void {
            if ($path !== '') {
                $mock->shouldReceive('path')->andReturn($path);
            }
            if ($url !== '') {
                $mock->shouldReceive('url')->andReturn($url);
            }
            if ($basename !== '') {
                $mock->shouldReceive('basename')->andReturn($basename);
            }
            $mock->shouldReceive('id')->andReturn($assetId);
        });
    }

    /**
     * @param  array<int, MockInterface>  $assets
     */
    protected function createAssetContainer(string $handle, array $assets = []): MockInterface
    {
        return $this->mock(AssetContainer::class, function (MockInterface $mock) use ($handle, $assets): void {
            $mock->shouldReceive('handle')->andReturn($handle);
            $mock->shouldReceive('assets')->andReturn(collect($assets));
        });
    }

    #[Test]
    public function it_can_find_asset_usage(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::test_container::test-image.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $asset = $this->createAsset('assets::test_container::test-image.jpg', 'test-image.jpg', 'https://example.com/test-image.jpg', 'test-image.jpg');

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::test_container::test-image.jpg')->andReturn($asset);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::test_container::test-image.jpg', $first->assetId);
        $this->assertCount(1, $first->pages);
        $firstPage = $first->pages->first();
        $this->assertNotNull($firstPage);
        $this->assertEquals('entry-123', $firstPage->entryId);
    }

    #[Test]
    public function it_returns_empty_collection_when_no_entries_exist(): void
    {
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([]));

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_skips_assets_that_cannot_be_found(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::test_container::non-existent.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::test_container::non-existent.jpg')->andReturn(null);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_deduplicates_pages_for_the_same_asset(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::test_container::test-image.jpg']);
        $collection = $this->createPageCollection();
        $entry1 = $this->createEntry($entryData, $collection, 'entry-123', 'Test Page 1', '/test-page-1');
        $entry2 = $this->createEntry($entryData, $collection, 'entry-123', 'Test Page 1', '/test-page-1');
        $asset = $this->createAsset('assets::test_container::test-image.jpg', 'test-image.jpg', 'https://example.com/test-image.jpg', 'test-image.jpg');

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry1, $entry2]));
        AssetFacade::shouldReceive('find')->with('assets::test_container::test-image.jpg')->andReturn($asset);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertCount(1, $first->pages);
    }

    #[Test]
    public function it_handles_entries_with_invalid_json_data(): void
    {
        $entryData = $this->createEntryData(["\xB1\x31" => 'invalid']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_can_resolve_plain_asset_paths_to_asset_ids(): void
    {
        $entryData = $this->createEntryData(['image' => 'test-folder/test-image.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $container = $this->createAssetContainer('test_container');
        $asset = $this->createAsset('assets::test_container::test-folder/test-image.jpg', 'test-folder/test-image.jpg', 'https://example.com/test-folder/test-image.jpg', 'test-image.jpg');

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$container]));
        AssetFacade::shouldReceive('find')
            ->with('test_container::test-folder/test-image.jpg')
            ->andReturn($asset);
        AssetFacade::shouldReceive('find')
            ->with('assets::test_container::test-folder/test-image.jpg')
            ->andReturn($asset);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::test_container::test-folder/test-image.jpg', $first->assetId);
    }

    #[Test]
    public function it_skips_plain_paths_that_cannot_be_resolved_to_assets(): void
    {
        $entryData = $this->createEntryData(['image' => 'non-existent-folder/non-existent.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $container = $this->createAssetContainer('test_container');

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$container]));
        AssetFacade::shouldReceive('find')
            ->with('test_container::non-existent-folder/non-existent.jpg')
            ->andReturn(null);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage();

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_filters_asset_usage_by_container_handles_when_provided(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::main::test-image.jpg', 'logo' => 'assets::images::logo.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $containerMain = $this->mock(AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });
        $containerImages = $this->mock(AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('images');
        });
        $assetMain = $this->mock(Asset::class, function (MockInterface $mock) use ($containerMain): void {
            $mock->shouldReceive('id')->andReturn('assets::main::test-image.jpg');
            $mock->shouldReceive('path')->andReturn('test-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/test-image.jpg');
            $mock->shouldReceive('basename')->andReturn('test-image.jpg');
            $mock->shouldReceive('container')->andReturn($containerMain);
        });
        $assetImages = $this->mock(Asset::class, function (MockInterface $mock) use ($containerImages): void {
            $mock->shouldReceive('id')->andReturn('assets::images::logo.jpg');
            $mock->shouldReceive('path')->andReturn('logo.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/logo.jpg');
            $mock->shouldReceive('basename')->andReturn('logo.jpg');
            $mock->shouldReceive('container')->andReturn($containerImages);
        });

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::main::test-image.jpg')->andReturn($assetMain);
        AssetFacade::shouldReceive('find')->with('assets::images::logo.jpg')->andReturn($assetImages);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage(['main']);

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::main::test-image.jpg', $first->assetId);
    }

    #[Test]
    public function it_skips_assets_that_cannot_be_found_during_container_filtering(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::main::test-image.jpg', 'logo' => 'assets::images::logo.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $containerMain = $this->mock(AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });
        $assetMain = $this->mock(Asset::class, function (MockInterface $mock) use ($containerMain): void {
            $mock->shouldReceive('id')->andReturn('assets::main::test-image.jpg');
            $mock->shouldReceive('path')->andReturn('test-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/test-image.jpg');
            $mock->shouldReceive('basename')->andReturn('test-image.jpg');
            $mock->shouldReceive('container')->andReturn($containerMain);
        });
        $assetImages = $this->mock(Asset::class, function (MockInterface $mock): void {
            $mock->shouldReceive('id')->andReturn('assets::images::logo.jpg');
            $mock->shouldReceive('path')->andReturn('logo.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/logo.jpg');
            $mock->shouldReceive('basename')->andReturn('logo.jpg');
        });

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::main::test-image.jpg')->andReturn($assetMain, $assetMain);
        AssetFacade::shouldReceive('find')->with('assets::images::logo.jpg')->andReturn($assetImages, null);

        $service = new AssetUsageService;
        $result = $service->findAssetUsage(['main']);

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::main::test-image.jpg', $first->assetId);
    }

    #[Test]
    public function it_can_find_unused_assets(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::main::used-image.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $usedAsset = $this->createAsset('assets::main::used-image.jpg', 'used-image.jpg', 'https://example.com/used-image.jpg', 'used-image.jpg');
        $unusedAsset = $this->createAsset('assets::main::unused-image.jpg');
        $container = $this->createAssetContainer('main', [$usedAsset, $unusedAsset]);

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::main::used-image.jpg')->andReturn($usedAsset);
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$container]));

        $service = new AssetUsageService;
        $result = $service->findUnusedAssets();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::main::unused-image.jpg', $first->id());
    }

    #[Test]
    public function it_returns_empty_collection_when_all_assets_are_used(): void
    {
        $entryData = $this->createEntryData(['image' => 'assets::main::test-image.jpg']);
        $collection = $this->createPageCollection();
        $entry = $this->createEntry($entryData, $collection);
        $usedAsset = $this->createAsset('assets::main::test-image.jpg', 'test-image.jpg', 'https://example.com/test-image.jpg', 'test-image.jpg');
        $container = $this->createAssetContainer('main', [$usedAsset]);

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$entry]));
        AssetFacade::shouldReceive('find')->with('assets::main::test-image.jpg')->andReturn($usedAsset);
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$container]));

        $service = new AssetUsageService;
        $result = $service->findUnusedAssets();

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_filters_by_container_handles_when_provided(): void
    {
        $unusedAssetMain = $this->createAsset('assets::main::unused.jpg');
        $unusedAssetImages = $this->createAsset('assets::images::unused.jpg');
        $containerMain = $this->createAssetContainer('main', [$unusedAssetMain]);
        $containerImages = $this->createAssetContainer('images', [$unusedAssetImages]);

        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([]));
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$containerMain, $containerImages]));

        $service = new AssetUsageService;
        $result = $service->findUnusedAssets(['main']);

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('assets::main::unused.jpg', $first->id());
    }

    #[Test]
    public function it_returns_empty_collection_when_no_containers_exist(): void
    {
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([]));
        AssetContainerFacade::shouldReceive('all')->andReturn(collect([]));

        $service = new AssetUsageService;
        $result = $service->findUnusedAssets();

        $this->assertEmpty($result);
    }
}
