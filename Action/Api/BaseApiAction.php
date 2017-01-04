<?php

namespace Ekyna\Component\Payum\Sips\Action\Api;

use Ekyna\Component\Payum\Sips\Api\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;

/**
 * Class BaseApiAction
 * @package Ekyna\Component\Payum\Sips\Action\Api
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class BaseApiAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var Api
     */
    protected $api;


    /**
     * @inheritDoc
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }
}
