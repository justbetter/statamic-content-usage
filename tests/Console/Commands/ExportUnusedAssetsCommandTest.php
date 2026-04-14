<?php

namespace JustBetter\StatamicContentUsage\Tests\Console\Commands;

use Illuminate\Testing\PendingCommand;
use JustBetter\StatamicContentUsage\Console\Commands\ExportUnusedAssetsCommand;
use JustBetter\StatamicContentUsage\Exporters\UnusedAssetExporter;
use JustBetter\StatamicContentUsage\Services\AssetUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Assets\Asset;

class ExportUnusedAssetsCommandTest extends TestCase
{
    #[Test]
    public function it_can_export_unused_assets_to_csv_file(): void
    {
        $container = $this->mock(\Statamic\Assets\AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });

        $asset = $this->mock(Asset::class, function (MockInterface $mock) use ($container): void {
            $mock->shouldReceive('path')->andReturn('unused-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/unused-image.jpg');
            $mock->shouldReceive('basename')->andReturn('unused-image.jpg');
            $mock->shouldReceive('container')->andReturn($container);
        });

        $unusedAssetsCollection = collect([$asset]);

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->andReturn($unusedAssetsCollection);
        });

        $this->mock(UnusedAssetExporter::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedAssetsCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedAssetsCommand::class, ['--output' => '/tmp/test.csv']);
        $command->expectsOutput('Scanning entries for unused assets in all containers...')
            ->expectsOutput('Found 1 unused assets.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_shows_warning_when_no_unused_assets_are_found(): void
    {
        $emptyUnusedAssetsCollection = collect();

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($emptyUnusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->andReturn($emptyUnusedAssetsCollection);
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedAssetsCommand::class);
        $command->expectsOutput('Scanning entries for unused assets in all containers...')
            ->expectsOutput('No unused assets found.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_uses_default_output_path_when_no_output_option_is_provided(): void
    {
        $container = $this->mock(\Statamic\Assets\AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });

        $asset = $this->mock(Asset::class, function (MockInterface $mock) use ($container): void {
            $mock->shouldReceive('path')->andReturn('unused-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/unused-image.jpg');
            $mock->shouldReceive('basename')->andReturn('unused-image.jpg');
            $mock->shouldReceive('container')->andReturn($container);
        });

        $unusedAssetsCollection = collect([$asset]);

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->andReturn($unusedAssetsCollection);
        });

        $this->mock(UnusedAssetExporter::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedAssetsCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedAssetsCommand::class);
        $command->expectsOutput('Scanning entries for unused assets in all containers...')
            ->expectsOutput('Found 1 unused assets.')
            ->assertSuccessful();
    }

    #[Test]
    public function it_filters_by_container_handles_when_provided(): void
    {
        $container = $this->mock(\Statamic\Assets\AssetContainer::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('main');
        });

        $asset = $this->mock(Asset::class, function (MockInterface $mock) use ($container): void {
            $mock->shouldReceive('path')->andReturn('unused-image.jpg');
            $mock->shouldReceive('url')->andReturn('https://example.com/unused-image.jpg');
            $mock->shouldReceive('basename')->andReturn('unused-image.jpg');
            $mock->shouldReceive('container')->andReturn($container);
        });

        $unusedAssetsCollection = collect([$asset]);

        $this->mock(AssetUsageService::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('findUnusedAssets')
                ->once()
                ->with(['main'])
                ->andReturn($unusedAssetsCollection);
        });

        $this->mock(UnusedAssetExporter::class, function (MockInterface $mock) use ($unusedAssetsCollection): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedAssetsCollection)
                ->andReturn('csv content');
        });

        /** @var PendingCommand $command */
        $command = $this->artisan(ExportUnusedAssetsCommand::class, ['--containers' => 'main']);
        $command->expectsOutput('Scanning entries for unused assets in containers: main')
            ->expectsOutput('Found 1 unused assets.')
            ->assertSuccessful();
    }
}
