<?php

namespace JustBetter\StatamicContentUsage\Tests\Services\ContentSources;

use JustBetter\StatamicContentUsage\Services\ContentSources\NavigationSourceCollector;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Nav as NavFacade;
use Statamic\Structures\Nav;
use Statamic\Structures\NavTree;

class NavigationSourceCollectorTest extends TestCase
{
    #[Test]
    public function it_collects_navigation_sources_including_children(): void
    {
        $tree = $this->mock(NavTree::class, function (MockInterface $mock): void {
            $mock->shouldReceive('tree')->andReturn([
                [
                    'id' => 'node-1',
                    'title' => 'Top Item',
                    'url' => '/top',
                    'data' => ['icon' => 'assets::main::icon.svg'],
                    'children' => [
                        [
                            'entry' => 'entry-2',
                            'title' => 'Child Item',
                            'url' => '/child',
                            'data' => [],
                        ],
                    ],
                ],
            ]);
        });

        $nav = $this->mock(Nav::class, function (MockInterface $mock) use ($tree): void {
            $mock->shouldReceive('handle')->andReturn('main');
            $mock->shouldReceive('trees')->andReturn(collect(['en' => $tree]));
        });

        NavFacade::shouldReceive('all')->once()->andReturn(collect([$nav]));

        $result = (new NavigationSourceCollector)->collect();

        $this->assertCount(2, $result);
        $first = $result->first();
        $second = $result->skip(1)->first();
        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertSame('navigation::main::en::node-1', $first->id);
        $this->assertSame('navigation::main::en::entry-2', $second->id);
    }

    #[Test]
    public function it_skips_invalid_navigation_and_tree_items(): void
    {
        $validTree = $this->mock(NavTree::class, function (MockInterface $mock): void {
            $mock->shouldReceive('tree')->andReturn([
                [
                    'id' => ['invalid'],
                    'title' => ['invalid'],
                    'url' => ['invalid'],
                    'children' => [],
                ],
            ]);
        });

        $navWithInvalidTree = $this->mock(Nav::class, function (MockInterface $mock) use ($validTree): void {
            $mock->shouldReceive('handle')->andReturn('footer');
            $mock->shouldReceive('trees')->andReturn(collect(['en' => new \stdClass, 'nl' => $validTree]));
        });

        NavFacade::shouldReceive('all')->once()->andReturn(collect([new \stdClass, $navWithInvalidTree]));

        $result = (new NavigationSourceCollector)->collect();

        $this->assertCount(0, $result);
    }
}
