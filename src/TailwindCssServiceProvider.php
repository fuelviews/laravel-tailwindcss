<?php

namespace Fuelviews\Tailwindcss;

use Fuelviews\Tailwindcss\Commands\TailwindCssCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TailwindCssServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('tailwindcss')
            ->hasCommand(TailwindCssCommand::class);
    }
}
