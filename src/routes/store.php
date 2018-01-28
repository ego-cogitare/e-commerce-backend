<?php
    $app->get('/store/bootstrap', '\Controllers\Store\BootstrapController::index');
    $app->get('/store/brands', '\Controllers\Store\BrandController::index');
    $app->get('/store/product/{id}', '\Controllers\Store\ProductController::get');
    $app->post('/store/product/{id}/add-review', '\Controllers\Store\ProductController::addReview');
    $app->get('/store/products', '\Controllers\Store\ProductController::index');
    $app->get('/store/blog', '\Controllers\Store\BlogController::index');
    $app->get('/store/page/{id}', '\Controllers\Store\PageController::get');
    $app->get('/store/tags', '\Controllers\Store\TagController::index');
    $app->get('/store/category/{id}', '\Controllers\Store\CategoryController::get');
    $app->get('/store/categories', '\Controllers\Store\CategoryController::index');
    $app->post('/store/checkout', '\Controllers\Store\CheckoutController::index');