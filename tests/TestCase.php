<?php

namespace Fuelviews\Tailwindcss\Tests;

use Fuelviews\Tailwindcss\TailwindCssServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    protected function getPackageProviders($app): array
    {
        return [
            TailwindCssServiceProvider::class,
        ];
    }
}
