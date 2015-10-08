<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

/**
 * Class StatusAction
 * @package Ekyna\Component\Payum\Sips\Action
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

        $details = ArrayObject::ensureArrayObject($request->getModel());

        if (false == isset($details['bank_response_code'])) {
            $request->markNew();

            return;
        }

        if ('00' === $details['bank_response_code']) {
            $request->markCaptured();

            return;
        }

        if (0 < intval($details['bank_response_code'])) {
            $request->markFailed();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
