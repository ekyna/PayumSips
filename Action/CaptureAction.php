<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Ekyna\Component\Payum\Sips\Request\AuthorizeForm;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Capture;
//use Payum\Core\Request\Sync;

/**
 * Class CaptureAction
 * @package Ekyna\Component\Payum\Sips\Action
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class CaptureAction extends GatewayAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // TODO fill details
        /*if (false == $model['location']) {
            $createOrderRequest = new CreateOrder($model);
            $this->gateway->execute($createOrderRequest);

            $model->replace($createOrderRequest->getOrder()->marshal());
            $model['location'] = $createOrderRequest->getOrder()->getLocation();
        }*/

        //$this->gateway->execute(new Sync($model));

        if (true) { // TODO condition
            $this->gateway->execute(new AuthorizeForm($model));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
