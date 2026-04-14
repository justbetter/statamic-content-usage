<?php

namespace JustBetter\StatamicContentUsage\Tests;

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use JustBetter\StatamicContentUsage\ServiceProvider;
use Statamic\Testing\AddonTestCase;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

abstract class TestCase extends AddonTestCase
{
    use LazilyRefreshDatabase;
    use PreventsSavingStacheItemsToDisk;

    protected string $addonServiceProvider = ServiceProvider::class;
}
