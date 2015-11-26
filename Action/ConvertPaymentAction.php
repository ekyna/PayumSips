<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Component\Payum\Sips\Action
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction extends PaymentAwareAction
{
    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        //$model['DESCRIPTION'] = $payment->getDescription();
        if (false == $model['amount']) {
            // TODO Check
            $model['currency_code'] = '978';
            // TODO Check
            $model['amount'] = abs($payment->getTotalAmount() / 100);
        }

        if (false == $model['order_id']) {
            $model['order_id'] = $payment->getNumber();
        }

        if (false == $model['customer_id']) {
            $model['customer_id'] = $payment->getClientId();
        }
        if (false == $model['customer_email']) {
            $model['customer_email'] = $payment->getClientEmail();
        }

        $request->setResult((array) $model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Convert &&
            $request->getSource() instanceof PaymentInterface &&
            $request->getTo() == 'array'
        ;
    }
}
