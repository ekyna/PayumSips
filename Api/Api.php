<?php

namespace Ekyna\Component\Payum\Sips\Api;

use Ekyna\Component\Payum\Sips\Client\ClientInterface;

/**
 * @author Étienne Dauvergne <contact@ekyna.com>
 * @author Grégory Planchat <grégory@kiboko.fr>
 */
class Api
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param array           $config
     * @param ClientInterface $client
     */
    public function __construct(array $config, ClientInterface $client)
    {
        $this->config = array_filter($config, function ($value) {
            return null !== $value;
        });

        $this->client = $client;
    }

    /**
     * Runs the request binary with given data
     * and returns the generated form.
     *
     * @param array $data
     *
     * @return string
     */
    public function request(array $data): string
    {
        $data = array_replace($this->config, $data);

        return $this->client->sendRequest($data);
    }

    /**
     * Runs the response binary and returns the new data.
     *
     * @param string $hash
     *
     * @return array
     */
    public function response(string $hash): array
    {
        return $this->client->sendResponse($hash);
    }
}
