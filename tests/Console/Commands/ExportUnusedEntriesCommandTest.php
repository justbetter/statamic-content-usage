<?php

namespace JustBetter\StatamicContentUsage\Tests\Console\Commands;

use Illuminate\Testing\PendingCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportUnusedEntriesCommand;
use JustBetter\StatamicContentUsage\Exporters\UnusedEntryExporter;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;

class ExportUnusedEntriesCommandTest extends TestCase
{
    #[Test]
    public function it_can_export_unused_entries_to_csv_file(): void
    {
        $collection = $this->mock(\Statamic\Entries\Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('blog');
        });

        $entry = $this->mock(Entry::class, function (MockInterface $mock) use ($collection): void {
            $mock->shouldReceive('id')->andReturn('entry-123');
            $mock->shouldReceive('get')->with('title', '')->andReturn('Unused Entry');
            $mock->shouldReceive('url')->andReturn('/unused-entry');
            $mock->shouldReceive('collection')->andReturn($collection);
        });

        $unusedEntriesCollection = collect([$entry]);

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($unusedEntriesCollection): void {
            $mock->shouldReceive('findUnusedEntries')
                ->once()
                ->with('blog')
                ->andReturn($unusedEntriesCollection);
        });

        $this->mock(UnusedEntryExporter::class, function (MockInterface $mock) use ($unusedEntriesCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedEntriesCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedEntriesCommand::class, [
            'collection' => 'blog',
            '--output' => '/tmp/test.csv',
        ]);
        $command->expectsOutput("Scanning entries for unused entries in collection 'blog'...")
            ->expectsOutput('Found 1 unused entries.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_warning_when_no_unused_entries_are_found(): void
    {
        $emptyUnusedEntriesCollection = collect();

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($emptyUnusedEntriesCollection): void {
            $mock->shouldReceive('findUnusedEntries')
                ->once()
                ->with('blog')
                ->andReturn($emptyUnusedEntriesCollection);
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedEntriesCommand::class, ['collection' => 'blog']);
        $command->expectsOutput("Scanning entries for unused entries in collection 'blog'...")
            ->expectsOutput("No unused entries found for collection 'blog'.")
            ->assertSuccessful();
    }

    #[Test]
    public function it_uses_default_output_path_when_no_output_option_is_provided(): void
    {
        $collection = $this->mock(\Statamic\Entries\Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('blog');
        });

        $entry = $this->mock(Entry::class, function (MockInterface $mock) use ($collection): void {
            $mock->shouldReceive('id')->andReturn('entry-123');
            $mock->shouldReceive('get')->with('title', '')->andReturn('Unused Entry');
            $mock->shouldReceive('url')->andReturn('/unused-entry');
            $mock->shouldReceive('collection')->andReturn($collection);
        });

        $unusedEntriesCollection = collect([$entry]);

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($unusedEntriesCollection): void {
            $mock->shouldReceive('findUnusedEntries')
                ->once()
                ->with('blog')
                ->andReturn($unusedEntriesCollection);
        });

        $this->mock(UnusedEntryExporter::class, function (MockInterface $mock) use ($unusedEntriesCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedEntriesCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedEntriesCommand::class, ['collection' => 'blog']);
        $command->expectsOutput("Scanning entries for unused entries in collection 'blog'...")
            ->expectsOutput('Found 1 unused entries.')
            ->assertSuccessful();
    }
}
