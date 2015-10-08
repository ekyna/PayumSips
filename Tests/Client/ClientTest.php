<?php

namespace Ekyna\Component\Payum\Sips\Tests\Client;

/**
 * Class ClientTest
 * @package Ekyna\Component\Payum\Sips\Tests\Client
 * @author Hubert Moutot <hubert.moutot@gmail.com>
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    private $client;

    public function setUp()
    {
        $logger = $this
            ->getMockBuilder('Psr\Log\LoggerInterface')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $bin = array('request_bin' => '', 'response_bin' => '');
        $config = array('pathfile' => '');

        $this->client = $this->getMock(
            'Ekyna\Component\Payum\Sips\Client\Client',
            array('run'),
            array($logger, $bin, $config)
        );
    }

    public function testRequestFailed()
    {
        $this->client
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue('!-1!wrong card number!!'))
        ;

        $this->setExpectedException(
            'Ekyna\Component\Payum\Sips\Exception\PaymentRequestException',
            'SIPS Request failed with the following error: wrong card number'
        );

        $result = $this->client->request(array('amount' => 10000));
    }

    public function testRequestInvalid()
    {
        $this->client
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue('!!!!'))
        ;

        $this->setExpectedException(
            'Ekyna\Component\Payum\Sips\Exception\PaymentRequestException',
            'SIPS Request failed. Output: !!!!'
        );

        $result = $this->client->request(array('amount' => 10000));
    }

    public function testRequestSuccess()
    {
        $this->client
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue('!0!!<div>form</div>!'))
        ;
        $expected = '<div>form</div>';

        $result = $this->client->request(array('amount' => 10000));

        $this->assertEquals($expected, $result);
    }

    public function testHandleResponseData()
    {
        $this->client
            ->expects($this->once())
            ->method('run')
            ->will($this->returnValue('!1!2!3!4!5!6!7!8!9!10!11!12!13!14!15!16!17!18!19!20!21!22!23!24!25!26!27!28!29!30!31!32!33!34!35!36!37!38!39!40!41!'))
        ;

        $expected = array(
            'code' => '1',
            'error' => '2',
            'merchant_id' => '3',
            'merchant_country' => '4',
            'amount' => '5',
            'transaction_id' => '6',
            'payment_means' => '7',
            'transmission_date' => '8',
            'payment_time' => '9',
            'payment_date' => '10',
            'response_code' => '11',
            'payment_certificate' => '12',
            'authorisation_id' => '13',
            'currency_code' => '14',
            'card_number' => '15',
            'cvv_flag' => '16',
            'cvv_response_code' => '17',
            'bank_response_code' => '18',
            'complementary_code' => '19',
            'complementary_info' => '20',
            'return_context' => '21',
            'caddie' => '22',
            'receipt_complement' => '23',
            'merchant_language' => '24',
            'language' => '25',
            'customer_id' => '26',
            'order_id' => '27',
            'customer_email' => '28',
            'customer_ip_address' => '29',
            'capture_day' => '30',
            'capture_mode' => '31',
            'dataString' => '32',
            'order_validity' => '33',
            'transaction_condition' => '34',
            'statement_reference' => '35',
            'card_validity' => '36',
            'score_value' => '37',
            'score_color' => '38',
            'score_info' => '39',
            'score_threshold' => '40',
            'score_profile' => '41',
        );

        $result = $this->client->handleResponseData('message=xxx');

        $this->assertEquals($expected, $result);
    }
}
