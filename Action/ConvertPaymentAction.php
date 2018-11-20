<?php

namespace Ekyna\Component\Payum\Sips\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\RuntimeException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

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
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
            if (2 < $currency->exp) {
                throw new RuntimeException('Unexpected currency exp.');
            }
            $divisor = pow(10, 2 - $currency->exp);

            $model['currency_code'] = $currency->numeric;
            $model['amount'] = abs($payment->getTotalAmount() / $divisor);
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

        $request->setResult((array)$model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
