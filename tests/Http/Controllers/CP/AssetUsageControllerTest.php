<?php

namespace JustBetter\StatamicContentUsage\Tests\Http\Controllers\CP;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use JustBetter\StatamicContentUsage\Exporters\AssetUsageExporter;
use JustBetter\StatamicContentUsage\Exporters\UnusedAssetExporter;
use JustBetter\StatamicContentUsage\Http\Controllers\CP\AssetUsageController;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;

class AssetUsageControllerTest extends TestCase
{
    #[Test]
    public function it_can_export_assets_to_csv(): void
    {
        $pageUsageData = new PageUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $assetUsageData = new AssetUsageData(
            assetId: 'assets::main::test-image.jpg',
            assetPath: 'test-image.jpg',
            assetUrl: 'https://example.com/test-image.jpg',
            assetBasename: 'test-image.jpg',
            pages: collect([$pageUsageData]),
        );

        $usageCollection = collect([$assetUsageData]);
        $csvContent = 'Asset Path,Asset URL,Asset Basename,Page Title,Page URL,Collection\ntest-image.jpg,https://example.com/test-image.jpg,test-image.jpg,Test Page,/test-page,pages\n';

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findAssetUsage')
                ->once()
                ->with([])
                ->andReturn($usageCollection);
        });

        $this->mock(AssetUsageExporter::class, function (MockInterface $mock) use ($usageCollection, $csvContent): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn($csvContent);
        });

        $request = Request::create('/cp/content-usage/export-assets', 'GET');
        $controller = app(AssetUsageController::class);
        $response = $controller->exportAssets(
            $request,
            app(AssetUsageService::class),
            app(AssetUsageExporter::class)
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertIsString($contentDisposition);
        $this->assertStringContainsString('attachment; filename="asset_usage_', $contentDisposition);
        $this->assertEquals($csvContent, $response->getContent());
    }

    #[Test]
    public function it_redirects_back_when_no_asset_usage_is_found(): void
    {
        $emptyUsageCollection = collect();

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($emptyUsageCollection): void {
            $mock->shouldReceive('findAssetUsage')
                ->once()
                ->with([])
                ->andReturn($emptyUsageCollection);
        });

        $request = Request::create('/cp/content-usage/export-assets', 'GET');
        $controller = app(AssetUsageController::class);
        $response = $controller->exportAssets(
            $request,
            app(AssetUsageService::class),
            app(AssetUsageExporter::class)
        );

        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('No asset usage found.', session('error'));
    }

    #[Test]
    public function it_can_export_unused_assets_to_csv(): void
    {
        $container = $this->mock(AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });

        $asset = $this->mock(Asset::class, function (MockInterface $mock) use ($container): void {
            $mock->shouldReceive('path')->andReturn('unused-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/unused-image.jpg');
            $mock->shouldReceive('basename')->andReturn('unused-image.jpg');
            $mock->shouldReceive('container')->andReturn($container);
        });

        $unusedAssetsCollection = collect([$asset]);
        $csvContent = 'Asset Path,Asset URL,Asset Basename,Container\nunused-image.jpg,https://example.com/unused-image.jpg,unused-image.jpg,main\n';

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->with([])
                ->andReturn($unusedAssetsCollection);
        });

        $this->mock(UnusedAssetExporter::class, function (MockInterface $mock) use ($unusedAssetsCollection, $csvContent): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedAssetsCollection)
                ->andReturn($csvContent);
        });

        $request = Request::create('/cp/content-usage/export-unused-assets', 'GET');
        $controller = app(AssetUsageController::class);
        $response = $controller->exportUnusedAssets(
            $request,
            app(AssetUsageService::class),
            app(UnusedAssetExporter::class)
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertIsString($contentDisposition);
        $this->assertStringContainsString('attachment; filename="unused_assets_', $contentDisposition);
        $this->assertEquals($csvContent, $response->getContent());
    }

    #[Test]
    public function it_redirects_back_when_no_unused_assets_are_found(): void
    {
        $emptyUnusedAssetsCollection = collect();

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($emptyUnusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->with([])
                ->andReturn($emptyUnusedAssetsCollection);
        });

        $request = Request::create('/cp/content-usage/export-unused-assets', 'GET');
        $controller = app(AssetUsageController::class);
        $response = $controller->exportUnusedAssets(
            $request,
            app(AssetUsageService::class),
            app(UnusedAssetExporter::class)
        );

        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('No unused assets found.', session('error'));
    }
}
