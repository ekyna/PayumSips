<?php

namespace Kiboko\Component\Payum\Sips\Client;

use GuzzleHttp\Psr7\Uri;
use Http\Client\HttpClient;
use Http\Client\Exception;
use Kiboko\Component\Payum\Sips\Exception\PaymentRequestException;
use Kiboko\Component\Payum\Sips\Exception\PaymentResponseException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Client implements ClientInterface
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @param Configuration      $config
     * @param HttpClient         $httpClient
     * @param LoggerInterface    $logger
     */
    public function __construct(
        Configuration $config,
        ?HttpClient $httpClient = null,
        ?LoggerInterface $logger = null
    ) {
        $this->validator = Validation::createValidatorBuilder()
            ->addYamlMapping(__DIR__ . '/../Resources/config/validation.yml')
            ->getValidator()
        ;

        if ($httpClient !== null) {
            $this->httpClient = $httpClient;
        } else {
            $this->httpClient = new \Http\Adapter\Guzzle6\Client(
                new \GuzzleHttp\Client([
                    'timeout' => 10.0,
                ])
            );
        }

        $this->logger = $logger ?: new NullLogger();

        $errors = $this->validator->validate($config);
        if ($errors->count() > 0) {
            throw new \RuntimeException(implode(PHP_EOL, iterator_to_array($errors)));
        }

        $this->config = $config;
    }

    /**
     * @param RequestInterface $request
     *
     * @return RedirectionInterface
     *
     * @throws PaymentRequestException
     */
    public function requestPayment(RequestInterface $request): RedirectionInterface
    {
        $result = $this->validator->validate($request);

        if ($result->count() > 0) {
            throw new PaymentRequestException(strtr(
                'SIPS request is invalid.' . PHP_EOL . '%messages%',
                [
                    '%messages%' => implode(PHP_EOL, iterator_to_array($result)),
                ]
            ));
        }

        try {
            $httpRequest = new \GuzzleHttp\Psr7\Request(
                'POST',
                new Uri($this->config->gatewayUrl . '/rs-services/v2/paymentInit'),
                [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                $this->encodeRequest($request)
            );

            $response = $this->httpClient->sendRequest($httpRequest);
        } catch (Exception $e) {
            throw new PaymentRequestException('The payment request failed.', null, $e);
        } catch (\Exception $e) {
            throw new PaymentRequestException('The payment request failed due to a protocol error.', null, $e);
        }

        if ($response->getStatusCode() !== 200) {
            throw new PaymentRequestException(strtr('The payment request returned a %code% code. %body%', [
                '%code%' => $response->getStatusCode(),
                '%body%' => $response->getStatusCode(),
            ]));
        }

        return $this->handleRequestOutput(
            $response->getBody()->getContents()
        );
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function sign(array $data): array
    {
        return array_merge($data, [
            'seal' => (new StandardSignatureProvider($this->config->secretKey))->signArray($data),
        ]);
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    private function encodeRequest(RequestInterface $request): string
    {
        $data = array_merge(
            $request->prepareArray(),
            [
                'merchantId' => $this->config->merchantId,
                'sealAlgorithm' => $this->config->sealAlgorithm,
                'keyVersion' => $this->config->keyVersion,
            ]
        );
        ksort($data, SORT_STRING);

        return json_encode($this->sign($data));
    }

    /**
     * @param  string $output
     *
     * @return RedirectionInterface
     *
     * @throws PaymentRequestException
     */
    private function handleRequestOutput($output): RedirectionInterface
    {
        $data = json_decode($output, true);

        $result = $this->validator->validate($data);
        if ($result->count() > 0) {
            throw new PaymentRequestException(strtr(
                'SIPS output message is invalid. %messages%' . PHP_EOL . 'Data was: %data%',
                [
                    '%messages%' => implode(PHP_EOL, iterator_to_array($result)),
                    '%data%' => $output,
                ]
            ));
        }

        if ('03' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, the merchant ID is not correct or your contract is not active yet.'
            );
        }

        if ('12' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, the transaction parameters are not valid.'
            );
        }

        if ('30' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(strtr(
                'SIPS Request failed, the transaction format is not valid. %message%' . PHP_EOL . 'Data was: %data%',
                [
                    '%message%' => $data['redirectionStatusMessage'] ?? '',
                    '%data%' => $output,
                ]
            ));
        }

        if ('34' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, security failure, check the seal calculation.'
            );
        }

        if ('94' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, this transaction reference is already registered.'
            );
        }

        if ('99' === $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, service temporarily unavailable.'
            );
        }

        if ('00' !== $data['redirectionStatusCode']) {
            throw new PaymentRequestException(
                'SIPS Request failed, recieved an unhandled response code.'
            );
        }

        return new Redirection(
            $data['redirectionUrl'],
            $data['redirectionVersion'],
            $data['redirectionData']
        );
    }

    /**
     * @param string $response
     * @param string string
     *
     * @return ResponseInterface
     *
     * @throws PaymentResponseException
     */
    public function handlePaymentResponse(string $response, string $hash): ResponseInterface
    {
        $signatureProvider = new StandardSignatureProvider($this->config->secretKey);
        if ($signatureProvider->signString($response) !== $hash) {
            throw new PaymentResponseException(
                'The message could not be authenticated.'
            );
        }

        return Response::buildFromIterable((function(array $fields) {
            foreach ($fields as $field) {
                list($key, $value) = explode('=', $field, 2);
                yield $key => $value;
            }
        })(explode('|', $response)));
    }
}
