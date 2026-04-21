<?php

namespace JustBetter\StatamicContentUsage\Tests\Services\ContentSources;

use JustBetter\StatamicContentUsage\Services\ContentSources\GlobalSourceCollector;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\GlobalSet as GlobalSetFacade;
use Statamic\Fields\Value;
use Statamic\Globals\GlobalCollection;
use Statamic\Globals\GlobalSet;
use Statamic\Globals\Variables;

class GlobalSourceCollectorTest extends TestCase
{
    #[Test]
    public function it_collects_global_sources_from_localizations(): void
    {
        $variablesData = $this->mock(Value::class, function (MockInterface $mock): void {
            $mock->shouldReceive('all')->andReturn(['hero_image' => 'assets::main::hero.jpg']);
        });

        $variables = $this->mock(Variables::class, function (MockInterface $mock) use ($variablesData): void {
            $mock->shouldReceive('handle')->andReturn('site_settings');
            $mock->shouldReceive('locale')->andReturn('en');
            $mock->shouldReceive('editUrl')->andReturn('/cp/globals/site_settings');
            $mock->shouldReceive('data')->andReturn($variablesData);
        });

        $globalSet = $this->mock(GlobalSet::class, function (MockInterface $mock) use ($variables): void {
            $mock->shouldReceive('localizations')->andReturn(collect([$variables]));
        });

        GlobalSetFacade::shouldReceive('all')->once()->andReturn(new GlobalCollection([$globalSet]));

        $result = (new GlobalSourceCollector)->collect();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertSame('global::site_settings::en', $first->id);
        $this->assertSame('Global: site_settings (en)', $first->title);
    }

    #[Test]
    public function it_skips_invalid_localization_objects(): void
    {
        $globalSet = $this->mock(GlobalSet::class, function (MockInterface $mock): void {
            $mock->shouldReceive('localizations')->andReturn(collect([new \stdClass]));
        });

        GlobalSetFacade::shouldReceive('all')->once()->andReturn(new GlobalCollection([$globalSet]));

        $result = (new GlobalSourceCollector)->collect();

        $this->assertCount(0, $result);
    }
}
