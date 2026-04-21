<?php

namespace JustBetter\StatamicContentUsage\Tests\Services\ContentSources;

use JustBetter\StatamicContentUsage\Services\ContentSources\TermSourceCollector;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Term as TermFacade;
use Statamic\Fields\Value;
use Statamic\Taxonomies\LocalizedTerm;
use Statamic\Taxonomies\Term;
use Statamic\Taxonomies\TermCollection;

class TermSourceCollectorTest extends TestCase
{
    #[Test]
    public function it_collects_term_sources_for_localizations(): void
    {
        $termData = $this->mock(Value::class, function (MockInterface $mock): void {
            $mock->shouldReceive('all')->andReturn(['related_image' => 'assets::main::term.jpg']);
        });

        $localizedTerm = $this->mock(LocalizedTerm::class, function (MockInterface $mock) use ($termData): void {
            $mock->shouldReceive('data')->andReturn($termData);
        });

        $term = $this->mock(Term::class, function (MockInterface $mock) use ($localizedTerm): void {
            $mock->shouldReceive('id')->andReturn('categories::news');
            $mock->shouldReceive('taxonomyHandle')->andReturn('categories');
            $mock->shouldReceive('slug')->andReturn('news');
            $mock->shouldReceive('localizations')->andReturn(collect(['en' => $localizedTerm]));
        });

        TermFacade::shouldReceive('all')->once()->andReturn(new TermCollection([$term]));

        $result = (new TermSourceCollector)->collect();

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertSame('term::categories::news::en', $first->id);
        $this->assertSame('taxonomy:categories', $first->collection);
    }

    #[Test]
    public function it_skips_invalid_term_items(): void
    {
        TermFacade::shouldReceive('all')->once()->andReturn(new TermCollection([new \stdClass]));

        $result = (new TermSourceCollector)->collect();

        $this->assertCount(0, $result);
    }
}
