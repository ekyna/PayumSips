<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
interface ResponseInterface
{
    public function prepareArray(): array;
}
