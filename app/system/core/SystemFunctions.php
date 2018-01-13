<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * System Functions
 *
 * Define the main system functions and aliases to other main methods
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */



if (!function_exists('dump')) {
    /**
     * @param object|string|array $object
     * @return void
     *
     * Exibe em detalhes os dados contidos no objeto passado por parâmetros
     */
    function dump($object)
    {
        echo '<pre>';
        var_dump($object);
        echo '</pre>';
    }
}

if (!function_exists('dd')) {
    /**
     * @param object|string|array $object
     * @return void
     *
     * Exibe em detalhes os dados contidos no objeto passado e encerra a execução do código
     */
    function dd($object)
    {
        dump($object);
        die;
    }
}


/**
 * @param object $data
 * @return string
 *
 * Converte o objeto e seus atributos (e valores) em uma string JSON
 */
function toJson ($data) {
    if((is_array($data) && hasObjectInArray($data)) || is_object($data))
        return json_encode(toArray($data));
    else
        return json_encode($data);
}


/**
 * @param object $data
 * @return array
 *
 * Converte o objeto e seus atributos (e valores) em um vetor
 */
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
                    //$className = $property->class;
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
                        } else { // Obtém o dado diretamente do atributo público
                            $name = $property->name;
                            $value = $obj->$name;
                        }

                        // Verifica se o atributo é um objeto ou um tipo primitivo de dados
                        if (is_object($value)) {
                            // Serializa as propriedades do objeto filho
                            $arrProperties[$i][$property->name] = toArray($value);
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

/**
 * @return array
 *
 * Filtra os dados vindos pelo método POST
 */
function filterPost () {
	return filter_var_array($_POST, FILTER_SANITIZE_STRING);
}


/**
 * @return array
 *
 * Filtra os dados vindos pelo método GET
 */
function filterGet () {
	return filter_var_array($_GET, FILTER_SANITIZE_STRING);
}


/**
 * @param string $url
 * @param int $status
 * @return App
 *
 * Executa o método redirect() contido na classe App (alias do método)
 */
function redirect ($url = '', $status = 200) {
	return App::redirect($url, $status);
}


/**
 * @return App $app
 *
 * Executa o método back() contido na classe App (alias do método)
 */
function back () {
	return App::back();
}



// Funções referentes ao uso de sessão, cookie e flash session

/**
 * @param string $name
 * @param string|array|null $data
 * @return null
 *
 * Executa o método session() contido na classe App (alias do método)
 */
function session ($name, $data = null) {
	return App::make()->session($name, $data);
}


/**
 * @param string $name
 * @param string|array|null $data
 * @return bool
 *
 * Executa o método cookie() contido na classe App (alias do método)
 */
function cookie ($name, $data = null) {
	return App::make()->cookie($name, $data);
}


/**
 * @param string $name
 * @return bool
 *
 * Executa o método unsetcookie() contido na classe App (alias do método)
 */
function unsetcookie ($name) {
	return App::make()->unsetcookie($name);
}


/**
 * @param string $name
 * @param string|array|null $data
 * @return bool
 *
 * Executa o método flash() contido na classe App (alias do método)
 */
function flash ($name, $data = null) {
	return App::make()->flash($name, $data);
}


/**
 * @param string $name
 * @return bool
 *
 * Executa o método hasFlash() contido na classe App (alias do método)
 */
function hasFlash ($name) {
	return App::make()->hasFlash($name);
}


/**
 * @param string $fieldName
 * @return bool
 *
 * Função para preencher o campo de formulário com o último valor inserido (usando flash)
 * Executa o método oldVal() contido na classe App (alias do método)
 */
function oldVal ($fieldName) {
	return App::make()->oldVal($fieldName);
}


/**
 * @param string $fieldName
 * @return bool
 *
 * Exibe a mensagem de erro quando retornado da função de validação
 * Executa o método getInputErrors() contido na classe App (alias do método)
 */
function getInputErrors ($fieldName) {
	return App::make()->getInputErrors($fieldName);
}


/**
 * @param string $fieldName
 * @return bool
 *
 * Exibe se existe algum erro de input salvo na sessão
 * Executa o método checkInputErrors() contido na classe App (alias do método)
 */
function checkInputErrors ($fieldName) {
	return App::make()->checkInputErrors($fieldName);
}


/**
 * @param array $files
 * @return array
 *
 * Recebe o array de arquivos vindo do formulário e reordena os elementos
 * Executa o método orderFiles() contido na classe App (alias do método)
 */
function orderFiles (array $files) {
	return App::make()->orderFiles($files);
}


/**
 * @param string $filePath
 * @return void
 *
 * Executa o método load() contido na classe App (alias do método)
 */
function load ($filePath) {
	App::make()->load($filePath);
}


/**
 * @param $viewName
 * @param string|array|null $data
 * @return void
 *
 * Executa o método view() contido na classe App (alias do método)
 */
function view ($viewName, $data = null) {
	App::make()->view($viewName, $data);
}


/**
 * @param string $modelName
 * @return bool
 *
 * Executa o método model() contido na classe App (alias do método)
 */
function model ($modelName) {
	return App::make()->model($modelName);
}


/**
 * @param string $relativePath
 * @return string
 *
 * Executa o método route() contido na classe App (alias do método)
 */
function route ($relativePath) {
	return App::make()->route($relativePath);
}


/**
 * @param string $assetFile
 * @return string
 *
 * Executa o método asset() contido na classe App (alias do método)
 */
function asset ($assetFile) {
	return App::make()->asset($assetFile);
}


/**
 * @return string
 *
 * Obtém o método utilizado na requisição atual
 */
function getRequest () {
    return strtolower($_SERVER['REQUEST_METHOD']);
}


/**
 * @param array $array
 * @return bool
 *
 * Verifica se existe um objeto dentro do vetor passado por parâmetro
 */
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


/**
 * @param object $data
 * @return array
 *
 * Converte um dado ou objeto em um array, tentando preencher seus atributos
 */
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
            } else { // Obtém o dado diretamente do atributo público
                $name = $property->name;
                $value = $data->$name;
            }
			
			// Verifica se o atributo é um objeto ou um tipo primitivo de dados
			if (is_object($value)){
				// Serializa as propriedades do objeto filho
				$arrProperties[$className][$property->name] = toArray($value);
			} else {
				$arrProperties[$className][$property->name] = $value;
			}
		}
	}

	return $arrProperties;
}