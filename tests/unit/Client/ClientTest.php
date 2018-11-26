<?php

namespace Kiboko\Component\Payum\Sips\Tests\Client;

use Http\Client\HttpClient;
use Kiboko\Component\Payum\Sips\Client\Request;
use Kiboko\Component\Payum\Sips\Client\Client;
use Kiboko\Component\Payum\Sips\Client\Configuration;
use Kiboko\Component\Payum\Sips\Exception\PaymentRequestException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @return HttpClient|MockObject
     */
    private function mockClient(): HttpClient
    {
        return $this->getMockBuilder(HttpClient::class)
            ->setMethods(['sendRequest'])
            ->getMock();
    }

    public function testSendRequestSuccess()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '00',
                    'redirectionUrl' => 'http://example.com/',
                    'redirectionVersion' => 'IR_WS_2.0',
                    'redirectionData' => 'LOREM=',
                ])
            ))
        ;

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $redirection = $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));

        $this->assertAttributeEquals('http://example.com/', 'url', $redirection);
        $this->assertAttributeEquals('IR_WS_2.0', 'version', $redirection);
        $this->assertAttributeEquals('LOREM=', 'data', $redirection);
    }

    public function testSendRequestInvalidMerchantId()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '03',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, the merchant ID is not correct or your contract is not active yet.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestInvalidParameters()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '12',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, the transaction parameters are not valid.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestInvalidTransactionFormat()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '30',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestSecurityFailure()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '34',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, security failure, check the seal calculation.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestTransactionAlreadyExists()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '94',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, this transaction reference is already registered.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestServiceUnavailable()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '99',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, service temporarily unavailable.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }

    public function testSendRequestUnknownReturnCode()
    {
        $http = $this->mockClient();

        $http->method('sendRequest')
            ->willReturn(new \GuzzleHttp\Psr7\Response(
                200,
                [
                    'Content-Type' => 'application/json'
                ],
                json_encode([
                    'redirectionStatusCode' => '42',
                    'redirectionVersion' => 'IR_WS_2.0',
                ])
            ))
        ;

        $this->expectException(PaymentRequestException::class);
        $this->expectExceptionMessage('SIPS Request failed, recieved an unhandled response code.');

        $client = new Client(
            new Configuration(
                '211000021310001',
                'http://example.com',
                'lorem'
            ),
            $http
        );

        $client->requestPayment(new Request(
            100.0,
            'EUR',
            sprintf('Kiboko%9d', random_int(0, 999999999)),
            'http://example.com',
            'http://example.com'
        ));
    }
}
