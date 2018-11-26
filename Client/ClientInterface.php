<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
interface ClientInterface
{
    public function requestPayment(RequestInterface $config): RedirectionInterface;

    public function handlePaymentResponse(string $response, string $hash): ResponseInterface;
}
