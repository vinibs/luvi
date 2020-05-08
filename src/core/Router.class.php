<?php

namespace App\Core;

/**
 * Class Router
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
class Router {
    /**
     * A instância da classe (modelo singleton)
     * @var Router
     */
    private static $instance;

    /**
     * Lista de rotas
     * @var array
     */
    private $routes;

    /**
     * Grupo a ser utilizado para novas rotas
     * @var string
     */
    private $currentGroup;

    /**
     * Inicializa o valor dos atributos
     * 
     * @return void
     */
    private function __construct () { 
        ob_start();
        $this->routes = array();
        $this->currentGroup = '/';
        $this->group($this->currentGroup);
    }

    /**
     * Registra uma rota na lista de rotas da classe
     * 
     * @param string $path The route path
     * @param string $action What should be called for the route
     * @param string $httpMethod What HTTP method should be listened
     * 
     * @return void
     */
    private function registerRoute (
        string $path, 
        string $action, 
        string $httpMethod
    ) : void {
        // Se a rota for vazia (raiz), considera a barra
        if (empty($path)) {
            $path = '/';
        }

        // Verifica se o formato do caminho da rota é válido
        $regexUriFormat = '/^\/?(([A-z0-9\_\-\+]+|\{[A-z0-9\_\-]+\})\/?)*$/';
        if (!preg_match($regexUriFormat, $path)) {
            // Não, então dispara exceção
            $tokenParams = [
                'path' => $path,
                'method' => strtoupper($httpMethod)
            ];
            try {
                $errorMessage = I18n::get(
                    'error.invalid_path_route', 
                    $tokenParams
                );
                throw new \Exception ($errorMessage);
            } 
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Verifica o formato da ação da rota
        $regexActionFormat = '/^[A-z0-9\_]+\:[A-z0-9\_]+$/';
        if (!preg_match($regexActionFormat, $action)) {
            // É inválido, dispara exceção
            $tokenParams = [
                'path' => $path,
                'method' => strtoupper($httpMethod)
            ];
            try {
                $errorMessage = I18n::get(
                    'error.invalid_action_route', 
                    $tokenParams
                );
                throw new \Exception ($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        $httpMethod = strtolower($httpMethod);

        // Se o último caractere da rota for "/", 
        // o remove da string para padronizar
        if (substr($path, -1) === '/' && strlen($path) > 1) {
            $path = substr($path, 0, -1);
        }

        // Define os atributos da rota
        $route = [
            'group' => $this->currentGroup,
            'uri' => $path,
            'action' => $action,
            'params' => array()
        ];

        // Se o índice com o método HTTP informado não exisitr na lista
        if (!key_exists($httpMethod, $this->routes)) {
            $this->routes[$httpMethod] = array();
        }

        // Se o índice com o grupo não existir na lista do método HTTP
        if (!key_exists($this->currentGroup, $this->routes[$httpMethod])) {
            $this->routes[$httpMethod][$this->currentGroup] = array();
        }

        array_push($this->routes[$httpMethod][$this->currentGroup], $route);
    }

    /**
     * Chama a ação registrada para processar a rota
     * 
     * @param array $route The route data
     * 
     * @return void
     * 
     * @throws \Exception If controller could not be found
     * @throws \Exception If method could not be found
     */
    private function callRouteAction (array $route) : void
    {
        // Caso não encontre o índice 'action' na rota ou
        // a ação seja nula ou vazia, dispara uma exceção
        // com mensagem de erro
        if (
            !isset($route['action']) 
            || is_null($route['action']) 
            || empty($route['action'])
        ) {
            $tokenParams = [
                'route' => $route['uri'],
            ];
            try {
                $errorMessage = I18n::get(
                    'error.undefined_action_route', 
                    $tokenParams
                );   
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Separa as partes (controller e método) da ação da rota
        $actionParts = explode(':', $route['action']);
        $controllerName = $actionParts[0];
        $methodName = $actionParts[1];

        // Constroi o nome do controller com o namespace
        $controllerFullName = namespacePrefix . 'Controller\\' 
            . $controllerName;

        // Verifica se a classe declarada para o controller existe
        if (!class_exists($controllerFullName)) {
            // Não existe, lança exceção
            $tokenParams = [
                'controller' => $controllerName,
            ];
            try {
                $errorMessage = I18n::get(
                    'error.controller_not_found', 
                    $tokenParams
                );
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Instancia a classe do controller
        $controller = new $controllerFullName();

        // Verifica se o método declarado para a ação existe na classe
        if (!method_exists($controller, $methodName)) {
            // Não existe, lança exceção
            $tokenParams = [
                'method' => $methodName,
                'controller' => $controllerName
            ];
            try {
                $errorMessage = I18n::get(
                    'error.method_not_found', 
                    $tokenParams
                );
                throw new \Exception($errorMessage);
            }
            catch (\Exception $e) {
                echo $e->getMessage();
            }
        }

        // Funde os parâmetros da rota com os GET comuns
        $requestParams = array_merge($route['params'], $_GET);
        // Adiciona todos os parâmetros à global $_GET também
        $_GET = $requestParams;

        // Executa o método do controller, passando os parâmetros
        $controller->$methodName($requestParams);
        return;
    }

    /**
     * Converte a rota recebida em uma regex
     * 
     * @param array $route The route data
     * 
     * @return string
     */
    private function generateRouteRegex (array $route) : string
    {
        $routeToRegex = $route['uri'];

        // Adiciona uma condicional à barra inicial da rota
        if (substr($routeToRegex, 0, 1) === '/') {
            $routeToRegex = '/?' . substr($routeToRegex, 1);
        }
        // Escapa as barras da rota
        $regexRoute = '/^' 
            . str_replace('/', '\/', $routeToRegex) . '\/?$/';
        // Troca os parâmetros para identifica qualquer valor
        // que não contenha o caractere "/"
        $regexRoute = preg_replace(
            '/\{[a-z]+\}/', 
            '[^\/]+', 
            $regexRoute
        );
        
        return $regexRoute;
    }

    /**
     * Obtém ou cria a instância do singleton
     * 
     * @return Router
     */
    public static function getInstance () : Router
    {
        // Não foi criada uma instância ainda?
        if (is_null(self::$instance)) {
            // Cria uma nova instância
            self::$instance = new self;
        }

        // Retorna a instância da classe
        return self::$instance;
    }

    /**
     * Chama a ação necessária para a rota
     * 
     * @return void
     * 
     * @throws \Exception If a parameter's value is invalid
     */
    public function process () : void
    {
        // Obtém a URL atual
        $requestUri = filter_input(
            \INPUT_SERVER, 
            'REQUEST_URI', 
            \FILTER_SANITIZE_SPECIAL_CHARS
        );

        // Quebra a URL requisitada em '?', caso
        // sejam passados métodos GET da maneira comum
        $uriParts = explode('?', $requestUri);
        // Registra apenas a primeira parte da URL como caminho
        $requestUri = $uriParts[0];
        
        // Se o último caractere da URL for "/", 
        // o remove da string
        if (substr($requestUri, -1) === '/' && strlen($requestUri) > 1) {
            $requestUri = substr($requestUri, 0, -1);
        }

        // Obtém o método HTTP usado na requisição
        $httpMethod = strtolower(filter_input(
            \INPUT_SERVER, 
            'REQUEST_METHOD', 
            \FILTER_SANITIZE_SPECIAL_CHARS
        ));

        // Armazena a rota encontrada
        $foundRoute = null;

        // Existe uma posição para o método HTTP atual no array de rotas?
        if (isset($this->routes[$httpMethod])) {
            
            // Passa pelos grupos de rotas definidos pelo método HTTP atual
            foreach ($this->routes[$httpMethod] as $group => $groupRoutes) {
                // Verifica se a URL atual faz parte do grupo de rotas
                $len = strlen($group);
                if (strncmp($group, $requestUri, $len) !== 0) {
                    // Não, então segue para o próximo grupo
                    continue;
                }

                // Verifica se alguma rota do grupo corresponde à URL atual
                foreach ($groupRoutes as $index => $route) {
                    // Processa o caminho da rota para adicionar o grupo
                    $processedRoute = $group;

                    // Caso não haja barra para separar o grupo e a rota,
                    // adiciona
                    if (
                        substr($route['uri'], 0, 1) !== '/' && 
                        substr($processedRoute, -1) !== '/'
                    ) {
                        $processedRoute .= '/';
                    }
                    // Caso o grupo termine em "/" e a rota também se incie
                    // em "/", remove a barra do fim do grupo
                    else if (
                        substr($processedRoute, -1) === '/' &&
                        substr($route['uri'], 0, 1) === '/'
                    ) {
                        $len = strlen($processedRoute) - 1;
                        $processedRoute = substr($processedRoute, 0, $len);
                    }
                    // Adiciona a rota básica à rota processada
                    $processedRoute .= $route['uri'];
                    
                    // Remove a barra no final da rota processada, se tiver
                    if (
                        substr($processedRoute, -1) === '/' && 
                        strlen($processedRoute) > 1
                    ) {
                        $len = strlen($processedRoute) - 1;
                        $processedRoute = substr($processedRoute, 0, $len);
                    }

                    // Reatribui a URL com o grupo, evitando barras duplas
                    $processedRoute = str_replace('//', '/', $processedRoute);
                    $route['uri'] = $processedRoute;

                    // A rota é exatamente a URL atual?
                    if (strcmp($processedRoute, $requestUri) === 0) {
                        // Sim, define a rota encontrada
                        $foundRoute = $route;
                        break;
                    }
                    else {
                        // Obtém a regex para a rota que está sendo verificada
                        $regexRoute = $this->generateRouteRegex($route);

                        // A URL atual corresponde a 
                        // uma rota com parâmetros?
                        if (preg_match($regexRoute, $requestUri) === 1) {
                            // Sim, então verifica:
                            // Já foi encontrada uma rota para essa URL?
                            if (!is_null($foundRoute)) {
                                // Sim, então extrai as opções de grupo para 
                                // verificar se a rota rota encontrada é 
                                //específica para o grupo dessa URL
                                $groupLen = strlen($foundRoute['group']);
                                // Obtem a substring da rota, com o comprimento 
                                // do grupo
                                $substrGroup = substr(
                                    $foundRoute['uri'], 
                                    0, 
                                    $groupLen
                                );
                                // Obtém o resto da rota, sem os caracteres 
                                // até o comprimento da string do grupo
                                $noGroupUri = substr(
                                    $foundRoute['uri'], 
                                    $groupLen
                                );

                                // Verifica se o grupo extraído é o mesmo 
                                // do definido e se a rota, com grupo, 
                                // é maior que a rota sem ele
                                if (
                                    strcmp(
                                        $foundRoute['group'], 
                                        $substrGroup
                                    ) == 0 &&
                                    strcmp($foundRoute['uri'], $noGroupUri) > 0
                                ) {
                                    // É, então ignora a rota atual porque a 
                                    // anterior é específica para o grupo
                                    break;
                                }
                            }
        
                            // Define a rota encontrada
                            $foundRoute = $route;
                            $routeParams = array();

                            // Quebra a URL e a rota nas barras
                            $routeParts = explode('/', $processedRoute);
                            $urlParts = explode('/', $requestUri);
                            
                            // Verifica as partes da URL para identificar 
                            // os parâmetros
                            foreach ($urlParts as $i => $urlPart) {
                                // Regex para identificar a sintaxe da 
                                // definição do parâmetro
                                $regexParam = '/^\{[A-z0-9\_\-]+\}$/';
                                // Verifica se a parte da rota bate com a 
                                // sintaxe de parâmetro
                                if (
                                    preg_match(
                                        $regexParam, 
                                        $routeParts[$i]
                                    ) === 1
                                    ) {
                                    // Sim, então a parte da rota é um parâmetro
                                    
                                    // Extrai o nome do parâmetro
                                    $paramName = preg_replace(
                                        '/[\{\}]/', 
                                        '', 
                                        $routeParts[$i]
                                    );

                                    // Os caracteres usados no parâmetro são
                                    //  válidos?
                                    $regexEncodedChars = 
                                        '/^[A-z0-9\-\_\.\+\%]+$/';
                                    if (
                                        preg_match(
                                            $regexEncodedChars, 
                                            $urlPart
                                        ) !== 1
                                    ) {
                                        // Não, então gera uma mensagem de erro
                                        $tokenParams = [
                                            'value' => $urlPart
                                        ];
                                        try {
                                            $errorMessage = I18n::get(
                                                'error.invalid_value_url_parameter',
                                                $tokenParams
                                            );
                                        }
                                        catch (\Exception $e) {
                                            echo $e->getMessage();
                                        }

                                        // Tenta carregar a view de erro 
                                        // com a mensagem
                                        try {
                                            View::load(
                                                'error/400', 
                                                400,
                                                ['errorMessage' => $errorMessage]
                                            );
                                            return;
                                        }
                                        // Se não, gera uma exceção
                                        catch (\Exception $e) {
                                            http_response_code(400);
                                            throw new \Exception($errorMessage);
                                        }
                                    }
                                    
                                    // Adiciona o parâmetro e seu valor 
                                    // ao array de parâmetros
                                    $routeParams[$paramName] = $urlPart;
                                }
                            }

                            $foundRoute['params'] = $routeParams;
                            break;
                        }
                    }
                }
            }
        }

        // Encontrou uma rota para a URL atual?
        if (!is_null($foundRoute)) {
            // Sim, executa a chamada da ação da rota
            try {
                $this->callRouteAction($foundRoute);
            }
            // Exibe a mensagem da exceção caso não consiga
            // carregar a ação da rota
            catch (\Exception $e) {
                echo $e->getMessage();
            }
            return;
        }
        
        // Não encontrou nenhuma rota, carrega view de 404
        try {
            View::load('error/404', 404);
        }
        catch (\Exception $e) {
            // Caso a view padrão de erro não exista, 
            // apenas exibe a mensagemda exceção
            echo $e->getMessage();
        }
        return;
    }

    /**
     * Define o grupo a ser utilizado para as próximas rotas definidas
     * 
     * @param string $groupBaseUri The base part of the routes group
     * 
     * @return Router
     */
    public function group ($groupBaseUri) : Router
    {
        $this->currentGroup = $groupBaseUri;
        return $this;
    }

    /**
     * Registra uma rota de método GET
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function get ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'get');
        return $this;
    }

    /**
     * Registra uma rota de método POST
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function post ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'post');
        return $this;
    }

    /**
     * Registra uma rota de método PUT
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function put ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'put');
        return $this;
    }

    /**
     * Registra uma rota de método DELETE
     * 
     * @param string $path The route URI path
     * @param string $action What should be done for the route
     * 
     * @return Router
     */
    public function delete ($path, $action) : Router
    {
        $this->registerRoute($path, $action, 'delete');
        return $this;
    }
}