<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\OrderController;



Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('aothun/{prd_id}', [ProductController::class, 'aothun'])->name('product.aothun');

Route::get('fashions', [ProductController::class, 'productList']);
Route::get('fashion', [ProductController::class, 'fashionID']);

Route::get('product-list', [ProductController::class, 'productList']);

Route::get('product', [ProductController::class, 'productID']);
Route::post('product', [ProductController::class, 'newOrUpdateProduct']);
//Route::post('product/{prd_id}', [ProductController::class, 'updateproduct']);
Route::get('product-id/{id}', [ProductController::class, 'edit']);

Route::get('get_product_type', [ProductController::class, 'getProductType']);

Route::get('getDiscount', [DiscountController::class, 'getDiscount']);

Route::get('get_states/', [StateController::class, 'getState']);
Route::get('get_cities/', [StateController::class, 'getcityState']);
Route::get('get_zips/', [StateController::class, 'getZip']);

Route::post('create_order/', [OrderController::class, 'createOrder']);

Route::post('add_image_library/',[LibraryController::class, 'add_image_library']);
Route::post('libraries/', [LibraryController::class, 'libraries']);

