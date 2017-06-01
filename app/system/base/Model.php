<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

abstract class Model {
	/**
	 * O nome dos modelos e das tabelas no BD devem ser iguais, exceto pela primeira letra,
	 * que pode ou não ser maiúscula no model.
	 */

	public $primarykey = 'id';

	public function save () {
		return DB::save($this);
	}	

	public function delete () {
		return DB::delete($this);
	}

	public function select ($selectedData = '*') {
		$table = $this->getTableVar();
		return (new DB)->select($selectedData, $table);
	}

	public function where ($where, $whereVars) {
		$table = $this->getTableVar();
		return (new DB)->where($where, $whereVars, $table);
	}

	public function all () {
		return (new DB)->select(null, $this->getTableVar())->find();
	}

	// Obtém um dado buscando pela sua $primarykey igual ao valor passado à função
	public function get ($pk) {
		return $this->where($this->primarykey . ' = :pk', array(':pk' => $pk))->find()[0];
	}

	// Converte o objeto e seus atributos (e valores) em uma string JSON
	public function jsonSerialize () {
		return json_encode($this->toArray());
	}

	// Converte o objeto e seus atributos (e valores) em um vetor
	public function toArray () {
		$reflection = new ReflectionClass($this);
		$properties = $reflection->getProperties();

		$arrProperties = array();

		// Passa por todas as propriedades do objeto e obtém seus valores em um vetor
		foreach ($properties as $property) {
			$className = $property->class;
			// Não adiciona o atributo 'primarykey' ao array de propriedades do objeto
			if ($property->name != 'primarykey') {
				$function = 'get'.ucfirst($property->name); // Obtém o nome da função get
				
				if( method_exists($this, $function) ) {
					$value = $this->$function(); // Obtem o valor da propriedade
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
		dump($arrProperties);
		return $arrProperties;
	}

	public function getObjVars () {
		$reflection = new ReflectionClass($this);
        $vars = $reflection->getProperties();

        $arrVars = array();

        foreach ($vars as $privateVar) {
         	$arrVars[] = $privateVar->getName();
        }

        return $arrVars;
	}

	public function getTableVar () {
		$backtrace = debug_backtrace()[0];
		$class = get_class($backtrace['object']);

		return lcfirst($class);
	}

	public function getPrimaryKey() {
		return $this->primarykey;
	}
}
