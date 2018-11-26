<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Request implements RequestInterface
{
    /** @var number */
    public $amount;

    /** @var string */
    public $currencyCode;

    /** @var string */
    public $orderChannel;

    /** @var string */
    public $interfaceVersion;

    /** @var string */
    public $transactionReference;

    /** @var string */
    public $automaticResponseUrl;

    /** @var string */
    public $normalReturnUrl;

    /** @var string */
    public $seal;

    /**
     * @param number      $amount
     * @param string      $currencyCode
     * @param string      $transactionReference
     * @param string      $normalReturnUrl
     * @param null|string $automaticResponseUrl
     */
    public function __construct(
        $amount,
        string $currencyCode,
        string $transactionReference,
        string $normalReturnUrl,
        ?string $automaticResponseUrl = null
    ) {
        $this->amount = $amount;
        $this->currencyCode = $currencyCode;
        $this->transactionReference = $transactionReference;
        $this->normalReturnUrl = $normalReturnUrl;
        $this->automaticResponseUrl = $automaticResponseUrl;
        $this->orderChannel = 'INTERNET';
        $this->interfaceVersion = 'IR_WS_2.20';
    }

    private static function toInternalCurrencyCode(string $isoCode)
    {
        static $codes = [
            'EUR' => '978',
            'USD' => '840',
            'CHF' => '756',
            'GBP' => '826',
            'CAD' => '124',
            'JPY' => '392',
            'MXP' => '484',
            'TRY' => '949',
            'AUD' => '036',
            'NZD' => '554',
            'NOK' => '578',
            'BRC' => '986',
            'ARP' => '032',
            'KHR' => '116',
            'TWD' => '901',
            'SEK' => '752',
            'DKK' => '208',
            'KRW' => '410',
            'SGD' => '702',
            'XPF' => '953',
            'XOF' => '952',
        ];

        if (!isset($codes[$isoCode])) {
            throw new \RuntimeException(strtr('Unsupported currency %currencyCode%.', [
                '%currencyCode%' => $isoCode,
            ]));
        }

        return $codes[$isoCode];
    }

    public function prepareArray(): array
    {
        return [
            'amount' => ($this->amount * 100),
            'automaticResponseUrl' => $this->automaticResponseUrl,
            'currencyCode' => self::toInternalCurrencyCode($this->currencyCode),
            'interfaceVersion' => $this->interfaceVersion,
            'normalReturnUrl' => $this->normalReturnUrl,
            'orderChannel' => $this->orderChannel,
            'transactionReference' => $this->transactionReference,
        ];
    }
}
