<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class Model
 *
 * Offers the base methods to be used by extended models
 */
abstract class Model {
	/**
	 * O nome dos modelos e das tabelas no BD devem ser iguais, exceto pela primeira letra,
	 * que pode ou não ser maiúscula no model.
	 */


    /**
     * @var string $primarykey
     */
	public $primarykey = 'id';

    /**
     * @var string $tableName
     */
    public $tableName = null;

    /**
     * @return object|string
     *
     * Salva o objeto atual no banco de dados (insere ou atualiza, dependendo da situação do objeto)
     */
	public function save () {
		return DB::save($this);
	}


    /**
     * @return bool
     *
     * Exclui o objeto atual do banco de dados
     */
	public function delete () {
		return DB::delete($this);
	}


    /**
     * @param string $selectedData
     * @return DB
     *
     * Inicia o processo de consulta ao banco de dados
     */
	public function select ($selectedData = '*') {
		$table = $this->getTableVar();
		return DB::make()->select($selectedData, $table);
	}


    /**
     * @param string $table
     * @param string $tableCol
     * @param string $thisCol
     * @param string $compare
     * @return DB
     *
     * Realiza a união da tabela referente ao objeto atual com uma outra tabela
     */
    public function join ($table, $tableCol, $thisCol, $compare = '=') {
        $tableOrigin = $this->getTableVar();
        return DB::make()->select(NULL, $tableOrigin)->join($table, $tableCol, $thisCol, $compare);
    }


    /**
     * @param string $where
     * @param null $whereVars
     * @return DB
     */
	public function where ($where, $whereVars = NULL) {
		$table = $this->getTableVar();
		return DB::make()->where($where, $whereVars, $table);
	}


    /**
     * @return array
     *
     * Obtém todos os objetos da mesma classe do objeto atual
     */
	public function all () {
		return DB::make()->select(null, $this->getTableVar())->find();
	}


    /**
     * @param string $pk
     * @return null
     *
     * Obtém um dado buscando pela sua $primarykey igual ao valor passado à função
     */
    public function get ($pk) {
		if (count($this->where($this->primarykey . ' = ?', [$pk])->find()) > 0)
			return $this->where($this->primarykey . ' = ?', [$pk])->find()[0];
		else
			return null;
	}


    /**
     * @return array
     *
     * Obtém os atributos do objeto e seus valores
     */
    public function getObjVars () {
        $reflection = new ReflectionClass($this);
        $parentVars = $reflection->getParentClass()->getProperties();
        $vars = $reflection->getProperties();

        $arrVars = array();

        foreach ($vars as $privateVar) {
            $arrVars[] = $privateVar->getName();
        }
        foreach ($parentVars as $privateVar) {
            if(!in_array($privateVar->getName(), $arrVars))
                $arrVars[] = $privateVar->getName();
        }

        return $arrVars;
    }


    /**
     * @return string
     *
     * Obtém a tabela no banco de dados referente ao objeto atual (assumindo que os nomes são iguais entre
     * o objeto e a tabela)
     */
	public function getTableVar () {
	    if($this->tableName != null)
	        return $this->tableName;

		$backtrace = debug_backtrace()[0];
		$class = get_class($backtrace['object']);

		return lcfirst($class);
	}


    /**
     * @return string
     *
     * Obtém a chave primária do objeto atual
     */
	public function getPrimaryKey() {
		return $this->primarykey;
	}

    /**
     * @return string
     *
     * Obtém o nome da tabela referente ao ovjeto tual
     */
	public function getTableName() {
		return $this->tableName;
	}


    /**
     * @return object
     *
     * Cria uma instância da classe atual ou da classe extendida
     */
    public static function make () {
	    $class = get_called_class();
        return new $class;
    }
}
