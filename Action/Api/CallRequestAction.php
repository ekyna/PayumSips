<?php

namespace Ekyna\Component\Payum\Sips\Action\Api;

use Ekyna\Component\Payum\Sips\Request\CallRequest;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

/**
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class CallRequestAction extends BaseApiAction
{
    /**
     * @var string
     */
    protected $templateName;


    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \Payum\Core\Reply\HttpRedirect       if authorization required.
     */
    public function execute($request)
    {
        /** @var $request CallRequest */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['transaction_id']) {
            return;
        }

        $model['transaction_id'] = date('His'); // TODO store and increment in a file

        $renderTemplate = new RenderTemplate($this->templateName, [
            'form' => $this->api->request($model->getArrayCopy()),
        ]);

        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof CallRequest
            && $request->getModel() instanceof \ArrayAccess;
    }
}
