<?php

namespace JustBetter\StatamicContentUsage\Tests\Exporters;

use Illuminate\Support\Collection;
use JustBetter\StatamicContentUsage\Exporters\UnusedAssetExporter;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;
use Statamic\Assets\AssetContainer;

class UnusedAssetExporterTest extends TestCase
{
    #[Test]
    public function it_can_export_to_csv(): void
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

        $exporter = new UnusedAssetExporter;
        /** @var Collection<int, Asset> $assetsCollection */
        $assetsCollection = collect([$asset]);
        $result = $exporter->exportToCsv($assetsCollection);

        $this->assertStringContainsString('Asset Path', $result);
        $this->assertStringContainsString('Asset URL', $result);
        $this->assertStringContainsString('Asset Basename', $result);
        $this->assertStringContainsString('Container', $result);
        $this->assertStringContainsString('unused-image.jpg', $result);
        $this->assertStringContainsString('https://example.com/unused-image.jpg', $result);
        $this->assertStringContainsString('main', $result);
    }

    #[Test]
    public function it_can_export_empty_collection_to_csv(): void
    {
        $exporter = new UnusedAssetExporter;
        $result = $exporter->exportToCsv(collect());

        $this->assertStringContainsString('Asset Path', $result);
        $this->assertStringContainsString('Asset URL', $result);
        $this->assertStringContainsString('Asset Basename', $result);
        $this->assertStringContainsString('Container', $result);
    }
}
