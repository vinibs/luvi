<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class App
 *
 * Set the essential methods to be used by the app
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */
class App
{

    /**
     * @param string $url
     * @param int $status
     * @return App
     *
     * Realiza o redirecionamento para uma URL passada por parâmetro
     */
    public static function redirect ($url = '', $status = 200) {
        http_response_code($status);
        if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://')
            header('Location: '.$url);
        else {
            if ($url[0] == '/') {
                $url = substr($url, 1);
            }

            if(SYSROOT[strlen(SYSROOT)-1] == '/')
                $finalUrl = SYSROOT.$url;
            else
                $finalUrl = SYSROOT.'/'.$url;
            header('Location: '.$finalUrl);
        }
        return (new App);
    }


    /**
     * @return App
     *
     * Redireciona para a página anterior, se possível
     */
	public static function back () {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return (new App);
	}


    /**
     * @param string $name
     * @param string|array|null $data
     * @return string|bool
     *
     * Define uma variável de sessão ou obtém seu valor, caso não seja passada a variável $data
     */
	public function session ($name, $data = null) {
		if(!is_null($data)){
			$_SESSION[$name] = $data;
		    return true;
        } else {
		    if(isset($_SESSION[$name]))
			    return $_SESSION[$name];
            else
                return false;
        }
	}


    /**
     * @param string $name
     * @param string|array|null $data
     * @return string|bool
     *
     * Definie um cookie ou obtém seu valor, caso não seja passada a variável $data
     */
	public function cookie ($name, $data = null) {
		if(!is_null($data)){
			setcookie($name, $data);
		    return true;
        } else  {
		    if(isset($_COOKIE[$name]))
			    return $_COOKIE[$name];
            else
                return false;
        }
	}


    /**
     * @param string $name
     * @return bool
     *
     * Define como nulo um dado cookie
     */
	public function unsetcookie ($name) {
		unset($_COOKIE[$name]);
		return setcookie($name, null, -1);
	}


    /**
     * @param string $name
     * @param string|array|null $data
     * @return bool|string
     *
     * Define uma variável de sessão para uso como flash, sendo apagada após o primeiro uso, ou obtém seu valor
     */
	public function flash ($name, $data = null) {
		if(!is_null($data)){
			$_SESSION['flash'][$name] = $data;
		    return true;
        } else {
			if (isset($_SESSION['flash'][$name])) {
				$flash =  $_SESSION['flash'][$name];
				unset($_SESSION['flash'][$name]);
				return $flash;
			} else
				return false;
		}
	}


    /**
     * @param string $name
     * @return bool
     *
     * Verifica se existe uma variável flash com o dado nome
     */
	public function hasFlash ($name) {
		return isset($_SESSION['flash'][$name]);
	}


    /**
     * @return App
     *
     * Obtém o valor vindo do POST para posteriormente preencher um campo de formulário
     */
	public function withValues () {
		flash('inputValues', filterPost());
		return $this;
	}


    /**
     * @param array $inputErrors
     * @return App
     *
     * Define uma flash contendo os erros de entrada dos campos de formulário
     */
	public function withErrors ($inputErrors) {
		foreach ($inputErrors as $input => $error) {
            if (!isset($_SESSION['flash']['inputErrors'][$input]))
                $_SESSION['flash']['inputErrors'][$input] = '';

            $_SESSION['flash']['inputErrors'][$input] .= $error . '<br>';
        }
		return $this;
	}


    /**
     * @param string $fieldName
     * @return bool|string
     *
     * Tenta obter o valor antigo de um dado campo de formulário da variável flash
     */
	public function oldVal ($fieldName) {
		if (isset($_SESSION['flash']['inputValues'][$fieldName])) {
			$oldValue =  $_SESSION['flash']['inputValues'][$fieldName];
			unset($_SESSION['flash']['inputValues'][$fieldName]);
			return $oldValue;
		} else
			return false;
	}


    /**
     * @param string $fieldName
     * @return bool|string
     *
     * Tenta obter os erros de entrada definidos para um dado campo de formulário
     */
	public function getInputErrors ($fieldName) {
		if (isset($_SESSION['flash']['inputErrors'][$fieldName])) {
		    $flashField = $_SESSION['flash']['inputErrors'][$fieldName];
		    unset($_SESSION['flash']['inputErrors'][$fieldName]);
			return $flashField;
		} else
			return false;
	}


    /**
     * @param string $fieldName
     * @return bool
     *
     * Verifica se existe algum erro de entrada para um dado campo de formulário
     */
    public function checkInputErrors ($fieldName = null) {
        if($fieldName != null) {
            if (isset($_SESSION['flash']['inputErrors'][$fieldName]) && $_SESSION['flash']['inputErrors'][$fieldName] != NULL)
                return true;
            else
                return false;
        } else{
            if (isset($_SESSION['flash']['inputErrors']) && $_SESSION['flash']['inputErrors'] != NULL)
                return true;
            else
                return false;
        }
    }


    /**
     * @param array $files
     * @return array
     *
     * Recebe o array de arquivos vindo do formulário e reordena os elementos
     */
	public function orderFiles ($files) {
		$arrangedFiles = array();
		foreach ($files['name'] as $i => $file) {
			if (!empty($files['name'][$i])) {
				$arrayFile['name'] = $files['name'][$i];
				$arrayFile['type'] = $files['type'][$i];
				$arrayFile['tmp_name'] = $files['tmp_name'][$i];
				$arrayFile['error'] = $files['error'][$i];
				$arrayFile['size'] = $files['size'][$i];

				$arrangedFiles[] = $arrayFile;
			}
		}

		return $arrangedFiles;
	}


    /**
     * @param string $filePath
     * @return void
     *
     * Carrega algum arquivo, a partir do caminho base do sistema
     */
	public function load ($filePath) {
        /** @noinspection PhpIncludeInspection */
        require_once BASEPATH . $filePath;
	}


    /**
     * @param string $viewName
     * @param string|array|null $data
     *
     * Carrega uma view
     */
	public function view ($viewName, $data = null) {
		ViewManager::make()->call($viewName, $data);
	}


    /**
     * @param string $modelName
     * @return bool
     *
     * Carrega um model
     */
	public function model ($modelName) {
		if (file_exists(BASEPATH . '/app/models/' . $modelName . '.php')) {
			load('/app/models/' . $modelName . '.php');
		    return true;
        } else
			return false;
	}


    /**
     * @param string $relativePath
     * @return string
     *
     * Obtém a rota para um dado recurso do sistema
     */
	public function route ($relativePath) {
        if(substr(SYSROOT, -1) == '/')
            $root = substr(SYSROOT, 0,-1);
        else
            $root = SYSROOT;


	    if (substr($relativePath, 0, 1) == '/')
            return $root.$relativePath;
	    else
	        return $root.'/'.$relativePath;
	}


    /**
     * @param string $assetFile
     * @return string
     *
     * Carrega um asset (recurso como CSS, JS)
     */
	public function asset ($assetFile) {
	    if(substr(SYSROOT, -1) == '/')
	        $folder = 'assets';
        else
	        $folder = '/assets';


	    if (substr($assetFile, 0, 1) == '/')
            return SYSROOT.$folder.$assetFile;
	    else
	        return SYSROOT.$folder.'/'.$assetFile;
	}


    /**
     * @return App
     *
     * Cria uma instância da classe
     */
	public static function make () {
	    return new self;
    }
}