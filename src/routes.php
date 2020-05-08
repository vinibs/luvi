<?php

/**
 * $router já está definido e não deve ser sobrescrito.
 * 
 * Para definir um grupo de rotas, usar, antes das rotas 
 * que serão incluídas:
 * $router->group()
 * 
 * Para definir uma rota, usar:
 * $router->get(), $router->post(), 
 * $router->put() ou $router->delete()
 * 
 * Exemplo 1:
 * $router->get('/', 'MainController:index');
 * 
 * Exemplo 2:
 * $router->group('/produtos')
 *      ->get('/', 'ProductsController:index);
 * (É o equivalente à rota "/produtos")
 * 
 * Exemplo 3:
 * $router->group('/produtos')
 *      ->get('/lista', 'ProductsController:list);
 * (É o equivalente à rota "/produtos/lista")
 */

$router
    // ->group('/')
        ->get('/', 'MainController:index')
        ->get('/{id}', 'MainController:param')
        ->get('/{id}/edit', 'MainController:edit')
        ->get('/{id}/edit/{nome}', 'MainController:edit')

    ->group('/dog')
        ->get('/', 'MainController:dogBark');