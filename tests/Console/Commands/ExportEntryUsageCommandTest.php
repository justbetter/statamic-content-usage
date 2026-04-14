<?php

namespace JustBetter\StatamicContentUsage\Tests\Console\Commands;

use Illuminate\Testing\PendingCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportEntryUsageCommand;
use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use JustBetter\StatamicContentUsage\Exporters\EntryUsageExporter;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ExportEntryUsageCommandTest extends TestCase
{
    #[Test]
    public function it_can_export_entry_usage_to_csv_file(): void
    {
        $pageUsageData = new EntryPageUsageData(
            entryId: 'page-entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $entryUsageData = new EntryUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Entry',
            entryUrl: '/test-entry',
            entryCollection: 'blog',
            pages: collect([$pageUsageData]),
        );

        $usageCollection = collect([$entryUsageData]);

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findEntryUsage')
                ->once()
                ->with('blog')
                ->andReturn($usageCollection);
        });

        $this->mock(EntryUsageExporter::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportEntryUsageCommand::class, [
            '--collection' => 'blog',
            '--output' => '/tmp/test.csv',
        ]);
        $command->expectsOutput("Scanning entries for usage of 'blog' collection entries...")
            ->expectsOutput("Found 1 unique entries from 'blog' collection.")
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_error_when_collection_is_missing(): void
    {
        /** @var PendingCommand $command */
        $command = $this->artisan(ExportEntryUsageCommand::class);
        $command->expectsOutput('Collection handle is required. Use --collection=handle')
            ->assertFailed();
    }

    #[Test]
    public function it_shows_warning_when_no_entry_usage_is_found(): void
    {
        $emptyUsageCollection = collect();

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($emptyUsageCollection): void {
            $mock->shouldReceive('findEntryUsage')
                ->once()
                ->with('blog')
                ->andReturn($emptyUsageCollection);
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportEntryUsageCommand::class, ['--collection' => 'blog']);
        $command->expectsOutput("Scanning entries for usage of 'blog' collection entries...")
            ->expectsOutput("No entry usage found for collection 'blog'.")
            ->assertSuccessful();
    }

    #[Test]
    public function it_uses_default_output_path_when_no_output_option_is_provided(): void
    {
        $pageUsageData = new EntryPageUsageData(
            entryId: 'page-entry-123',
            entryTitle: 'Test Page',
            entryUrl: '/test-page',
            entryCollection: 'pages',
        );

        $entryUsageData = new EntryUsageData(
            entryId: 'entry-123',
            entryTitle: 'Test Entry',
            entryUrl: '/test-entry',
            entryCollection: 'blog',
            pages: collect([$pageUsageData]),
        );

        $usageCollection = collect([$entryUsageData]);

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findEntryUsage')
                ->once()
                ->with('blog')
                ->andReturn($usageCollection);
        });

        $this->mock(EntryUsageExporter::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportEntryUsageCommand::class, ['--collection' => 'blog']);
        $command->expectsOutput("Scanning entries for usage of 'blog' collection entries...")
            ->expectsOutput("Found 1 unique entries from 'blog' collection.")
            ->assertSuccessful();
    }
}
