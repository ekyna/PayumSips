<?php

namespace Kiboko\Component\Payum\Sips\Client;

/**
 * @author Grégory Planchat <gregory@kiboko.fr>
 */
interface SignatureProviderInterface
{
    public function signArray(array $data): string;
    public function signString(string $data): string;
}
