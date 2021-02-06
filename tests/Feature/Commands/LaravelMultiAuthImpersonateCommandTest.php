<?php


namespace HashmatWaziri\LaravelMultiAuthImpersonate\Tests\Feature\Commands;

use HashmatWaziri\LaravelMultiAuthImpersonate\Tests\TestCase;

class LaravelMultiAuthImpersonateCommandTest extends TestCase
{
    /** @test */
    public function the_multiauth_command_works()
    {
        $this->artisan('laravel-multi-auth-impersonate')->assertExitCode(0);
    }
}
