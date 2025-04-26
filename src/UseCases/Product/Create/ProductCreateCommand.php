<?php

namespace Ammanade\Docs\UseCases\Product\Create;

class ProductCreateCommand
{
    public function __construct(
        public string $name
    ) {}
}
