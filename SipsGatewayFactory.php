<?php

namespace Ekyna\Component\Payum\Sips;

use Ekyna\Component\Payum\Sips\Action\Api\CallRequestAction;
use Ekyna\Component\Payum\Sips\Action\Api\CallResponseAction;
use Ekyna\Component\Payum\Sips\Action\CaptureAction;
use Ekyna\Component\Payum\Sips\Action\ConvertPaymentAction;
use Ekyna\Component\Payum\Sips\Action\StatusAction;
use Ekyna\Component\Payum\Sips\Action\SyncAction;
use Ekyna\Component\Payum\Sips\Api\Api;
use Ekyna\Component\Payum\Sips\Client\Client;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory as CoreGatewayFactory;
use Payum\Core\GatewayFactoryInterface;

/**
 * Class SipsGatewayFactory
 * @package Ekyna\Component\Payum\Sips
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SipsGatewayFactory implements GatewayFactoryInterface
{
    /**
     * @var GatewayFactoryInterface
     */
    protected $coreGatewayFactory;

    /**
     * @var array
     */
    private $defaultConfig;


    /**
     * @param array $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     */
    public function __construct(array $defaultConfig = array(), GatewayFactoryInterface $coreGatewayFactory = null)
    {
        $this->coreGatewayFactory = $coreGatewayFactory ?: new CoreGatewayFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->coreGatewayFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->coreGatewayFactory->createConfig((array) $config));

        $config->defaults(array(
            'payum.factory_name'  => 'Atos SIPS',
            'payum.factory_title' => 'Atos SIPS',

            'payum.action.capture'         => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.call_request'    => new CallRequestAction($config['payum.template.authorize']),
            'payum.action.call_response'   => new CallResponseAction(),
            'payum.action.sync'            => new SyncAction(),
            'payum.action.status'          => new StatusAction(),
        ));

        if (false == $config['payum.api']) {
            $config['payum.default_options'] = array(
                'language' => 'fr',
            );

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = array(
                'language',
            );

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $apiConfig = array(
                    'language' => $config['language'],
                );

                $client = $config['payum.client'] instanceof \Closure
                    ? $config['payum.client']($config)
                    : $config['payum.client'];

                return new Api($apiConfig, $client);
            };
        }

        if (false == $config['payum.client']) {
            $config['payum.default_options']['client'] = array(
                'merchant_id' => null,
                'merchant_country' => 'fr',
                'pathfile' => null,
                'request_bin' => null,
                'response_bin' => null,
                'debug' => false,
            );

            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'][] = 'client';

            $config['payum.client'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                new Client($config['client']);
            };
        }

        return (array) $config;
    }
}
