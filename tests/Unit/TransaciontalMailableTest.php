<?php declare(strict_types = 1);

namespace Duijker\LaravelTransactionalMails\Tests\Unit;

use Duijker\LaravelTransactionalMails\Tests\Support\DummyMail;
use Duijker\LaravelTransactionalMails\Tests\TestCase;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Mail\Transport\ArrayTransport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

class TransaciontalMailableTest extends TestCase
{
    /** @var ArrayTransport */
    private $mailDriver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mailDriver = $this->app->make('swift.transport');
    }

    /** @test */
    public function it_sends_mail_after_db_commit()
    {
        DB::beginTransaction();
        Mail::to('user@example.com')->send(new DummyMail());
        $this->assertCount(0, $this->mailDriver->messages());
        DB::commit();

        $this->assertCount(1, $this->mailDriver->messages());
    }

    /** @test */
    public function it_queues_mail_after_db_commit()
    {
        Queue::fake();

        DB::beginTransaction();
        Mail::to('user@example.com')->queue(new DummyMail());
        Queue::assertNothingPushed();
        DB::commit();

        Queue::assertPushed(SendQueuedMailable::class);
    }

    /** @test */
    public function it_sends_mail_later_after_db_commit()
    {
        Queue::fake();

        DB::beginTransaction();
        Mail::to('user@example.com')->later(now()->addMinutes(10), new DummyMail());
        Queue::assertNothingPushed();
        DB::commit();

        Queue::assertPushed(SendQueuedMailable::class);
    }

    /** @test */
    public function it_does_not_sends_mail_after_db_rollback()
    {
        DB::beginTransaction();
        Mail::to('user@example.com')->send(new DummyMail());
        $this->assertCount(0, $this->mailDriver->messages());
        DB::rollBack();

        $this->assertCount(0, $this->mailDriver->messages());
    }

    /** @test */
    public function it_does_not_queue_mail_after_db_rollback()
    {
        Queue::fake();

        DB::beginTransaction();
        Mail::to('user@example.com')->queue(new DummyMail());
        Queue::assertNothingPushed();
        DB::rollBack();

        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_does_not_send_mail_later_after_db_rollback()
    {
        Queue::fake();

        DB::beginTransaction();
        Mail::to('user@example.com')->later(now()->addMinutes(10), new DummyMail());
        Queue::assertNothingPushed();
        DB::rollBack();

        Queue::assertNothingPushed();
    }

    /** @test */
    public function it_sends_mail_when_outer_transaction_is_committed()
    {
        DB::beginTransaction();
        DB::beginTransaction();

        Mail::to('user@example.com')->send(new DummyMail());
        $this->assertCount(0, $this->mailDriver->messages());

        DB::commit();
        $this->assertCount(0, $this->mailDriver->messages());

        DB::commit();

        $this->assertCount(1, $this->mailDriver->messages());
    }

    public function it_directly_sends_mail_when_after_transactions_property_is_false()
    {
        $mail = new DummyMail();
        $mail->afterTransactions = false;

        DB::beginTransaction();
        Mail::to('user@example.com')->send($mail);

        $this->assertCount(1, $this->mailDriver->messages());
    }

    /** @test */
    public function it_directly_sends_mail_when_not_in_transaction()
    {
        Mail::to('user@example.com')->send(new DummyMail());

        $this->assertCount(1, $this->mailDriver->messages());
    }
}
