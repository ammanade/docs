<?php

namespace Ammanade\Docs;

use Ammanade\Docs\Commands\DocsCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DocsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('docs')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(DocsCommand::class);
    }
}
