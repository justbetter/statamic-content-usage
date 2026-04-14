<?php

namespace JustBetter\StatamicContentUsage\Tests\Services\ContentSources;

use JustBetter\StatamicContentUsage\Services\ContentSources\EntrySourceCollector;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Fields\Value;

class EntrySourceCollectorTest extends TestCase
{
    #[Test]
    public function it_collects_entry_sources(): void
    {
        $entryData = $this->mock(Value::class, function (MockInterface $mock): void {
            $mock->shouldReceive('all')->andReturn(['banner' => 'assets::main::banner.jpg']);
        });

        $collection = $this->mock(Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('pages');
        });

        $entry = $this->mock(Entry::class, function (MockInterface $mock) use ($entryData, $collection): void {
            $mock->shouldReceive('id')->andReturn('entry-1');
            $mock->shouldReceive('get')->with('title', '')->andReturn('Homepage');
            $mock->shouldReceive('url')->andReturn('/home');
            $mock->shouldReceive('collection')->andReturn($collection);
            $mock->shouldReceive('data')->andReturn($entryData);
        });

        EntryFacade::shouldReceive('all')->once()->andReturn(new EntryCollection([$entry]));

        $result = (new EntrySourceCollector)->collect();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertSame('entry-1', $first->id);
        $this->assertSame('Homepage', $first->title);
        $this->assertSame('pages', $first->collection);
    }
}
