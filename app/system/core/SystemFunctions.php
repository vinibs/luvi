<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/* System Functions */


if (!function_exists('dump')) {

    function dump($object)
    {
        echo '<pre>';
        var_dump($object);
        echo '</pre>';
    }
}

if (!function_exists('dd')) {

    function dd($object)
    {
        dump($object);
        die;
    }
}

// Converte o objeto e seus atributos (e valores) em uma string JSON
function jsonSerialize ($data) {
    if((is_array($data) && hasObjectInArray($data)) || is_object($data))
        return json_encode(toArray($data));
    else
        return json_encode($data);
}


// Converte o objeto e seus atributos (e valores) em um vetor
function toArray ($data) {
	$arrProperties = array();
	if(is_array($data) && sizeof($data) > 1){
		// Passa por todas as posições do vetor
		foreach($data as $i => $obj) {
            // Verifica se a posiçãoatual do vetor é vetor também
		    if(is_array($obj)) {
		        $arrProperties[$i] = toArray($obj);

            } else {
		        // Se não for vetor, trata como objeto
                $reflection = new ReflectionClass($obj);
                $properties = $reflection->getProperties();

                // Passa por todas as propriedades do objeto e obtém seus valores em um vetor
                foreach ($properties as $property) {
                    $className = $property->class;
                    // Não adiciona o atributo 'primarykey' ao array de propriedades do objeto
                    if ($property->name != 'primarykey') {

                        $funcNameParts = explode('_', $property->name);
                        foreach ($funcNameParts as $j => $part) {
                            $funcNameParts[$j] = ucfirst($part);
                        }
                        $funcName = implode('', $funcNameParts);

                        $function = 'get' . $funcName; // Obtém o nome da função get

                        if (method_exists($obj, $function)) {
                            $value = $obj->$function(); // Obtem o valor da propriedade
                        } else {
                            $value = null;
                        }

                        // Verifica se o atributo é um objeto ou um tipo primitivo de dados
                        if (is_object($value)) {
                            // Serializa as propriedades do objeto filho
                            $arrProperties[$i][$property->name] = $value->toArray();
                        } else {
                            // Cria um vetor com todas as propriedades do objeto
                            $arrProperties[$i][$property->name] = $value;
                        }
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

//function filterPut () {
//	return filter_var_array($_PUT, FILTER_SANITIZE_STRING);
//}

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
	return App::make()->session($name, $data);
}

function cookie ($name, $data = null) {
	return App::make()->cookie($name, $data);
}

function unsetcookie ($name) {
	return App::make()->unsetcookie($name);
}

function flash ($name, $data = null) {
	return App::make()->flash($name, $data);
}

function hasFlash ($name) {
	return App::make()->hasFlash($name);
}

// Função para preencher o campo de formulário com o último valor inserido (usando flash)
function oldVal ($fieldName) {
	return App::make()->oldVal($fieldName);
}

// Exibe a mensagem de erro quando retornado da função de validação
function getInputErrors ($fieldName) {
	return App::make()->getInputErrors($fieldName);
}

// Exibe se existe algum erro de input salvo na sessão
function checkInputErrors ($fieldName = NULL) {
	return App::make()->checkInputErrors($fieldName);
}

// Recebe o array de arquivos vindo do formulário e reordena os elementos
function orderFiles (array $files) {
	return App::make()->orderFiles($files);
}

function load ($filePath) {
	return App::make()->load($filePath);
}

function view ($viewName, $data = null) {
	return App::make()->view($viewName, $data);
}

function model ($modelName) {
	return App::make()->model($modelName);
}

function route ($relativePath) {
	return App::make()->route($relativePath);
}

function asset ($assetFile) {
	return App::make()->asset($assetFile);
}


function getRequest () {
    return strtolower($_SERVER['REQUEST_METHOD']);
}


function hasObjectInArray ($array = array()) {
    $has = array();
    foreach($array as $i => $d){
        if(is_array($d)){
            $has[$i] = hasObjectInArray($d);
        } else {
            if(is_object($d))
                $has[$i] = true;
            else
                $has[$i] = false;
        }
    }
    return in_array(true, $has);
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

            $funcNameParts = explode('_', $property->name);
            foreach ($funcNameParts as $j => $part) {
                $funcNameParts[$j] = ucfirst($part);
            }
            $funcName = implode('', $funcNameParts);

            $function = 'get' . $funcName; // Obtém o nome da função get

            if (method_exists($data, $function)) {
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
