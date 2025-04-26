<?php

namespace Ammanade\Docs\Http\Controllers;

use Ammanade\Docs\Attributes\Input;
use Ammanade\Docs\Attributes\Output;
use Ammanade\Docs\UseCases\Product\Create\ProductCreateCommand;
use Ammanade\Docs\UseCases\Product\Create\ProductCreateHandler;
use Ammanade\Docs\UseCases\Product\Create\ProductCreateResult;
use Illuminate\Http\Request;

class ProductsController
{
    #[Input(ProductCreateCommand::class)]
    #[Output(ProductCreateResult::class)]
    public function create(Request $request, ProductCreateHandler $handler)
    {
        $command = new ProductCreateCommand($request->name);

        return $handler($command);
    }
}
