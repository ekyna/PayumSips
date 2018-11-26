<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
interface RequestInterface
{
    public function prepareArray(): array;
}
