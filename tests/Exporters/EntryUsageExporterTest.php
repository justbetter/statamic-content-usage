<?php

namespace JustBetter\StatamicContentUsage\Tests\Exporters;

use JustBetter\StatamicContentUsage\Data\EntryPageUsageData;
use JustBetter\StatamicContentUsage\Data\EntryUsageData;
use JustBetter\StatamicContentUsage\Exporters\EntryUsageExporter;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class EntryUsageExporterTest extends TestCase
{
    #[Test]
    public function it_can_export_to_csv(): void
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

        $exporter = new EntryUsageExporter;
        $result = $exporter->exportToCsv(collect([$entryUsageData]));

        $this->assertStringContainsString('Entry Title', $result);
        $this->assertStringContainsString('Entry URL', $result);
        $this->assertStringContainsString('Entry Collection', $result);
        $this->assertStringContainsString('Page Title', $result);
        $this->assertStringContainsString('Page URL', $result);
        $this->assertStringContainsString('Page Collection', $result);
        $this->assertStringContainsString('Test Entry', $result);
        $this->assertStringContainsString('/test-entry', $result);
        $this->assertStringContainsString('blog', $result);
        $this->assertStringContainsString('Test Page', $result);
        $this->assertStringContainsString('/test-page', $result);
        $this->assertStringContainsString('pages', $result);
    }

    #[Test]
    public function it_can_export_empty_collection_to_csv(): void
    {
        $exporter = new EntryUsageExporter;
        $result = $exporter->exportToCsv(collect());

        $this->assertStringContainsString('Entry Title', $result);
        $this->assertStringContainsString('Entry URL', $result);
        $this->assertStringContainsString('Entry Collection', $result);
        $this->assertStringContainsString('Page Title', $result);
        $this->assertStringContainsString('Page URL', $result);
        $this->assertStringContainsString('Page Collection', $result);
    }
}
