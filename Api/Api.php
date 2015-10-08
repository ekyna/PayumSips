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
     * @var Client
     */
    private $client;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->client = new Client($config);
    }

    /**
     * Requests the authorize form.
     *
     * @param \ArrayObject $data
     * @return string
     */
    public function getAuthorizeForm(\ArrayObject $data)
    {

    }

    public function doResponse()
    {

    }
}
