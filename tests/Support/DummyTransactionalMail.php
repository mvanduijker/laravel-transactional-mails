<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails\Tests\Support;

use Duijker\LaravelTransactionalMails\TransactionalMailable;

class DummyTransactionalMail extends TransactionalMailable
{
    public function build()
    {
        return $this->html('dummy');
    }
}
