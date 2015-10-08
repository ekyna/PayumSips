<?php

namespace Ekyna\Component\Payum\Sips;

use Ekyna\Component\Payum\Sips\Action\Api\AuthorizeFormAction;
use Ekyna\Component\Payum\Sips\Action\CaptureAction;
use Ekyna\Component\Payum\Sips\Action\ConvertPaymentAction;
use Ekyna\Component\Payum\Sips\Action\StatusAction;
use Ekyna\Component\Payum\Sips\Api\Api;
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
            'payum.factory_name' => 'Atos SIPS',
            'payum.factory_title' => 'Atos SIPS',

            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.authorize_form' => new AuthorizeFormAction($config['payum.template.authorize']),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
        ));

        if (false == $config['payum.api']) {

            $apiDefault = isset($config['payum.api_default_config']) ? $config['payum.api_default_config'] : array();

            $config['payum.default_options'] = array(
                'api' => array_replace(array(
                    'merchant_id'      => null,
                    'merchant_country' => 'fr',
                    'pathfile'         => null,
                    'request_bin'      => null,
                    'response_bin'     => null,
                    'debug'            => false,
                ), $apiDefault),
                'default_language' => 'fr',
            );
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = array(
                'api',
                'default_language',
            );

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                // TODO api validation

                $apiConfig = $config['api'];
                $apiConfig['default_language'] = $config['default_language'];

                return new Api($apiConfig);
            };
        }

        return (array) $config;
    }
}
