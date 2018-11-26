<?php

namespace Kiboko\Component\Payum\Sips\Tests\Client;

use Kiboko\Component\Payum\Sips\Client\Request;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    public function testPrepareArray()
    {
        $request = new Request(
            100,
            'EUR',
            'Kiboko',
            'http://example.com/normal_return_url.php',
            'http://example.com/normal_return_url.php'
        );

        $this->assertEquals(
            [
                'amount' => 10000,
                'currencyCode' => '978',
                'interfaceVersion' => 'IR_WS_2.20',
                'normalReturnUrl' => 'http://example.com/normal_return_url.php',
                'automaticResponseUrl' => 'http://example.com/normal_return_url.php',
                'orderChannel' => 'INTERNET',
                'transactionReference' => 'Kiboko',
            ],
            $request->prepareArray()
        );
    }
}
