<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Configuration
{
    /** @var string */
    public $merchantId;

    /** @var string */
    public $gatewayUrl;

    /** @var string */
    public $secretKey;

    /** @var int */
    public $keyVersion;

    /** @var string */
    public $sealAlgorithm;

    /**
     * @param string $merchantId
     * @param string $gatewayUrl
     * @param string $secretKey
     * @param int    $keyVersion
     */
    public function __construct(
        string $merchantId,
        string $gatewayUrl,
        string $secretKey,
        int $keyVersion = 1
    ) {
        $this->merchantId = $merchantId;
        $this->gatewayUrl = $gatewayUrl;
        $this->secretKey = $secretKey;
        $this->keyVersion = $keyVersion;
        $this->sealAlgorithm = 'HMAC-SHA-256';
    }
}
