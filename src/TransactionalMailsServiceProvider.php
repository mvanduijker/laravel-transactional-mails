<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\ServiceProvider;

class TransactionalMailsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(TransactionalMailableQueue::class, function ($app) {
            return new TransactionalMailableQueue($app[Dispatcher::class]);
        });
    }

    public function provides()
    {
        return [TransactionalMailableQueue::class];
    }
}
