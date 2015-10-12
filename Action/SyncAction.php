<?php

namespace Ekyna\Component\Payum\Sips\Action;
use Ekyna\Component\Payum\Sips\Request\CallResponse;
use Payum\Core\Action\GatewayAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\Sync;

/**
 * Class SyncAction
 * @package Ekyna\Component\Payum\Sips\Action
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SyncAction extends GatewayAwareAction
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new CallResponse($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Sync &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
