<?php
/**
 * Inicializador da aplicação
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */

// Carrega todas as classes e arquivos de config
require_once __DIR__ . '/autoload.php';

use App\Core\Router as Router;

define('defaultLocale', $appConfig->language ?? 'en-en');

// Inicializa um objeto de roteador
$router = Router::getInstance();

// Carrega as rotas definidas para a aplicação
require_once __DIR__ . '/../routes.php';

// Chama o processamento de rotas
try {
    $router->process();
}
// Caso estoure alguma exceção, exibe a mensagem de erro
catch (\Exception $e) {
    echo $e->getMessage();
    die();
}