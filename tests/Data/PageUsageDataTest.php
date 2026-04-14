<?php

namespace JustBetter\StatamicContentUsage\Tests\Data;

use JustBetter\StatamicContentUsage\Data\PageUsageData;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PageUsageDataTest extends TestCase
{
    #[Test]
    public function it_can_convert_to_array(): void
    {
        $pageUsageData = new PageUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $result = $pageUsageData->toArray();

        $this->assertEquals('entry-123', $result['entry_id']);
        $this->assertEquals('Test Page', $result['entry_title']);
        $this->assertEquals('/test-page', $result['entry_url']);
        $this->assertEquals('pages', $result['entry_collection']);
    }
}
