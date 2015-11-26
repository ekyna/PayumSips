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
use Payum\Core\PaymentFactory as CorePaymentFactory;
use Payum\Core\PaymentFactoryInterface;

/**
 * Class SipsPaymentFactory
 * @package Ekyna\Component\Payum\Sips
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SipsPaymentFactory implements PaymentFactoryInterface
{
    /**
     * @var PaymentFactoryInterface
     */
    protected $corePaymentFactory;

    /**
     * @var array
     */
    private $defaultConfig;


    /**
     * @param array $defaultConfig
     * @param PaymentFactoryInterface $corePaymentFactory
     */
    public function __construct(array $defaultConfig = array(), PaymentFactoryInterface $corePaymentFactory = null)
    {
        $this->corePaymentFactory = $corePaymentFactory ?: new CorePaymentFactory();
        $this->defaultConfig = $defaultConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function create(array $config = array())
    {
        return $this->corePaymentFactory->create($this->createConfig($config));
    }

    /**
     * {@inheritDoc}
     */
    public function createConfig(array $config = array())
    {
        $config = ArrayObject::ensureArrayObject($config);
        $config->defaults($this->defaultConfig);
        $config->defaults($this->corePaymentFactory->createConfig((array) $config));

        $template = false != $config['payum.template.capture']
            ? $config['payum.template.capture']
            : '@PayumSips/Action/capture.html.twig';

        $apiConfig = false != $config['payum.api_config']
            ? (array) $config['payum.api_config']
            : array();

        $config->defaults(array(
            'payum.factory_name'  => 'atos_sips',
            'payum.factory_title' => 'Atos SIPS',

            'payum.action.capture'         => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.call_request'    => new CallRequestAction($template),
            'payum.action.call_response'   => new CallResponseAction(),
            'payum.action.sync'            => new SyncAction(),
            'payum.action.status'          => new StatusAction(),
        ));

        $defaultOptions  = array();
        $requiredOptions = array();

        if (false == $config['payum.client']) {
            $defaultOptions['client'] = array(
                'merchant_id'      => null,
                'merchant_country' => 'fr',
                'pathfile'         => null,
                'request_bin'      => null,
                'response_bin'     => null,
            );

            $requiredOptions[] = 'client';

            $config['payum.client'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                new Client($config['client']);
            };
        }

        if (false == $config['payum.api']) {
            $defaultOptions['api'] = array_replace(array(
                'language'           => null,
                'payment_means'      => null,
                'header_flag'        => null,
                'bgcolor'            => null,
                'block_align'        => null,
                'block_order'        => null,
                'textcolor'          => null,
                'normal_return_logo' => null,
                'cancel_return_logo' => null,
                'submit_logo'        => null,
                'logo_id'            => null,
                'logo_id2'           => null,
                'advert'             => null,
                'background_id'      => null,
                'templatefile'       => null,
            ), $apiConfig);

            $requiredOptions[] = 'api';

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $client = $config['payum.client'] instanceof \Closure
                    ? $config['payum.client']($config)
                    : $config['payum.client'];

                return new Api($config['api'], $client);
            };
        }

        $config['payum.default_options']  = $defaultOptions;
        $config['payum.required_options'] = $requiredOptions;
        $config->defaults($config['payum.default_options']);

        return (array) $config;
    }
}
