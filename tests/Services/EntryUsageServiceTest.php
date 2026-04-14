<?php

namespace JustBetter\StatamicContentUsage\Tests\Services;

use JustBetter\StatamicContentUsage\Services\EntryUsageService;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\Collection;
use Statamic\Entries\Entry;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Entry as EntryFacade;
use Statamic\Fields\Value;

class EntryUsageServiceTest extends TestCase
{
    protected function createSourceCollection(string $handle = 'blog'): MockInterface
    {
        return $this->mock(Collection::class, function (MockInterface $mock) use ($handle): void {
            $mock->shouldReceive('handle')->andReturn($handle);
        });
    }

    protected function createPageCollection(): MockInterface
    {
        return $this->mock(Collection::class, function (MockInterface $mock): void {
            $mock->shouldReceive('handle')->andReturn('pages');
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function createEntryData(array $data): MockInterface
    {
        return $this->mock(Value::class, function (MockInterface $mock) use ($data): void {
            $mock->shouldReceive('all')->andReturn($data);
        });
    }

    protected function createSourceEntry(string $entryId): MockInterface
    {
        return $this->mock(Entry::class, function (MockInterface $mock) use ($entryId): void {
            $mock->shouldReceive('id')->andReturn($entryId);
        });
    }

    protected function createPageEntry(MockInterface $entryData, MockInterface $pageCollection, string $entryId = 'page-entry-123'): MockInterface
    {
        return $this->mock(Entry::class, function (MockInterface $mock) use ($entryData, $pageCollection, $entryId): void {
            $mock->shouldReceive('id')->andReturn($entryId);
            $mock->shouldReceive('get')->with('title', '')->andReturn('Test Page');
            $mock->shouldReceive('url')->andReturn('/test-page');
            $mock->shouldReceive('collection')->andReturn($pageCollection);
            $mock->shouldReceive('data')->andReturn($entryData);
        });
    }

    protected function createReferencedEntry(string $entryId, MockInterface $sourceCollection, string $title = 'Source Entry', string $url = '/source-entry'): MockInterface
    {
        return $this->mock(Entry::class, function (MockInterface $mock) use ($entryId, $sourceCollection, $title, $url): void {
            $mock->shouldReceive('id')->andReturn($entryId);
            $mock->shouldReceive('get')->with('title', '')->andReturn($title);
            $mock->shouldReceive('url')->andReturn($url);
            $mock->shouldReceive('collection')->andReturn($sourceCollection);
        });
    }

    #[Test]
    public function it_can_find_entry_usage(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $sourceEntry = $this->createSourceEntry('source-entry-123');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry' => 'entry::blog::source-entry-123']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);
        $referencedEntry = $this->createReferencedEntry('source-entry-123', $sourceCollection);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$sourceEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));
        EntryFacade::shouldReceive('find')->with('source-entry-123')->andReturn($referencedEntry);

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('source-entry-123', $first->entryId);
        $this->assertCount(1, $first->pages);
        $firstPage = $first->pages->first();
        $this->assertNotNull($firstPage);
        $this->assertEquals('page-entry-123', $firstPage->entryId);
    }

    #[Test]
    public function it_returns_empty_collection_when_collection_does_not_exist(): void
    {
        CollectionFacade::shouldReceive('findByHandle')->with('non-existent')->andReturn(null);

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('non-existent');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_returns_empty_collection_when_collection_has_no_entries(): void
    {
        $sourceCollection = $this->mock(Collection::class);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([]));

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_deduplicates_pages_for_the_same_entry(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $sourceEntry = $this->createSourceEntry('source-entry-123');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry' => 'entry::blog::source-entry-123']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);
        $referencedEntry = $this->createReferencedEntry('source-entry-123', $sourceCollection);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$sourceEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry, $pageEntry]));
        EntryFacade::shouldReceive('find')->with('source-entry-123')->andReturn($referencedEntry);

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertCount(1, $first->pages);
    }

    #[Test]
    public function it_can_find_unused_entries(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $usedEntry = $this->createSourceEntry('used-entry-123');
        $unusedEntry = $this->createSourceEntry('unused-entry-123');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry' => 'entry::blog::used-entry-123']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);
        $referencedEntry = $this->createReferencedEntry('used-entry-123', $sourceCollection, 'Used Entry', '/used-entry');

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$usedEntry, $unusedEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));
        EntryFacade::shouldReceive('find')->with('used-entry-123')->andReturn($referencedEntry);

        $service = new EntryUsageService;
        $result = $service->findUnusedEntries('blog');

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals('unused-entry-123', $first->id());
    }

    #[Test]
    public function it_returns_empty_collection_when_all_entries_are_used(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $usedEntry = $this->createSourceEntry('used-entry-123');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry' => 'entry::blog::used-entry-123']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);
        $referencedEntry = $this->createReferencedEntry('used-entry-123', $sourceCollection, 'Used Entry', '/used-entry');

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$usedEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));
        EntryFacade::shouldReceive('find')->with('used-entry-123')->andReturn($referencedEntry);

        $service = new EntryUsageService;
        $result = $service->findUnusedEntries('blog');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_returns_empty_collection_when_collection_does_not_exist_for_unused(): void
    {
        CollectionFacade::shouldReceive('findByHandle')->with('non-existent')->andReturn(null);

        $service = new EntryUsageService;
        $result = $service->findUnusedEntries('non-existent');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_skips_referenced_entries_that_cannot_be_found(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $sourceEntry = $this->createSourceEntry('non-existent-entry');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry' => 'entry::blog::non-existent-entry']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$sourceEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));
        EntryFacade::shouldReceive('find')->with('non-existent-entry')->andReturn(null);

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_handles_entries_with_invalid_json_data(): void
    {
        $sourceCollection = $this->createSourceCollection();
        $sourceEntry = $this->createSourceEntry('source-entry-123');
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(["\xB1\x31" => 'invalid']);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$sourceEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertEmpty($result);
    }

    #[Test]
    public function it_can_find_entry_references_by_uuid(): void
    {
        $uuid = 'a1b2c3d4-e5f6-7890-abcd-ef1234567890';
        $sourceCollection = $this->createSourceCollection();
        $sourceEntry = $this->createSourceEntry($uuid);
        $pageCollection = $this->createPageCollection();
        $pageEntryData = $this->createEntryData(['entry_id' => $uuid]);
        $pageEntry = $this->createPageEntry($pageEntryData, $pageCollection);
        $referencedEntry = $this->createReferencedEntry($uuid, $sourceCollection);

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([$sourceEntry]));
        EntryFacade::shouldReceive('all')->andReturn(new EntryCollection([$pageEntry]));
        EntryFacade::shouldReceive('find')->with($uuid)->andReturn($referencedEntry);

        $service = new EntryUsageService;
        $result = $service->findEntryUsage('blog');

        $this->assertCount(1, $result);
        $first = $result->first();
        $this->assertNotNull($first);
        $this->assertEquals($uuid, $first->entryId);
    }

    #[Test]
    public function it_returns_empty_collection_when_collection_has_no_entries_for_unused(): void
    {
        $sourceCollection = $this->createSourceCollection();

        CollectionFacade::shouldReceive('findByHandle')->with('blog')->andReturn($sourceCollection);
        EntryFacade::shouldReceive('whereCollection')->with('blog')->andReturn(new EntryCollection([]));

        $service = new EntryUsageService;
        $result = $service->findUnusedEntries('blog');

        $this->assertEmpty($result);
    }
}
