<?php

namespace JustBetter\StatamicContentUsage\Tests\Console\Commands;

use Illuminate\Testing\PendingCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportAssetUsageCommand;
use JustBetter\StatamicContentUsage\Data\AssetUsageData;
use JustBetter\StatamicContentUsage\Data\PageUsageData;
use JustBetter\StatamicContentUsage\Exporters\AssetUsageExporter;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;

class ExportAssetUsageCommandTest extends TestCase
{
    #[Test]
    public function it_can_export_asset_usage_to_csv_file(): void
    {
        $testAssetId = 'assets::main::test-image.jpg';
        $testAssetPath = 'test-image.jpg';
        $testAssetUrl = 'https://example.com/test-image.jpg';
        $testAssetBasename = 'test-image.jpg';
        $testEntryId = 'entry-123';
        $testEntryTitle = 'Test Page';
        $testEntryUrl = '/test-page';
        $testEntryCollection = 'pages';

        $pageUsageData = new PageUsageData(
            entryId: $testEntryId,
            entryTitle: $testEntryTitle,
            entryUrl: $testEntryUrl,
            entryCollection: $testEntryCollection,
        );

        $assetUsageData = new AssetUsageData(
            assetId: $testAssetId,
            assetPath: $testAssetPath,
            assetUrl: $testAssetUrl,
            assetBasename: $testAssetBasename,
            pages: collect([$pageUsageData]),
        );

        $usageCollection = collect([$assetUsageData]);

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findAssetUsage')
                ->once()
                ->andReturn($usageCollection);
        });

        $this->mock(AssetUsageExporter::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportAssetUsageCommand::class, ['--output' => '/tmp/test.csv']);
        $command->expectsOutput('Scanning entries for asset usage...')
            ->expectsOutput('Found 1 unique assets.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_warning_when_no_asset_usage_is_found(): void
    {
        $emptyUsageCollection = collect();

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($emptyUsageCollection): void {
            $mock->shouldReceive('findAssetUsage')
                ->once()
                ->andReturn($emptyUsageCollection);
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportAssetUsageCommand::class);
        $command->expectsOutput('Scanning entries for asset usage...')
            ->expectsOutput('No asset usage found.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_uses_default_output_path_when_no_output_option_is_provided(): void
    {
        $testAssetId = 'assets::main::test-image.jpg';
        $testAssetPath = 'test-image.jpg';
        $testAssetUrl = 'https://example.com/test-image.jpg';
        $testAssetBasename = 'test-image.jpg';
        $testEntryId = 'entry-123';
        $testEntryTitle = 'Test Page';
        $testEntryUrl = '/test-page';
        $testEntryCollection = 'pages';

        $pageUsageData = new PageUsageData(
            entryId: $testEntryId,
            entryTitle: $testEntryTitle,
            entryUrl: $testEntryUrl,
            entryCollection: $testEntryCollection,
        );

        $assetUsageData = new AssetUsageData(
            assetId: $testAssetId,
            assetPath: $testAssetPath,
            assetUrl: $testAssetUrl,
            assetBasename: $testAssetBasename,
            pages: collect([$pageUsageData]),
        );

        $usageCollection = collect([$assetUsageData]);

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findAssetUsage')
                ->once()
                ->andReturn($usageCollection);
        });

        $this->mock(AssetUsageExporter::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportAssetUsageCommand::class);
        $command->expectsOutput('Scanning entries for asset usage...')
            ->expectsOutput('Found 1 unique assets.')
            ->assertSuccessful();
    }
}
