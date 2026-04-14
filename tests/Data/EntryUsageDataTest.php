<?php

namespace JustBetter\StatamicContentUsage\Tests\Data;

use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EntryUsageDataTest extends TestCase
{
    #[Test]
    public function it_can_convert_to_array(): void
    {
        $pageUsageData = new EntryPageUsageData(
            entryId: 'page-entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $entryUsageData = new EntryUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Entry',
            entryUrl: '/test-entry',
            entryCollection: 'blog',
            pages: collect([$pageUsageData]),
        );

        $result = $entryUsageData->toArray();

        $this->assertEquals('entry-123', $result['entry_id']);
        $this->assertEquals('Test Entry', $result['entry_title']);
        $this->assertEquals('/test-entry', $result['entry_url']);
        $this->assertEquals('blog', $result['entry_collection']);
        $this->assertIsArray($result['pages']);
        $this->assertCount(1, $result['pages']);
        /** @var array<string, string> $firstPage */
        $firstPage = $result['pages'][0];
        $this->assertEquals('page-entry-123', $firstPage['entry_id']);
    }

    #[Test]
    public function it_can_convert_to_array_with_empty_pages_collection(): void
    {
        $entryUsageData = new EntryUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Entry',
            entryUrl: '/test-entry',
            entryCollection: 'blog',
            pages: collect(),
        );

        $result = $entryUsageData->toArray();

        $this->assertEquals('entry-123', $result['entry_id']);
        $this->assertIsArray($result['pages']);
        $this->assertCount(0, $result['pages']);
    }
}
