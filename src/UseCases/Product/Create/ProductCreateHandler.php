<?php

namespace Ammanade\Docs\UseCases\Product\Create;

class ProductCreateHandler
{
    public function __invoke(ProductCreateCommand $command): ProductCreateResult
    {
        return new ProductCreateResult(
            id: 1,
            name: 'asdasd'
        );
    }
}