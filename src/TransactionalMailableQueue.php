<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\DB;

class TransactionalMailableQueue
{
    /** @var Dispatcher */
    private $eventDispatcher;

    /** @var array */
    private $queue = [];

    public function __construct(Dispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->setUpTransactionListeners();
    }

    public function add(callable $closure)
    {
        $this->queue[] = $closure;
    }

    private function commitTransaction()
    {
        if (DB::transactionLevel() > 0) {
            return;
        }

        foreach ($this->queue as $callable) {
            $callable();
        }

        $this->queue = [];
    }

    private function rollbackTransaction()
    {
        if (DB::transactionLevel() > 0) {
            return;
        }

        $this->queue = [];
    }

    private function setUpTransactionListeners()
    {
        $this->eventDispatcher->listen(TransactionCommitted::class, function () {
            $this->commitTransaction();
        });

        $this->eventDispatcher->listen(TransactionRolledBack::class, function () {
            $this->rollbackTransaction();
        });
    }
}
