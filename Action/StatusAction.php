<?php

namespace Kiboko\Component\Payum\Sips\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

/**
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var GetStatusInterface $request */

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $model['transaction_id']) {
            $request->markNew();

            return;
        }

        if (false != $responseCode = $model['response_code']) {
            // Success
            if ('00' === $responseCode) {
                $request->markCaptured();

                return;
            }
            // Canceled by user
            if ('17' === $responseCode) {
                $request->markCanceled();

                return;
            }
            // Failure
            $request->markFailed();

            return;
        }

        $request->markPending();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
