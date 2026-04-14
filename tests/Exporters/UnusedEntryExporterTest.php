<?php

namespace JustBetter\StatamicContentUsage\Tests\Exporters;

use JustBetter\StatamicContentUsage\Exporters\UnusedEntryExporter;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;

class UnusedEntryExporterTest extends TestCase
{
    #[Test]
    public function it_can_export_to_csv(): void
    {
        $collection = $this->mock(Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('blog');
        });

        $entry = $this->mock(Entry::class, function (MockInterface $mock) use ($collection): void {
            $mock->shouldReceive('id')->andReturn('entry-123');
            $mock->shouldReceive('get')->with('title', '')->andReturn('Unused Entry');
            $mock->shouldReceive('url')->andReturn('/unused-entry');
            $mock->shouldReceive('collection')->andReturn($collection);
        });

        $exporter = new UnusedEntryExporter;
        /** @var \Illuminate\Support\Collection<int, Entry> $entriesCollection */
        $entriesCollection = collect([$entry]);
        $result = $exporter->exportToCsv($entriesCollection);

        $this->assertStringContainsString('Entry ID', $result);
        $this->assertStringContainsString('Entry Title', $result);
        $this->assertStringContainsString('Entry URL', $result);
        $this->assertStringContainsString('Collection', $result);
        $this->assertStringContainsString('entry-123', $result);
        $this->assertStringContainsString('Unused Entry', $result);
        $this->assertStringContainsString('/unused-entry', $result);
        $this->assertStringContainsString('blog', $result);
    }

    #[Test]
    public function it_can_export_empty_collection_to_csv(): void
    {
        $exporter = new UnusedEntryExporter;
        $result = $exporter->exportToCsv(collect());

        $this->assertStringContainsString('Entry ID', $result);
        $this->assertStringContainsString('Entry Title', $result);
        $this->assertStringContainsString('Entry URL', $result);
        $this->assertStringContainsString('Collection', $result);
    }
}
