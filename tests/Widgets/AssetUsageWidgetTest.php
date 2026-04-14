<?php

namespace JustBetter\StatamicContentUsage\Tests\Widgets;

use JustBetter\StatamicContentUsage\Tests\TestCase;
use JustBetter\StatamicContentUsage\Widgets\AssetUsageWidget;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\AssetContainer;
use Statamic\Facades\AssetContainer as AssetContainerFacade;

class AssetUsageWidgetTest extends TestCase
{
    #[Test]
    public function it_can_render_html_with_export_url(): void
    {
        $container = $this->mock(AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
            $mock->shouldReceive('title')->andReturn('Main Container');
        });

        AssetContainerFacade::shouldReceive('all')->andReturn(collect([$container]));

        $widget = new AssetUsageWidget;
        $html = $widget->html();

        $this->assertStringContainsString('content-usage/export-assets', $html);
        $this->assertStringContainsString('Main Container', $html);
    }

    #[Test]
    public function it_has_a_title(): void
    {
        $title = AssetUsageWidget::title();

        $this->assertNotEmpty($title);
    }
}
