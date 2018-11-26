<?php

namespace Kiboko\Component\Payum\Sips\Api;

use Kiboko\Component\Payum\Sips\Client\ClientInterface;
use Kiboko\Component\Payum\Sips\Client\RedirectionInterface;
use Kiboko\Component\Payum\Sips\Client\Request;
use Kiboko\Component\Payum\Sips\Client\RequestInterface;
use Kiboko\Component\Payum\Sips\Client\Response;
use Kiboko\Component\Payum\Sips\Client\ResponseInterface;
use Kiboko\Component\Payum\Sips\Exception\PaymentRequestException;
use Kiboko\Component\Payum\Sips\Exception\PaymentResponseException;

/**
 * @author Étienne Dauvergne <contact@ekyna.com>
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Api
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Runs the request binary with given data
     * and returns the generated form.
     *
     * @param RequestInterface $request
     *
     * @return RedirectionInterface
     *
     * @throws \RuntimeException
     */
    public function request(RequestInterface $request): RedirectionInterface
    {
        try {
            return $this->client->requestPayment($request);
        } catch (PaymentRequestException $e) {
            throw new \RuntimeException(null, null, $e);
        }
    }

    /**
     * Runs the response binary and returns the new data.
     *
     * @param string $data
     * @param string $hash
     *
     * @return Response
     *
     * @throws \RuntimeException
     */
    public function response(string $data, string $hash): ResponseInterface
    {
        try {
            return $this->client->handlePaymentResponse($data, $hash);
        } catch (PaymentResponseException $e) {
            throw new \RuntimeException(null, null, $e);
        }
    }
}
