<?php  defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class Router
 *
 * Sets the routing methods and its related functions
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */
class Router
{

    /**
     * @var array $routes
     *
     * Define o vetor, estático, com as rotas, para ser utilizado pelos métodos de rotear e definir rotas
     */
    private static $routes = array();


    /**
     * Router constructor.
     * @return boolean
     *
     * Gets the controller, method and parameters to be used, start the session and the other components of the system
     */
	public function __construct () {
        // Redireciona para a mesma página usando HTTPS, caso definido como true (e o acesso seja HTTP)
        if(REDIR_HTTPS){
            if(json_decode($_SERVER['HTTP_CF_VISITOR'])->scheme != 'https'){
                header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                exit;
            }
        }


        self::iniSystem();
        self::iniSession(); // Inclui a classe Session e inicia a sessão já configurada

        self::loadModels(); // Inclui todos os models
		self::loadControllers(); // Inclui todos os Controllers


		// Quebra a URL e requisita o controller e o método passados
		//$path = explode('/', $_SERVER['REQUEST_URI']);

		$data = $this->setRouteData();

		$controller = ucfirst($data['controller']);
		$method = $data['method'];
		$params = $data['params'];
		$error = $data['error'];
		
		if (!$error) {
			// Inclui e instancia o controller e o método, passando os parâmetros
			if (file_exists(BASEPATH . '/app/controllers/' . $controller . '.php')
				|| $controller == 'MainController' ) {

				load('/app/system/base/Controller.php');
				
				if ($controller != 'MainController')
					load('/app/controllers/' . $controller . '.php');

				$instance = new $controller;
				if (method_exists($instance, $method)){
					$instance->$method($params);
					return TRUE;
				}
				else
					$error = TRUE;
			}
			else {
				$error = TRUE;
			}

		} 

		// Caso a função ou a tentativa de incluir retorne $error == TRUE, exibe um 404
		if ($error) {
			http_response_code(404);

			view('error/404');			
			return false;
		}
		else {
		    return true;
        }
	}


    /**
     * @return array
     *
     * Define os dados a serem utilizados na rota, como controller, método e parâmetros
     */
    private function setRouteData () {
        require_once BASEPATH . '/app/Routes.php';
        $routed = false;
        $error = false;

        $controller = null;
        $method = null;
        $params = null;

        $urlcomponents = $this->getUrlComponents();
        $lastOfComp = substr($urlcomponents, -1); // Último caractere dos componentes da URL

        // Remover barra do fim da URL
        if($lastOfComp == '/')
            $urlcomponents = substr($urlcomponents, 0, strlen($urlcomponents)-1);


        // Verifica se há rota definida para a URL atual
        if (sizeof(self::$routes) > 0) {
            foreach (self::$routes as $rt => $newpath) {
                // Obtém o primeiro e último caracteres da rota
                $firstOfRoute = substr($rt, 0, 1); // Primeiro caractere da rota
                $lastOfRoute = substr($rt, -1); // Último caractere da rota

                // Remover barras do início e do fim da rota
                if($firstOfRoute == '/')
                    $rt = substr($rt, 1);

                if($lastOfRoute == '/')
                    $rt = substr($rt, 0, strlen($rt)-1);



                $arrPath = explode('/', $newpath);
                $controller = $arrPath[0];
                $method = $arrPath[1];
                $params = array();

                // Se a rota for exata, sem parâmetros
                if(($urlcomponents == '' && ($rt == '' || $rt == '/')) || // Página inicial
                    $urlcomponents == $rt)
                {
                    $routed = true;
                    break; // Encerra a execução do loop
                }
                // Verifica se existe alguma rota com parâmetros que corresponda à URL atual.
                // Sempre executará a primeira rota correspondente encontrada
                else
                {
                    // Divide a rota e o caminho nas barras, transformando-os em vetor
                    $splitRoute = explode('/', $rt);
                    $splitPath = explode('/', $urlcomponents);

                    foreach($splitPath as $i => $pathPart){
                        // Verifica se cada posição da URL é igual à mesma posição da rota
                        // ou se na rota há espaço para uma variável nesta posição ("{[nome]}")
                        if(
                            isset($splitRoute[$i]) && // Caso haja o índice da URL na rota atual
                            ($splitPath[$i] == $splitRoute[$i] || // Caso o trecho atual seja igual na URL e na rota
                            preg_match('/{.+}/', $splitRoute[$i])) // Caso o trecho atual da rota seja variável
                        ){
                            // Se for o último parâmetro e estiver OK, determina os parâmetros
                            // (identificados por "{[nome]}" na rota)
                            if($i === count($splitPath)-1) {
                                $params = array();

                                // Roda a rota toda para armazenar os valores das variáveis
                                foreach($splitRoute as $j => $routePart){
                                    // Caso seja variável na rota,
                                    // adiciona ao vetor usando o nome descrito na própria rota
                                    if(preg_match('/{.+}/', $routePart)){
                                        // Remove as chaves do conteúdo e adiciona a uma variável
                                        $varName = preg_replace('/{|}/', '', $routePart);
                                        $params[$varName] = $splitPath[$j];
                                    }
                                }
                                $routed = true;
                            }
                        }
                        else {
                            break; // Encerra a execução do loop
                        }
                    }

                    if($routed) // Caso tenha encontrado uma rota e definidos controller e método,
                        break;  // Encerra a execução do loop
                }
            }
        }

        // Caso não seja encontrada nenhuma rota para a URL atual
        if(!$routed){
            // Caso seja a página inicial
            if($urlcomponents == '' || $urlcomponents == '/'){
                $controller = 'MainController';
                $method = 'index';
                $params = null;
            }
            // Caso seja alguma outra URL que não a inicial
            else {
                $compParts = explode('/', $urlcomponents);

                // Se houver um parâmetro que possa indicar o Controller, utiliza-o
                if($compParts[0] != ''){
                    $controller = $compParts[0].'Controller';

                    // Se houver um parâmetro que indica o método, utiliza-o
                    if(isset($compParts[1]) && $compParts[1] != ''){
                        $method = $compParts[1];
                    }
                    // Se não houver método, chama o método index
                    else {
                        $method = 'index';
                    }

                    // Caso haja mais trechos na URL, os define como parâmetros para a função
                    if(count($compParts) > 2){
                        foreach($compParts as $i => $part){
                            if($i >= 2){
                                $params[] = $part;
                            }
                        }
                    }
                    // Caso sejam passados apenas controller e possivelmente método, define que não há parâmetros
                    else {
                        $params = null;
                    }
                }
                // Caso não haja um padrão para utilizar com o controller, ou seja, nenhuma rota formada
                else {;
                    // 404
                    // Se não houver nenhuma rota nem conseguir definir o controller e o método pelos parâmetros, limpa
                    $controller = '';
                    $method = '';
                    $params = null;
                    $error = true;
                }
            }
        }

        return array(
            'controller' => $controller,
            'method' => $method,
            'params' => $params,
            'error' => $error
        );
    }


    /**
     * @return string
     *
     * Obtém apenas os parâmetros passados após o endereço root da aplicação
     */
	private function getUrlComponents () {
        if(substr(SYSROOT, -1) == '/')
            $root = substr(SYSROOT, 0,-1);
        else
            $root = SYSROOT;

		return substr($_SERVER['REQUEST_URI'], strlen($root.'/'));
	}


    /**
     * @return void
     *
     * Inicializa os arquivos e configurações básicas do sistema
     */
	private function iniSystem () {
		// Carrega os arquivos básicos do sistema
		require_once BASEPATH . '/app/system/core/App.php';
		require_once BASEPATH . '/app/system/core/Auth.php';
		require_once BASEPATH . '/app/system/core/SystemFunctions.php';
		require_once BASEPATH . '/app/system/core/ViewManager.php';
		require_once BASEPATH . '/app/system/base/Controller.php';
		require_once BASEPATH . '/app/system/base/Model.php';
		require_once BASEPATH . '/app/SystemMessages.php';

		// Carrega o controller principal (pode ser excluído ou renomeado)
		include_once BASEPATH . '/app/controllers/MainController.php';

		// Inclui recursos do sistema
		require_once BASEPATH . '/app/system/components/DB.php';
		require_once BASEPATH . '/app/system/components/Mail.php';
		require_once BASEPATH . '/app/system/components/Validation.php';

		// Mostra ou oculta os erros com base na constante ENVIRONMENT
		if(ENVIRONMENT == 'dev'){
			ini_set('display_errors', 'On');
		} else if(ENVIRONMENT == 'production'){
			ini_set('display_errors', 'Off');
		}

		// Define cabeçalhos HTTP com foco na segurança da aplicação
		header('X-Frame-Options: DENY');
		header('X-Content-Type-Options: nosniff');
	}


    /**
     * @return void
     *
     * Inicia a sessão padrão do sistema
     */
	private function iniSession () {
		ini_set('session.use_only_cookies',1);
		ini_set( 'session.cookie_httponly', SESSION_HTTP_ONLY );

		session_name(SESSION_NAME);
		session_start();
		ob_start();
	}


    /**
     * @return void
     *
     * Carrega todos os models do respectivo diretório
     */
	private function loadModels () {		
		foreach (glob("app/models/*.php") as $filename) {
		    require_once $filename;
		}	
	}


    /**
     * @return void
     *
     * Carrega todos os controllers do respectivo diretório
     */
	private function loadControllers () {
		foreach (glob("app/controllers/*.php") as $filename) {
		    require_once $filename;
		}
	}


    /**
     * @param string $url
     * @param string $action
     * @return void
     *
     * Define uma nova rota, passandoa a URL e a ação (controller e método) a ser tomada
     */
	public static function define ($url, $action) {
	    self::$routes[$url] = $action;
    }
}