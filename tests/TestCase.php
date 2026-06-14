<?php

namespace Tests;

use Laravel\Passport\ClientRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if ($this->app->bound('db') && $this->app['db']->getSchemaBuilder()->hasTable('oauth_clients')) {
            app(ClientRepository::class)->createPersonalAccessGrantClient('Test Personal Access Client', 'users');
        }
    }
}
