<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class DB
 *
 * Offers methods to manage a database
 *
 * @author Vinicius Baroni Soares <vinibaronisoares@gmail.com>
 * @copyright 2017 Luvi
 */
class DB
{

    /**
     * @var string $select
     * @var string $from
     * @var string $join
     * @var string $where
     * @var string $extraSql
     * @var string $orderBy
     *
     * @var string $whereVars
     * @var string $table
     */
    private $select = '';
    private $from = '';
    private $join = '';
    private $where = '';
    private $extraSql = '';
    private $orderBy = '';

	private $whereVars;
	private $table;


    /**
     * @param object $object
     * @return string|object
     *
     * Salva um objeto no banco de dados (insere ou atualiza, dependendo da situação do objeto)
     */
	public static function save ($object)
    {
        $arrVars = $object->getObjVars();
        //print_r($object);

        // Remove o atributo 'primarykey' da lista
        $arr = $arrVars;
        $arrVars = array();
        foreach ($arr as $i => $var) {
            if ($var != 'primarykey') {
                $arrVars[] = $var;
            }
        }
        //print_r($arrVars);

        // Define o número que deve ser subtraído
        // da contagem máxima de elementos nas rotinas abaixo
        $varsSub = 0;
        if (in_array('id', $arrVars))
            $varsSub++;

        $table = $object->getTableVar();

        if (self::isNew($object)) {
            $sql = 'INSERT INTO `' . $table . '` (';
            foreach ($arrVars as $i => $var) {
                if ($var != 'id' && $var != 'primarykey') {
                    $sql .= $var;
                    if ($i < (sizeof($arrVars) - $varsSub))
                        $sql .= ', ';
                }
            }
            $sql .= ') VALUES (';
            foreach ($arrVars as $i => $var) {
                if ($var != 'id' && $var != 'primarykey') {
                    $sql .= ':' . $var;
                    if ($i < (sizeof($arrVars) - $varsSub))
                        $sql .= ', ';
                }
            }
            $sql .= ');';


        } else {
            $sql = 'UPDATE `' . $table . '` SET ';
            foreach ($arrVars as $i => $var) {
                if ($var != 'id' && $var != 'primarykey') {
                    $sql .= $var . ' = :' . $var;
                    if ($i < (sizeof($arrVars) - $varsSub))
                        $sql .= ', ';
                }
            }
            $sql .= ' WHERE ' . $object->primarykey . ' = :' . $object->primarykey . ';';
        }

        //echo "\n".$sql."\n";
        $con = self::connect();
        $stmt = $con->prepare($sql);

        foreach ($arrVars as $var) {
            if ($var != 'id' && $var != 'primarykey') {
                $funcNameParts = explode('_', $var);
                foreach ($funcNameParts as $i => $part) {
                    $funcNameParts[$i] = ucfirst($part);
                }
                $funcName = implode('', $funcNameParts);

                $getFunc = 'get' . $funcName;
                $stmt->bindValue(':' . $var, (string)$object->$getFunc());
                //echo '$stmt->bindValue(":"'.$var.', '.(string)$object->$getFunc().');'."\n";
            }
        }
        //exit;
        if (!self::isNew($object)) {
            $getFunc = 'get' . ucfirst($object->primarykey);
            $stmt->bindValue(':' . $object->primarykey, (int)$object->$getFunc());
        }

        if ($stmt->execute()) {
            // Se o objeto for novo, adiciona o ID recentemente adicionado ao objeto
            if(self::isNew($object)){
                $funcName = 'set' . ucfirst($object->getPrimaryKey());
                $object->$funcName($con->lastInsertId());
            }
            $con = null;
            $stmt = null;
            return $object;
        } else {
            dump($sql);
            $con = null;
            return $stmt->errorInfo()[2];
        }
	}


    /**
     * @param object $object
     * @return bool
     *
     * Exclui um objeto do banco de dados
     */
	public static function delete ($object) {
		if(self::isNew($object))
			return false;
		else {
            $table = $object->getTableVar();

            $sql = 'DELETE FROM `' . $table . '` WHERE ' . $object->primarykey . ' = :pk;';

            $objFunc = 'get' . ucfirst($object->primarykey);

            $con = self::connect();
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':pk', $object->$objFunc());

            if ($stmt->execute()){
                $con = null;
                $stmt = null;
                return true;
            }
            else{
                $con = null;
                $stmt = null;
                return false;
            }
        }
	}


    /**
     * @param string|null $selectedData
     * @param string $table
     * @return DB
     *
     * Inicia o processo de fazer um select no banco de dados
     */
	public function select ($selectedData = NULL, $table) {
		if($selectedData == NULL || $selectedData == '')
			$selectedData = '`'.$table.'`.*';

		$this->table = $table;


        // Inicia o SELECT ou adiciona elementos a ele
        if($this->select == '')
            $this->select = 'SELECT ' . $selectedData;
        else
            $this->select .= ', '.$selectedData;


        // Inicia o FROM ou adiciona elementos a ele
        if($this->from == '')
            $this->from = ' FROM `'.$table.'`';
        else
            $this->from .= ', `'.$table.'`';

		return $this;
	}


    public function join ($table, $tableCol, $thisCol, $compare = '=') {
        $this->join .= ' JOIN `' . $table . '` ON `' . $table.'`.'.$tableCol .' '. $compare .' `'
            . $this->table.'`.'.$thisCol.' ';

        return $this;
    }


    /**
     * @param string $where
     * @param null|array $whereVars
     * @param null|string $table
     * @return DB
     *
     * Realiza um where para a consulta select ao banco de dados
     *
     *
     * Padrão para o where:
     * Caso 1: 'Element = ? AND OtherElement = ?'
     * Caso 2: 'Element = ?'
     *
     * Padrão para o whereVars:
     * Caso 1: ['Value1', 'Value2']
     * Caso 2: 'Value1'
     */
	public function where ($where, $whereVars = NULL, $table = NULL) {
		if (!isset($table)) {
			if (isset($this->table))
				$table = $this->table;
			else
				die('ERROR: Cannot search with undefined table');
		} else
			$this->table = $table;

        // Reconstroi um SELECT básico caso a função seja usada sem o auxílio da select()
		if ($this->select == '')
			$this->select = 'SELECT `' . $table . '`.* ';

		if ($this->from == '')
		    $this->from = 'FROM `' . $table . '`';


        if ($this->where == '')
            $this->where = ' WHERE ' . $where;
        else
            $this->where .= ' AND ' . $where;


        if(is_array($whereVars)) {
            foreach ($whereVars as $var) {
                $this->whereVars[] = $var;
            }
        } else
            $this->whereVars[] = $whereVars;

		return $this;
	}


    /**
     * @param string $orderValue
     * @param string $order
     * @return DB
     *
     * Acrescenta um order by ao select no banco de dados
     */
	public function orderBy ($orderValue, $order = 'ASC') {
	    if($this->orderBy == '')
            $this->orderBy = ' ORDER BY '.$orderValue.' '.$order;
        else
            $this->orderBy .= ', '.$orderValue.' '.$order;

		return $this;
	}


    /**
     * @param string $sql
     * @return DB
     *
     * Adiciona um trecho SQL qualquer à consulta select no banco de dados
     */
	public function extraSql ($sql) {
        $this->extraSql .= ' ' . $sql;

		return $this;
	}


    /**
     * @return array
     *
     * Executa a consulta SQL previamente preparada pelos comandos select(), where()...
     */
	public function find () {

		$con = self::connect();
        $sql = $this->select . $this->from . $this->join . $this->where . $this->extraSql . $this->orderBy;
        $stmt = $con->prepare($sql);

		if (!empty($this->whereVars)) {
			if (is_array($this->whereVars)) {
				$i = 1;
				foreach ($this->whereVars as $bind => $whereVar) {
					$stmt->bindValue($i, $whereVar);
					$i++;
				}
			} else
				$stmt->bindValue(1, $this->whereVars);
		}

		$stmt->execute();

        // Limpa as variáveis do where após executar a consulta
        $this->whereVars = null;

		// Retorna um objeto da classe definida pela tabela ($this->table)
		$return =  $stmt->fetchAll(PDO::FETCH_CLASS, $this->table);

        $con = null;
        $stmt = null;
        return $return;
	}


    /**
     * @return string
     *
     * Obtém o SQL gerado pelas demais funções da classe (select, where...)
     */
    public function generatedSql () {
        return $this->select . $this->from . $this->join . $this->where . $this->extraSql . $this->orderBy;
    }


    /**
     * @param object $object
     * @return bool
     *
     * Verifica se um dado objeto é novo no banco de dados
     */
    public static function isNew ($object) {
		$table = $object->getTableVar();

		$sql = 'SELECT `'.$table.'`.* FROM `' . $table . '` WHERE '.$object->primarykey.' = :'.$object->primarykey.';';

		$con = self::connect();
		$stmt = $con->prepare($sql);

		$getFunc = 'get'.ucfirst($object->primarykey);
		$stmt->bindValue(':'.$object->primarykey, (int) $object->$getFunc());

		$stmt->execute();

		if ($stmt->rowCount() > 0){
            $con = null;
            $stmt = null;
            return false;
        }
		else {
            $con = null;
            $stmt = null;
            return true;
        }
	}


    /**
     * @param string $table
     * @return array
     *
     * Obtém todos os registros de uma dada tabela do banco de dados
     */
    public static function all($table) {
        $sql = 'SELECT `'.$table.'`.* FROM `'.$table.'`;';

        $con = self::connect();
        $stmt = $con->prepare($sql);


        $stmt->execute();
        // Retorna um objeto da classe definida pela tabela ($this->table)
        $return = $stmt->fetchAll(PDO::FETCH_CLASS, $table);

        $con = null;
        $stmt = null;
        return $return;
    }


    /**
     * @return PDO
     *
     * Realiza a conexão do sistema com o banco de dados, utilizando as constantes de sistema
     */
    public static function connect () {
		return new PDO(DB_DRIVER.":host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	}


    /**
     * @return DB
     *
     * Cria uma instância da classe
     */
    public static function make () {
        return new self;
    }
}
