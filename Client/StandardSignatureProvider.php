<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class StandardSignatureProvider implements SignatureProviderInterface
{
    /**
     * @var string
     */
    private $privateKey;

    /**
     * @param string $privateKey
     */
    public function __construct(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    public function signArray(array $data): string
    {
        $dataToSign = $data;
        unset($dataToSign['keyVersion'], $dataToSign['sealAlgorithm']);
        ksort($dataToSign, SORT_STRING);

        return $this->signString(implode('', array_values($dataToSign)));
    }

    public function signString(string $data): string
    {
        return hash_hmac('sha256', $data, $this->privateKey);
    }
}
