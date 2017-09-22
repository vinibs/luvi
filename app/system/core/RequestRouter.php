<?php  defined('INITIALIZED') OR exit('You cannot access this file directly');

class RequestRouter {

	public function __construct () {
		self::iniSystem();
		self::iniSession(); // Inclui a classe Session e inicia a sessão já configurada

		self::loadModels(); // Inclui todos os models
		self::loadControllers(); // Inclui todos os Controllers


		// Quebra a URL e requisita o controller e o método passados
		$path = explode('/', $_SERVER['REQUEST_URI']);
		
		$data = $this->setRouteData($path);

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
			return FALSE;
		}
	}


	private function setRouteData ($path) {
		$route = array();
		require_once BASEPATH . '/app/Routes.php';
		$routed = FALSE;
		$error = FALSE;

		$urlcomponents = $this->getUrlComponents();

		// Verifica se há rota definida para a URL atual
		if (sizeof($route) > 0) {
			foreach ($route as $rt => $newpath) {
				// Rota completa, sem / mas com o ?, caso haja
				$pureroute = preg_replace('[\/]', '', $rt);
				// URL completa, sem / mas com parâmetros, caso haja
				$clearurl = preg_replace('[\/]', '', $urlcomponents);

				// Rota limpa, sem / e nem o ?
				$pureroutevars = preg_replace('[\/]', '', substr($rt, 0, -1));
				if($rt[0] == '/')
					$routenovars = substr($rt, 1, -1);
				else
					$routenovars = substr($rt, 0, -1);
				$lastroutechar = substr($rt, -1);

				// Define a rota limpa baseado em ter ou não a opção para parâmetros
				if($lastroutechar == '?')
					$routecompare = $pureroutevars;
				else
					$routecompare = $pureroute;

				// URL limpa, sem / e nem parâmetros após o a rota limpa
				$clearurlvars = substr(
					preg_replace('[\/]', '', $urlcomponents),
					0,
					strlen($routecompare)
				);

				// preg_replace remove as barras "/" da url passada e da rota
				// Compara url/rota e url (descartando variáveis)/rota (descartando o '?')
				if (
					$clearurl == $pureroute ||
					($lastroutechar == '?' &&
					$clearurlvars == $pureroutevars &&
					// Evita que uma url sem variáveis bata com uma rota com variáveis
					$clearurlvars != $clearurl)
				) {
					// Separa o valor do vetor da rota para definir o controller e o método
					$newpath = explode('/', $newpath);

					$controller = $newpath[0];
					if (isset($newpath[1]))
						$method = $newpath[1];
					else
						$method = 'index';

					// Vetor com as variáveis passadas à função
					$varpass = substr($urlcomponents, strlen($routecompare));
					$varpass = explode('/', $varpass);

					// Define que foi encontrada uma rota para a URL atual
					$routed = TRUE;
                    $error = FALSE;
				}
				// Caso sejam passadas variáveis pra uma rota que não peça
				else if ($clearurlvars == $pureroute) {
					$error = TRUE;
				}
			}
		}

		// Caso não haja rota, realiza o procedimento comum de leitura do controller e método
		if ($routed == FALSE) {
			$urlcomponents = $path = explode('/', $urlcomponents);

			// Define o controller a ser usado
			// (caso o primeiro caractere seja '?', chama o MainController e passa o $_GET)
			if (isset($urlcomponents[0]) && !empty($urlcomponents[0]) && $urlcomponents[0][0] != '?')
				$controller = $urlcomponents[0].'Controller';
			 else
				$controller = 'MainController';

			// Define o método a ser chamado
			if(isset($urlcomponents[1]) && !empty($urlcomponents[1]))
				$method = $urlcomponents[1];
			else
				$method = 'index';


			// Vetor com as variáveis passadas à função - filtra controller e método da url
			$varpass = $urlcomponents;
			unset($varpass[0], $varpass[1]);
			$varpass = array_values($varpass);
		}

        if(sizeof($varpass) > 0){
			unset($varpass[0]);
        }

		// Define os parâmetros a serem passados para a função
		if(sizeof($varpass) > 0) {
			// Padrão: 1º (posição 4) é ID, demais são parâmetros comuns

			$id = $varpass[1];
			unset($varpass[1]);

			$params = array_values($varpass);
			$params['id'] = $id;

			// Limpa os valores vazios no vetor de parâmetros
			$params = array_filter($params, function ($value) {
				return $value !== '';
			});

			// Filtra os caracteres do vetor de parâmetros
			$params = filter_var_array($params, FILTER_SANITIZE_STRING);

		} else
			$params = '';

        // Corrige os parâmetros em caso de serem um vetor vazio
        if(is_array($params) && sizeof($params) == 0){
            $params = '';
        }

		return array(
			'controller' => $controller, 
			'method' => $method, 
			'params' => $params, 
			'error' => $error
		);
	}

	// Obtém apenas os parâmetros passados após o endereço root da aplicação
	private function getUrlComponents () {
		return substr($_SERVER['REQUEST_URI'], strlen(SYSROOT.'/'));
	}

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
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');
	}

	private function iniSession () {
		ini_set('session.use_only_cookies',1);
		ini_set( 'session.cookie_httponly', SESSION_HTTP_ONLY );

		session_name(SESSION_NAME);
		session_start();
		ob_start();
	}

	private function loadModels () {		
		foreach (glob("app/models/*.php") as $filename) {
		    require_once $filename;
		}	
	}

	private function loadControllers () {
		foreach (glob("app/controllers/*.php") as $filename) {
		    require_once $filename;
		}
	}
}