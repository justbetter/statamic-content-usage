<?php

namespace JustBetter\StatamicContentUsage\Tests\Http\Controllers\CP;

use Illuminate\Http\RedirectResponse;
use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use JustBetter\StatamicContentUsage\Exporters\EntryUsageExporter;
use JustBetter\StatamicContentUsage\Exporters\UnusedEntryExporter;
use JustBetter\StatamicContentUsage\Http\Controllers\CP\EntryUsageController;
use JustBetter\StatamicContentUsage\Http\Requests\ExportEntryUsageRequest;
use JustBetter\StatamicContentUsage\Services\EntryUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Entry;

class EntryUsageControllerTest extends TestCase
{
    #[Test]
    public function it_can_export_entries_to_csv(): void
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
        $csvContent = 'Entry Title,Entry URL,Entry Collection,Page Title,Page URL,Page Collection\nTest Entry,/test-entry,blog,Test Page,/test-page,pages\n';

        /** @var ExportEntryUsageRequest $request */
        $request = $this->mock(ExportEntryUsageRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn(['collection' => 'blog', 'export_type' => 'used']);
        });

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($usageCollection): void {
            $mock->shouldReceive('findEntryUsage')
                ->once()
                ->with('blog')
                ->andReturn($usageCollection);
        });

        $this->mock(EntryUsageExporter::class, function (MockInterface $mock) use ($usageCollection, $csvContent): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($usageCollection)
                ->andReturn($csvContent);
        });

        $controller = app(EntryUsageController::class);
        $response = $controller->exportEntryUsage(
            $request,
            app(EntryUsageService::class),
            app(EntryUsageExporter::class),
            app(UnusedEntryExporter::class)
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertIsString($contentDisposition);
        $this->assertStringContainsString('attachment; filename="entry_used_blog_', $contentDisposition);
        $this->assertEquals($csvContent, $response->getContent());
    }

    #[Test]
    public function it_redirects_back_when_no_entry_usage_is_found(): void
    {
        $emptyUsageCollection = collect();

        /** @var ExportEntryUsageRequest $request */
        $request = $this->mock(ExportEntryUsageRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn(['collection' => 'blog', 'export_type' => 'used']);
        });

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($emptyUsageCollection): void {
            $mock->shouldReceive('findEntryUsage')
                ->once()
                ->with('blog')
                ->andReturn($emptyUsageCollection);
        });

        $controller = app(EntryUsageController::class);
        $response = $controller->exportEntryUsage(
            $request,
            app(EntryUsageService::class),
            app(EntryUsageExporter::class),
            app(UnusedEntryExporter::class)
        );

        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals("No used entries found for collection 'blog'.", session('error'));
    }

    #[Test]
    public function it_can_export_unused_entries_to_csv(): void
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
        $csvContent = 'Entry ID,Entry Title,Entry URL,Collection\nentry-123,Unused Entry,/unused-entry,blog\n';

        /** @var ExportEntryUsageRequest $request */
        $request = $this->mock(ExportEntryUsageRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn(['collection' => 'blog', 'export_type' => 'unused']);
        });

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($unusedEntriesCollection): void {
            $mock->shouldReceive('findUnusedEntries')
                ->once()
                ->with('blog')
                ->andReturn($unusedEntriesCollection);
        });

        $this->mock(UnusedEntryExporter::class, function (MockInterface $mock) use ($unusedEntriesCollection, $csvContent): void {
            $mock->shouldReceive('exportToCsv')
                ->once()
                ->with($unusedEntriesCollection)
                ->andReturn($csvContent);
        });

        $controller = app(EntryUsageController::class);
        $response = $controller->exportEntryUsage(
            $request,
            app(EntryUsageService::class),
            app(EntryUsageExporter::class),
            app(UnusedEntryExporter::class)
        );

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/csv', $response->headers->get('Content-Type'));
        $contentDisposition = $response->headers->get('Content-Disposition');
        $this->assertIsString($contentDisposition);
        $this->assertStringContainsString('attachment; filename="entry_unused_blog_', $contentDisposition);
        $this->assertEquals($csvContent, $response->getContent());
    }

    #[Test]
    public function it_redirects_back_when_no_unused_entries_are_found(): void
    {
        $emptyUnusedEntriesCollection = collect();

        /** @var ExportEntryUsageRequest $request */
        $request = $this->mock(ExportEntryUsageRequest::class, function (MockInterface $mock): void {
            $mock->shouldReceive('validated')
                ->once()
                ->andReturn(['collection' => 'blog', 'export_type' => 'unused']);
        });

        $this->mock(EntryUsageService::class, function (MockInterface $mock) use ($emptyUnusedEntriesCollection): void {
            $mock->shouldReceive('findUnusedEntries')
                ->once()
                ->with('blog')
                ->andReturn($emptyUnusedEntriesCollection);
        });

        $controller = app(EntryUsageController::class);
        $response = $controller->exportEntryUsage(
            $request,
            app(EntryUsageService::class),
            app(EntryUsageExporter::class),
            app(UnusedEntryExporter::class)
        );

        $this->assertTrue($response->isRedirect());
        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals("No unused entries found for collection 'blog'.", session('error'));
    }
}
