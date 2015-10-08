<?php

namespace Ekyna\Component\Payum\Sips\Action\Api;

use Ekyna\Component\Payum\Sips\Request\AuthorizeForm;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

/**
 * Class RequestFormAction
 * @package Ekyna\Component\Payum\Sips\Action\Api
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AuthorizeFormAction extends BaseApiAction
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
        /** @var $request AuthorizeForm */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        // TODO check data
        /*if (false == $model['TOKEN']) {
            throw new LogicException('The TOKEN must be set by SetExpressCheckout request but it was not executed or failed. Review payment details model for more information');
        }*/

        if (!isset($model['response_code'])) {
            $renderTemplate = new RenderTemplate($this->templateName, array(
                'form' => $this->api->getAuthorizeForm($model),
            ));

            $this->gateway->execute($renderTemplate);

            throw new HttpResponse($renderTemplate->getResult());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof AuthorizeForm &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
