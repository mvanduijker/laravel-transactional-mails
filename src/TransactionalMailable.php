<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails;

use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Queue\Factory as Queue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\DB;

class TransactionalMailable extends Mailable
{
    public $afterTransactions = true;

    public function send(MailerContract $mailer)
    {
        if (DB::transactionLevel() && $this->afterTransactions) {
            resolve(TransactionalMailableQueue::class)->add(function () use ($mailer) {
                parent::send($mailer);
            });
        } else {
            parent::send($mailer);
        }
    }

    public function queue(Queue $queue)
    {
        if (DB::transactionLevel() && $this->afterTransactions) {
            resolve(TransactionalMailableQueue::class)->add(function () use ($queue) {
                parent::queue($queue);
            });
        } else {
            parent::queue($queue);
        }
    }

    public function later($delay, Queue $queue)
    {
        if (DB::transactionLevel() && $this->afterTransactions) {
            resolve(TransactionalMailableQueue::class)->add(function () use ($delay, $queue) {
                parent::later($delay, $queue);
            });
        } else {
            parent::later($delay, $queue);
        }
    }
}
