<?php

namespace JustBetter\StatamicContentUsage\Tests\Exporters;

use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use JustBetter\StatamicContentUsage\Exporters\AssetUsageExporter;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AssetUsageExporterTest extends TestCase
{
    #[Test]
    public function it_can_export_to_csv(): void
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

        $exporter = new AssetUsageExporter;
        $result = $exporter->exportToCsv(collect([$assetUsageData]));

        $this->assertStringContainsString('Asset Path', $result);
        $this->assertStringContainsString('Asset URL', $result);
        $this->assertStringContainsString('test-image.jpg', $result);
        $this->assertStringContainsString('Test Page', $result);
        $this->assertStringContainsString('/test-page', $result);
        $this->assertStringContainsString('pages', $result);
    }

    #[Test]
    public function it_can_export_empty_collection_to_csv(): void
    {
        $exporter = new AssetUsageExporter;
        $result = $exporter->exportToCsv(collect());

        $this->assertStringContainsString('Asset Path', $result);
        $this->assertStringContainsString('Asset URL', $result);
        $this->assertStringContainsString('Collection', $result);
    }
}
