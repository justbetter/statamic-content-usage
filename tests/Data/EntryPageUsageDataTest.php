<?php

namespace JustBetter\StatamicContentUsage\Tests\Data;

use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EntryPageUsageDataTest extends TestCase
{
    #[Test]
    public function it_can_convert_to_array(): void
    {
        $entryPageUsageData = new EntryPageUsageData(
            entryId: 'page-entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $result = $entryPageUsageData->toArray();

        $this->assertEquals('page-entry-123', $result['entry_id']);
        $this->assertEquals('Test Page', $result['entry_title']);
        $this->assertEquals('/test-page', $result['entry_url']);
        $this->assertEquals('pages', $result['entry_collection']);
    }
}
