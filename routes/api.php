<?php

use Ammanade\Docs\Http\Controllers\ProductsController;
use Illuminate\Support\Facades\Route;

Route::post('/products', [ProductsController::class, 'create']);