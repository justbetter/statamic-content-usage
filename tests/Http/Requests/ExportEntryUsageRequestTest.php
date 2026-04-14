<?php

namespace JustBetter\StatamicContentUsage\Tests\Http\Requests;

use JustBetter\StatamicContentUsage\Http\Requests\ExportEntryUsageRequest;
use JustBetter\StatamicContentUsage\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ExportEntryUsageRequestTest extends TestCase
{
    #[Test]
    public function it_has_required_collection_rule(): void
    {
        $request = new ExportEntryUsageRequest;
        $rules = $request->rules();

        $this->assertArrayHasKey('collection', $rules);
        /** @var string $collectionRule */
        $collectionRule = $rules['collection'];
        $this->assertStringContainsString('required', $collectionRule);
        $this->assertStringContainsString('string', $collectionRule);
    }
}
