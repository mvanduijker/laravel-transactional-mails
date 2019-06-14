<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails\Tests;

use Duijker\LaravelTransactionalMails\TransactionalMailsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('mail.driver', 'array');
    }

    protected function getPackageProviders($app)
    {
        return [
            TransactionalMailsServiceProvider::class,
        ];
    }
}
