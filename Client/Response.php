<?php

namespace Kiboko\Component\Payum\Sips\Client;

class Response implements ResponseInterface
{
    public static function buildFromIterable(iterable $data)
    {
        $instance = new self;
        foreach ($data as $attribute => $value) {
            $instance->$attribute = $value;
        }

        return $instance;
    }

    public function prepareArray(): array
    {
        return [];
    }
}
