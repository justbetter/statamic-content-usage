<?php

namespace JustBetter\StatamicContentUsage\Tests\Data;

use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AssetUsageDataTest extends TestCase
{
    #[Test]
    public function it_can_convert_to_array_with_pages(): void
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

        $result = $assetUsageData->toArray();

        $this->assertEquals('assets::main::test-image.jpg', $result['asset_id']);
        $this->assertEquals('test-image.jpg', $result['asset_path']);
        $this->assertEquals('https://example.com/test-image.jpg', $result['asset_url']);
        $this->assertEquals('test-image.jpg', $result['asset_basename']);
        $this->assertIsArray($result['pages']);
        $this->assertCount(1, $result['pages']);
        $this->assertIsArray($result['pages'][0]);
        $this->assertEquals('entry-123', $result['pages'][0]['entry_id']);
    }

    #[Test]
    public function it_can_convert_to_array_with_empty_pages(): void
    {
        $assetUsageData = new AssetUsageData(
            assetId: 'assets::main::test-image.jpg',
            assetPath: 'test-image.jpg',
            assetUrl: 'https://example.com/test-image.jpg',
            assetBasename: 'test-image.jpg',
            pages: collect(),
        );

        $result = $assetUsageData->toArray();

        $this->assertEmpty($result['pages']);
    }
}
