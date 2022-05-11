<?php

namespace App;

use MadWeb\Initializer\Contracts\Runner;
use MadWeb\Initializer\Jobs\Supervisor\MakeQueueSupervisorConfig;

class Install
{
    public function local(Runner $run)
    {
        return $run
            ->external('composer', 'install')
            ->external('composer', 'cghooks', 'add')
            ->artisan('key:generate')
            ->artisan('storage:link')
            ->external('yarn', 'install')
            ->external('yarn', 'run', 'development')
            ->publish(Update::vendorPublishments())
            ->artisan('migrate', ['--seed' => true])
            ->artisan('cache:clear')
            ->artisan('ide-helper:generate')
            ->artisan('ide-helper:meta')
            ->artisan('ide-helper:models', ['--nowrite' => true])
            ->artisan('horizon:terminate');
    }

    public function production(Runner $run)
    {
        return $run
            ->external('composer', 'install', '--no-dev', '--prefer-dist', '--optimize-autoloader')
            ->artisan('key:generate', ['--force' => true])
            ->artisan('storage:link')
            ->external('yarn', 'install', '--production')
            ->external('yarn', 'run', 'production')
            ->publishForce(Update::vendorPublishments())
            ->artisan('route:cache')
            ->artisan('config:cache')
            ->artisan('event:cache')
            ->artisan('migrate', ['--seed' => true, '--force' => true])
            ->artisan('cache:clear')
            ->artisan('horizon:terminate');
    }

    public function localRoot(Runner $run)
    {
        return $run
            ->dispatch(new MakeQueueSupervisorConfig([
                'command' => 'php artisan horizon',
            ]))
            ->external('supervisorctl', 'reread')
            ->external('supervisorctl', 'update');
    }

    public function productionRoot(Runner $run)
    {
        return $this->localRoot($run);
    }
}
