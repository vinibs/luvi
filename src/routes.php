<?php

/**
 * $router is already defined and shouldn't be overriden.
 * 
 * To define a route group, use, before the routes that
 * will be included:
 * $router->group()
 * 
 * To define a route, use:
 * $router->get(), $router->post(), 
 * $router->put() or $router->delete()
 * 
 * Example 1:
 * $router->get('/', 'MainController:index');
 * 
 * Example 2:
 * $router->group('/products')
 *      ->get('/', 'ProductsController:index);
 * (It's the same as "/products" route)
 * 
 * Example 3:
 * $router->group('/products')
 *      ->get('/list', 'ProductsController:list);
 * (It's the same as "/products/list" route)
 */

$router
    // ->group('/')
        ->get('/', 'MainController:index')
        ->get('/{id}', 'MainController:param')
        ->get('/{id}/edit', 'MainController:edit')
        ->get('/{id}/edit/{nome}', 'MainController:edit')

    ->group('/dog')
        ->get('/', 'MainController:dogBark');