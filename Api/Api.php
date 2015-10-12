<?php

namespace Ekyna\Component\Payum\Sips\Api;

use Ekyna\Component\Payum\Sips\Client\Client;

/**
 * Class Api
 * @package Ekyna\Component\Payum\Sips\Api
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Api
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param array  $config
     * @param Client $client
     */
    public function __construct(array $config, Client $client)
    {
        $this->config = $config;
        $this->client = $client;
    }

    /**
     * Runs the request binary with given data
     * and returns the generated form.
     *
     * @param array $data
     * @return string
     */
    public function request(array $data)
    {
        $data = array_replace($this->config, $data);

        return $this->client->callRequest($data);
    }

    /**
     * Runs the response binary and returns the new data.
     *
     * @param string $hash
     * @return array
     */
    public function response($hash)
    {
        return $this->client->callResponse($hash);
    }
}
