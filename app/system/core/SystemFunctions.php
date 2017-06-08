<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/* System Functions */

function dump ($object) {
	echo '<pre>';
	var_dump($object);
	echo '</pre>';
}

function dd ($object) {
	dump($object);
	die;
}

// Converte o objeto e seus atributos (e valores) em uma string JSON
function jsonSerialize ($data) {
	return json_encode(toArray($data));
}

// Converte o objeto e seus atributos (e valores) em um vetor
function toArray ($data) {
	$arrProperties = array();
	if(is_array($data) && sizeof($data) > 1){
		// Passa por todas as posições do vetor
		foreach($data as $i => $obj) {
			$reflection = new ReflectionClass($obj);
			$properties = $reflection->getProperties();

			// Passa por todas as propriedades do objeto e obtém seus valores em um vetor
			foreach ($properties as $property) {
				$className = $property->class;
				// Não adiciona o atributo 'primarykey' ao array de propriedades do objeto
				if ($property->name != 'primarykey') {
					$function = 'get'.ucfirst($property->name); // Obtém o nome da função get
					
					if( method_exists($obj, $function) ) {
						$value = $obj->$function(); // Obtem o valor da propriedade
					} else {
						$value = null;
					}
					
					// Verifica se o atributo é um objeto ou um tipo primitivo de dados
					if (is_object($value)){
						// Serializa as propriedades do objeto filho
						$arrProperties[$i][$property->name] = $value->toArray();
					} else {
						$arrProperties[$i][$property->name] = $value;
					}
				}
			}
		}
	} else if (is_array($data) && sizeof($data) == 1){
		$arrProperties = singleToArray($data[0]);
	} else if (is_array($data) && sizeof($data) == 0){
		$arrProperties = [];
	} else {
		$arrProperties = singleToArray($data);
	}
	return $arrProperties;
}

/**
 * TODAS AS FUNÇÕES ABAIXO SÃO FUNÇÕES ESSENCIAIS PARA O FUNCIONAMENTO DO SISTEMA OU ALIAS DELAS
 */

function filterPost () {
	return filter_var_array($_POST, FILTER_SANITIZE_STRING);
}

function filterPut () {
	return filter_var_array($_PUT, FILTER_SANITIZE_STRING);
}

function filterGet () {
	return filter_var_array($_GET, FILTER_SANITIZE_STRING);
}

function redirect ($url = '', $status = 200) {
	return App::redirect($url, $status);
}

function back () {
	return App::back();
}

// Funções referentes ao uso de sessão, cookie e flash session
function session ($name, $data = null) {
	return (new App)->session($name, $data);
}

function cookie ($name, $data = null) {
	return (new App)->cookie($name, $data);
}

function unsetcookie ($name) {
	return (new App)->unsetcookie($name);
}

function flash ($name, $data = null) {
	return (new App)->flash($name, $data);
}

// Função para preencher o campo de formulário com o último valor inserido (usando flash)
function oldVal ($fieldName) {
	return (new App)->oldVal($fieldName);
}

// Exibe a mensagem de erro quando retornado da função de validação
function getInputErrors ($fieldName) {
	return (new App)->getInputErrors($fieldName);
}

// Exibe se existe algum erro de input salvo na sessão
function checkInputErrors ($fieldName = NULL) {
	return (new App)->checkInputErrors($fieldName);
}

// Recebe o array de arquivos vindo do formulário e reordena os elementos
function orderFiles (array $files) {
	return (new App)->orderFiles($files);
}

function load ($filePath) {
	return (new App)->load($filePath);
}

function view ($viewName, $data = null) {
	return (new App)->view($viewName, $data);
}

function model ($modelName) {
	return (new App)->model($modelName);
}

function singleToArray ($data) {
	$reflection = new ReflectionClass($data);
	$properties = $reflection->getProperties();

	$arrProperties = array();

	// Passa por todas as propriedades do objeto e obtém seus valores em um vetor
	foreach ($properties as $property) {
		$className = $property->class;
		// Não adiciona o atributo 'primarykey' ao array de propriedades do objeto
		if ($property->name != 'primarykey') {
			$function = 'get'.ucfirst($property->name); // Obtém o nome da função get
			
			if( method_exists($data, $function) ) {
				$value = $data->$function(); // Obtem o valor da propriedade
			} else {
				$value = null;
			}
			
			// Verifica se o atributo é um objeto ou um tipo primitivo de dados
			if (is_object($value)){
				// Serializa as propriedades do objeto filho
				$arrProperties[$className][$property->name] = $value->toArray();
			} else {
				$arrProperties[$className][$property->name] = $value;
			}
		}
	}

	return $arrProperties;
}
