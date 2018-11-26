<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Redirection implements RedirectionInterface
{
    /** @var string */
    public $url;

    /** @var string */
    public $version;

    /** @var string */
    public $data;

    /**
     * @param string $url
     * @param string $version
     * @param string $data
     */
    public function __construct(string $url, string $version, string $data)
    {
        $this->url = $url;
        $this->version = $version;
        $this->data = $data;
    }
}
