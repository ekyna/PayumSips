<?php

namespace Ekyna\Component\Payum\Sips\Client;

interface ClientInterface
{
    public function sendRequest(array $config): array;

    public function sendResponse(array $config): array;
}
