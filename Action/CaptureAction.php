<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Ekyna\Component\Payum\Sips\Request\CallRequest;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\Sync;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;

/**
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface, GenericTokenFactoryAwareInterface
{
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['customer_ip_address']) {
            $this->gateway->execute($httpRequest = new GetHttpRequest());
            $model['customer_ip_address'] = $httpRequest->clientIp;
        }

        if (false == $model['normal_return_url'] && $request->getToken()) {
            $model['normal_return_url'] = $request->getToken()->getTargetUrl();
        }
        if (false == $model['cancel_return_url'] && $request->getToken()) {
            $model['cancel_return_url'] = $request->getToken()->getTargetUrl();
        }
        if (empty($model['automatic_response_url']) && $request->getToken() && $this->tokenFactory) {
            $notifyToken = $this->tokenFactory->createNotifyToken(
                $request->getToken()->getGatewayName(),
                $request->getToken()->getDetails()
            );
            $model['automatic_response_url'] = $notifyToken->getTargetUrl();
        }

        if (false == $model['transaction_id']) {
            $this->gateway->execute(new CallRequest($model));
        }

        $this->gateway->execute(new Sync($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
