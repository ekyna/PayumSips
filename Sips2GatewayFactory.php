<?php

namespace Kiboko\Component\Payum\Sips;

use Kiboko\Component\Payum\Sips\Action;
use Kiboko\Component\Payum\Sips\Api\Api;
use Kiboko\Component\Payum\Sips\Client\Client;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Core\GatewayFactoryInterface;

/**
 * @author Étienne Dauvergne <contact@ekyna.com>
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
class Sips2GatewayFactory extends GatewayFactory
{
    /**
     * Builds a new factory.
     *
     * @param array                   $defaultConfig
     * @param GatewayFactoryInterface $coreGatewayFactory
     *
     * @return Sips2GatewayFactory
     */
    public static function build(array $defaultConfig, GatewayFactoryInterface $coreGatewayFactory = null)
    {
        return new static($defaultConfig, $coreGatewayFactory);
    }

    /**
     * @inheritdoc
     */
    protected function populateConfig(ArrayObject $config)
    {
        $template = false != $config['payum.template.capture']
            ? $config['payum.template.capture']
            : '@EkynaPayumSips/Action/capture.html.twig';

        $apiConfig = false != $config['payum.api_config']
            ? (array)$config['payum.api_config']
            : [];

        $config->defaults([
            'payum.factory_name'  => 'atos_sips2',
            'payum.factory_title' => 'Atos SIPS 2.0',

            'payum.action.capture'         => new Action\CaptureAction(),
            'payum.action.convert_payment' => new Action\ConvertPaymentAction(),
            'payum.action.call_request'    => new Action\Api\CallRequestAction($template),
            'payum.action.call_response'   => new Action\Api\CallResponseAction(),
            'payum.action.sync'            => new Action\SyncAction(),
            'payum.action.status'          => new Action\StatusAction(),
        ]);

        $defaultOptions = [];
        $requiredOptions = [];

        if (false == $config['payum.client']) {
            $defaultOptions['client'] = [
                'merchant_id'      => null,
                'merchant_country' => 'fr',
                'secret_key'       => null,
                'request_bin'      => null,
                'response_bin'     => null,
            ];

            $requiredOptions[] = 'client';

            $config['payum.client'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                new Client($config['client']);
            };
        }

        if (false == $config['payum.api']) {
            $defaultOptions['api'] = array_replace([
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
            ], $apiConfig);

            $requiredOptions[] = 'api';

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $client = $config['payum.client'] instanceof \Closure
                    ? $config['payum.client']($config)
                    : $config['payum.client'];

                return new Api($config['api'], $client);
            };
        }

        $config['payum.default_options'] = $defaultOptions;
        $config['payum.required_options'] = $requiredOptions;

        $config->defaults($config['payum.default_options']);
    }
}
