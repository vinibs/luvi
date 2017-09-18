<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class App {
	
	public static function redirect ($url = '', $status = 200) {
		http_response_code($status);
		if (substr($url, 0, 7) == 'http://' || substr($url, 0, 8) == 'https://')
			header('Location: '.$url);
		else {
			if ($url[0] == '/') {
				$url = substr($url, 1);
			}
			header('Location: '.SYSROOT.'/'.$url);
		}
		return (new App);
	}

	public static function back () {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		return (new App);
	}

	public function session ($name, $data = null) {
		if(!is_null($data))
			return $_SESSION[$name] = $data;
		else {
		    if(isset($_SESSION[$name]))
			    return $_SESSION[$name];
            else
                return null;
        }
	}

	public function cookie ($name, $data = null) {
		if(!is_null($data))
			return setcookie($name, $data);
		else  {
		    if(isset($_COOKIE[$name]))
			    return $_COOKIE[$name];
            else
                return null;
        }
	}

	public function unsetcookie ($name) {
		unset($_COOKIE[$name]);
		return setcookie($name, null, -1);
	}

	public function flash ($name, $data = null) {
		if(!is_null($data))
			return $_SESSION['flash'][$name] = $data;
		else {
			if (isset($_SESSION['flash'][$name])) {
				$flash =  $_SESSION['flash'][$name];
				unset($_SESSION['flash'][$name]);
				return $flash;
			} else
				return FALSE;
		}
	}

	public function withValues () {
		flash('inputValues', $_POST);
		return $this;
	}

	public function withErrors ($inputErrors) {
		foreach ($inputErrors as $input => $error) {
            if (!isset($_SESSION['flash']['inputErrors'][$input]))
                $_SESSION['flash']['inputErrors'][$input] = '';

            $_SESSION['flash']['inputErrors'][$input] .= $error . '<br>';
        }
		return $this;
	}

	public function oldVal ($fieldName) {
		if (isset($_SESSION['flash']['inputValues'][$fieldName])) {
			$oldValue =  $_SESSION['flash']['inputValues'][$fieldName];
			unset($_SESSION['flash']['inputValues'][$fieldName]);
			return $oldValue;
		} else
			return FALSE;
	}

	public function getInputErrors ($fieldName) {
		if (isset($_SESSION['flash']['inputErrors'][$fieldName])) {
		$flashField = $_SESSION['flash']['inputErrors'][$fieldName];
		unset($_SESSION['flash']['inputErrors'][$fieldName]);
			return $flashField;
		} else
			return FALSE;
	}

    public function checkInputErrors ($fieldName = NULL) {
        if($fieldName != NULL) {
            if (isset($_SESSION['flash']['inputErrors'][$fieldName]) && $_SESSION['flash']['inputErrors'][$fieldName] != NULL)
                return true;
            else
                return false;
        } else{
            if (isset($_SESSION['flash']['inputErrors']) && $_SESSION['flash']['inputErrors'] != NULL)
                return TRUE;
            else
                return FALSE;
        }
    }

	// Recebe o array de arquivos vindo do formulário e reordena os elementos
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
	

	public function load ($filePath) {
		return require_once BASEPATH . $filePath;
	}

	public function view ($viewName, $data = null) {
		ViewManager::make()->call($viewName, $data);
	}

	public function model ($modelName) {
		if (file_exists(BASEPATH . '/app/models/' . $modelName . '.php'))
			load('/app/models/' . $modelName . '.php');
		else 
			return FALSE;
	}

	public function route ($relativePath) {
	    if (substr($relativePath, 0, 1) == '/')
            return SYSROOT.$relativePath;
	    else
	        return SYSROOT.'/'.$relativePath;
	}

	public function asset ($assetFile) {
	    if (substr($assetFile, 0, 1) == '/')
            return SYSROOT.'/assets'.$assetFile;
	    else
	        return SYSROOT.'/assets/'.$assetFile;
	}

	public static function make () {
	    return new self;
    }
}