<?php

namespace Ekyna\Component\Payum\Sips\Client;

use Ekyna\Component\Payum\Sips\Exception\PaymentRequestException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Class Client
 * @package Ekyna\Component\Payum\Sips\Client
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Client
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * Constructor
     *
     * @param array           $config
     * @param LoggerInterface $logger
     */
    public function __construct(array $config, LoggerInterface $logger = null)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param  string $bin
     * @param  array  $args
     * @return string
     */
    public function run($bin, $args)
    {
        if (!is_file($bin)) {
            throw new \InvalidArgumentException(sprintf('Binary %s not found', $bin));
        }

        $process = new Process(sprintf('"%s" %s', $bin, escapeshellcmd($this->arrayToArgsString($args))));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->logger->critical($process->getErrorOutput());
            throw new \RuntimeException($process->getErrorOutput());
        }

        $this->logger->debug(sprintf('SIPS Request output: %s', $process->getOutput()));

        return $process->getOutput();
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
            if (is_numeric($val) or $val) {
                $str .= sprintf('%s=%s ', $key, escapeshellarg($val));
            }
        }

        return trim($str);
    }

    /**
     * @param  array  $config
     * @return string
     */
    public function request($config)
    {
        $args = array_merge($this->config, $config);

        $output = $this->run($this->binaries['request_bin'], $this->arrayToArgsString($args));

        return $this->handleRequestOutput($output);
    }

    /**
     * @param  string $data the raw response
     * @return array
     */
    public function handleResponseData($data)
    {
        $args = array(
            'message' => $data,
            'pathfile' => $this->config['pathfile'],
        );

        $output = $this->run($this->binaries['response_bin'], $this->arrayToArgsString($args));

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
}
