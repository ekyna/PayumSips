<?php

namespace Kiboko\Component\Payum\Sips\Tests\Client;

use Kiboko\Component\Payum\Sips\Client\StandardSignatureProvider;
use PHPUnit\Framework\TestCase;

class StandardSignatureProviderTest extends TestCase
{
    public function testSign()
    {
        $provider = new StandardSignatureProvider('S9i8qClCnb2CZU3y3Vn0toIOgz3z_aBi79akR30vM9o');

        $this->assertEquals(
            '891721e9fd759f19966cfa46761eecbaefe5022405296f748cdd225275cb1a88',
            $provider->signArray([
                'amount' => 10000,
                'currencyCode' => '978',
                'interfaceVersion' => 'IR_WS_2.20',
                'keyVersion' => '1',
                'merchantId' => '211000021310001',
                'normalReturnUrl' => 'http://example.com/normal_return_url.php',
                'orderChannel' => 'INTERNET',
                'transactionReference' => 'Kiboko',
            ])
        );
    }
}
