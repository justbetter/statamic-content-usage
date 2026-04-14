<?php

namespace JustBetter\StatamicContentUsage\Tests\Widgets;

use JustBetter\StatamicContentUsage\Tests\TestCase;
use JustBetter\StatamicContentUsage\Widgets\EntryUsageWidget;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection as CollectionFacade;

class EntryUsageWidgetTest extends TestCase
{
    #[Test]
    public function it_can_render_html_with_export_urls(): void
    {
        $collection = $this->mock(Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('blog');
            $mock->shouldReceive('title')->andReturn('Blog');
        });

        CollectionFacade::shouldReceive('all')->andReturn(new EntryCollection([$collection]));

        $widget = new EntryUsageWidget;
        $html = $widget->html();

        $this->assertStringContainsString('content-usage/export-entry-usage', $html);
    }

    #[Test]
    public function it_has_a_title(): void
    {
        $title = EntryUsageWidget::title();

        $this->assertNotEmpty($title);
    }
}
