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

    public function join ($table, $tableCol, $thisCol, $compare = '=') {
        $tableOrigin = $this->getTableVar();
        return (new DB)->select('*', $tableOrigin)->join($table, $tableCol, $thisCol, $compare);
    }

	public function where ($where, $whereVars = NULL) {
		$table = $this->getTableVar();
		return (new DB)->where($where, $whereVars, $table);
	}

	public function all () {
		return (new DB)->select(null, $this->getTableVar())->find();
	}

	// Obtém um dado buscando pela sua $primarykey igual ao valor passado à função
	public function get ($pk) {
		return $this->where($this->primarykey . ' = ?', [$pk])->find()[0];
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
