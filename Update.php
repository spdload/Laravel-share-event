<?php

namespace App;

use Emilianotisato\NovaTinyMCE\FieldServiceProvider as NovaTinyMCEFieldServiceProvider;
use Laravel\Horizon\HorizonServiceProvider;
use Laravel\Nova\NovaServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;
use Laravelium\Sitemap\SitemapServiceProvider;
use MadWeb\Initializer\Contracts\Runner;

class Update
{
    public function production(Runner $run)
    {
        $run->external('composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader')
            ->external('yarn', 'install', '--production')
            ->external('yarn', 'run', 'production')
            ->publishForce(static::vendorPublishments())
            ->artisan('route:cache')
            ->artisan('config:cache')
            ->artisan('event:cache')
            ->artisan('migrate', ['--seed' => true, '--force' => true])
            ->artisan('cache:clear')
            ->artisan('horizon:terminate');
    }

    public function local(Runner $run)
    {
        $run->external('composer', 'install')
            ->external('composer', 'cghooks', 'update')
            ->external('yarn', 'install')
            ->external('yarn', 'run', 'development')
            ->publish(static::vendorPublishments())
            ->artisan('migrate')
            ->artisan('cache:clear')
            ->artisan('ide-helper:generate')
            ->artisan('ide-helper:meta')
            ->artisan('ide-helper:models', ['--nowrite' => true])
            ->artisan('horizon:terminate');
    }

    public static function vendorPublishments(): array
    {
        return [
            HorizonServiceProvider::class => 'horizon-assets',
            NovaServiceProvider::class => 'nova-assets',
            TelescopeServiceProvider::class => 'telescope-assets',
            SitemapServiceProvider::class => 'public',
            NovaTinyMCEFieldServiceProvider::class => 'public',
        ];
    }
}
