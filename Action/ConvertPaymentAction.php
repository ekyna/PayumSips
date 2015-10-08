<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\ISO4217\ISO4217;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Component\Payum\Sips\Action
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction implements ActionInterface
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

        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details['order_id'] = $payment->getNumber();
        //$details['DESCRIPTION'] = $payment->getDescription();

        $details['amount'] = $payment->getTotalAmount() * 100;
        $iso = new ISO4217();
        $details['currency_code'] = $iso->findByAlpha3($payment->getCurrencyCode());

        $details['customer_id'] = $payment->getClientId();
        $details['customer_email'] = $payment->getClientEmail();

        $request->setResult((array) $details);
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
