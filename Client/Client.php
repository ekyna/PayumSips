<?php

namespace Ekyna\Component\Payum\Sips\Client;

use Ekyna\Component\Payum\Sips\Exception\PaymentRequestException;
use Payum\Core\Exception\InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

/**
 * Class Client
 * @package Ekyna\Component\Payum\Sips\Client
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Client
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var LoggerInterface
     */
    protected $logger;


    /**
     * Constructor
     *
     * @param array           $config
     * @param LoggerInterface $logger
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->config = array_replace(array(
            'merchant_id'      => null,
            'merchant_country' => null,
            'pathfile'         => null,
            'request_bin'      => null,
            'response_bin'     => null,
        ), $config);

        if (empty($this->config['merchant_id'])) {
            throw new InvalidArgumentException('The merchant_id option must be set.');
        }
        if (empty($this->config['merchant_country'])) {
            throw new InvalidArgumentException('The merchant_country option must be set.');
        }
        if (empty($this->config['pathfile'])) {
            throw new InvalidArgumentException('The pathfile option must be set.');
        }
        if (empty($this->config['request_bin'])) {
            throw new InvalidArgumentException('The request_bin option must be set.');
        }
        if (empty($this->config['response_bin'])) {
            throw new InvalidArgumentException('The response_bin option must be set.');
        }

        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * @param  array  $config
     * @return string
     */
    public function callRequest($config)
    {
        $args = array_replace(array(
            'merchant_id'      => $this->config['merchant_id'],
            'merchant_country' => $this->config['merchant_country'],
            'pathfile'         => $this->config['pathfile'],
        ), $config);

        // TODO move into Api ?
        $args = array_intersect_key($args, array_flip($this->getRequestAllowedKeys()));

        $output = $this->run($this->config['request_bin'], $args);

        return $this->handleRequestOutput($output);
    }

    /**
     * @param string $data
     * @return array
     */
    public function callResponse($data)
    {
        $args = array(
            'message' => $data,
            'pathfile' => $this->config['pathfile'],
        );

        $output = $this->run($this->config['response_bin'], $args);

        return $this->handleResponseOutput($output);
    }

    /**
     * @param  string $bin
     * @param  array  $args
     * @return string
     */
    protected function run($bin, $args)
    {
        if (!is_file($bin)) {
            throw new \InvalidArgumentException(sprintf('Binary %s not found', $bin));
        }

        $process = new Process(sprintf('"%s" %s', $bin, escapeshellcmd($this->arrayToArgsString($args))));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->critical($process->getErrorOutput());
            throw new \RuntimeException($process->getOutput().$process->getErrorOutput());
        }

        $this->logger->debug(sprintf('SIPS Request output: %s', $process->getOutput()));

        return $process->getOutput();
    }

    /**
     * @param  array  $args
     * @return string
     */
    protected function arrayToArgsString($args)
    {
        if (is_string($args)) {
            return $args;
        }

        $str = '';
        foreach ($args as $key => $val) {
            if ($key == 'step') {
                continue;
            }
            if (is_numeric($val) or $val) {
                $str .= sprintf('%s=%s ', $key, escapeshellarg($val));
            }
        }

        return trim($str);
    }

    /**
     * @param  string $output
     * @return string
     * @throws PaymentRequestException
     */
    protected function handleRequestOutput($output)
    {
        list($code, $error, $message) = array_merge(explode('!', trim($output, '!')), array_fill(0, 2, ''));

        if ('' === $code && '' === $error) {
            throw new PaymentRequestException(sprintf('SIPS Request failed. Output: %s', $output));
        } elseif ('0' !== $code) {
            throw new PaymentRequestException(sprintf('SIPS Request failed with the following error: %s', $error));
        }

        if (!$message) {
            throw new PaymentRequestException(sprintf('SIPS Output message missing. Output: %s', $output));
        }

        return $message;
    }

    /**
     * @param  string $output the raw response
     * @return array
     */
    protected function handleResponseOutput($output)
    {
        list(
            $result['code'],
            $result['error'],
            $result['merchant_id'],
            $result['merchant_country'],
            $result['amount'],
            $result['transaction_id'],
            $result['payment_means'],
            $result['transmission_date'],
            $result['payment_time'],
            $result['payment_date'],
            $result['response_code'],
            $result['payment_certificate'],
            $result['authorisation_id'],
            $result['currency_code'],
            $result['card_number'],
            $result['cvv_flag'],
            $result['cvv_response_code'],
            $result['bank_response_code'],
            $result['complementary_code'],
            $result['complementary_info'],
            $result['return_context'],
            $result['caddie'],
            $result['receipt_complement'],
            $result['merchant_language'],
            $result['language'],
            $result['customer_id'],
            $result['order_id'],
            $result['customer_email'],
            $result['customer_ip_address'],
            $result['capture_day'],
            $result['capture_mode'],
            $result['dataString'],
            $result['order_validity'],
            $result['transaction_condition'],
            $result['statement_reference'],
            $result['card_validity'],
            $result['score_value'],
            $result['score_color'],
            $result['score_info'],
            $result['score_threshold'],
            $result['score_profile']
        ) = array_merge(explode('!', trim($output, '!')), array_fill(0, 40, ''));

        return $result;
    }

    private function getRequestAllowedKeys()
    {
        return array(
            // Required
            'merchant_id',
            'merchant_country',
            'amount',
            'currency_code',
            'pathfile',

            // Optional
            'transaction_id',
            'normal_return_url',
            'cancel_return_url',
            'automatic_response_url',
            'language',
            'payment_means',
            'header_flag',
            'capture_day',
            'capture_mode',
            'bgcolor',
            'block_align',
            'block_order',
            'textcolor',
            'receipt_complement',
            'caddie',
            'customer_id',
            'customer_email',
            'customer_ip_address',
            'data',
            'return_context',
            'target',
            'order_id',
            'customer_title',
            'customer_name',
            'customer_firstname',
            'customer_birthdate',
            'customer_phone',
            'customer_mobile_phone',
            'customer_nationality_country',
            'customer_birth_zipcode',
            'customer_birth_city',
            'home_city',
            'home_streetnumber',
            'home_street',
            'home_zipcode',

            // Pre-prod only
            'normal_return_logo',
            'cancel_return_logo',
            'submit_logo',
            'logo_id',
            'logo_id2',
            'advert',
            'background_id',
            'templatefile',
        );
    }
}
