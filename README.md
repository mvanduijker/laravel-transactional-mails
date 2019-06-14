# Laravel Transactional Mails

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mvanduijker/laravel-transactional-mails.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-transactional-mails)
[![Build Status](https://img.shields.io/travis/mvanduijker/laravel-transactional-mails/master.svg?style=flat-square)](https://travis-ci.org/mvanduijker/laravel-transactional-mails)
[![Total Downloads](https://img.shields.io/packagist/dt/mvanduijker/laravel-transactional-mails.svg?style=flat-square)](https://packagist.org/packages/mvanduijker/laravel-transactional-mails)


Send your mails after database transaction is committed. 

Will fix issue when you want to send an e-mail within a transaction and the transaction fails, the mail would have been send. 
Also when queueing a mail within a transaction and the queue picks up the mail before the transaction has been committed, the mail might
have incomplete data.


## Installation

You can install the package via composer:

```bash
composer require mvanduijker/laravel-transactional-mails
```

## Usage

You only have to extend your mailable with `Duijker\LaravelTransactionalMails\TransactionalMailable` instead of `Illuminate\Mail\Mailable`.


```php
<?php

namespace App\Mail;

use App\Order;
use Duijker\LaravelTransactionalMails\TransactionalMailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends TransactionalMailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var Order
     */
    protected $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.orders.shipped')
                    ->with([
                        'orderName' => $this->order->name,
                        'orderPrice' => $this->order->price,
                    ]);
    }
}
```

```php
<?php

namespace App\Http\Controllers;

use App\Order;
use App\Mail\OrderShipped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    /**
     * Ship the given order.
     *
     * @param  Request  $request
     * @param  int  $orderId
     * @return Response
     */
    public function ship(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        
        DB::transaction(function () use ($order, $request) {
            $order->ship();
            Mail::to($request->user())->send(new OrderShipped($order));
            
            throw new \RuntimeException('Mail won\'t be sent');
        });
        
    }
}
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.


## Credits

- [Mark van Duijker](https://github.com/mvanduijker)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
