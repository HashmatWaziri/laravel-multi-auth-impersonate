<?php

namespace HashmatWaziri\LaravelMultiAuthImpersonate\Commands;

use Illuminate\Console\Command;

class LaravelMultiAuthImpersonateCommand extends Command
{
    public $signature = 'laravel-multi-auth-impersonate';

    public $description = 'My command';

    public function handle()
    {
        $this->comment('All done');
    }
}
