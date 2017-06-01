<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class DB {
	private $sql;
	private $whereVars;
	private $table;

	public static function save ($object) {
		$arrVars = $object->getObjVars();

		$table = $object->getTableVar();

		if (self::isNew($object)) {
			$sql = 'INSERT INTO ' . $table . ' (';
			foreach ($arrVars as $i => $var) {
				if($var != 'id'){
					$sql .= $var;
					if ($i < (sizeof($arrVars) - 1))
						$sql .= ', ';
				}
			}
			$sql .= ') VALUES (';
			foreach ($arrVars as $i => $var) {
				if($var != 'id'){
					$get = 'get' . ucfirst($var);
					$sql .= ':' . $var;
					if ($i < (sizeof($arrVars) - 1))
						$sql .= ', ';
				}
			}
			$sql .= ');';


		} else {
			$sql = 'UPDATE ' . $table . ' SET ';
			foreach ($arrVars as $i => $var) {
				if($var != 'id'){
					$get = 'get' . ucfirst($var);
					$sql .= $var . ' = :' . $var;
					if ($i < (sizeof($arrVars) - 1))
						$sql .= ', ';
				}
			}
			$sql .= ' WHERE id = :id;';
		}

		
		$con = self::connect();
		$stmt = $con->prepare($sql);

		foreach ($arrVars as $var) {
			if ($var != 'id') {
				$getFunc = 'get' . ucfirst($var);
				$stmt->bindValue(':'.$var, (string) $object->$getFunc());
			}
		}

		if (!self::isNew($object)) 
			$stmt->bindValue(':id', (int) $object->getId());


		if ($stmt->execute())
			return $object;
		else
			return NULL;
	}

	public static function delete ($object) {
		if(self::isNew($object))
			return FALSE;
		else
			$arrVars = $object->getObjVars();
			$table = $object->getTableVar();

			$sql = 'DELETE FROM ' . $table . ' WHERE '.$object->primarykey.' = :pk;';

			$objFunc = 'get'.ucfirst($object->primarykey);

			$con = self::connect();
			$stmt = $con->prepare($sql);
			$stmt->bindValue(':pk', $object->$objFunc());

			if ($stmt->execute())
				return TRUE;
			else
				return FALSE;
	}

	public function select ($selectedData = NULL, $table) {
		if(empty($selectedData))
			$selectedData = '*';

		$this->table = $table;

		$this->sql = 'SELECT ' . $selectedData . ' FROM ' . $table;

		return $this;
	}

	/**
	 * Padrão para o where: 
	 * Caso 1: 'Element = ? AND OtherElement = ?'
	 * Caso 2: 'Element = ?'
	 *
	 * Padrão para o whereVars: 
	 * Caso 1: ['Value1', 'Value2']
	 * Caso 2: 'Value1'
	 */
	public function where ($where, $whereVars, $table = NULL) {
		if (!isset($table)) {
			if (isset($this->table))
				$table = $this->table;
			else
				die('ERROR: Cannot search with undefined table');
		} else
			$this->table = $table;

		if (empty($this->sql))
			$this->sql = 'SELECT * FROM ' . $table . ' WHERE ' . $where;
		else
			$this->sql .= ' WHERE ' . $where;

		$this->whereVars = $whereVars;

		return $this;
	}

	public function orderBy ($orderValue, $order = 'ASC') {
		if (empty($this->sql)) 
			return FALSE;
		else {
			$orderExplode = explode('ORDER BY', $this->sql);

			if (sizeof($orderExplode) == 1)
				$this->sql .= ' ORDER BY ';
			else
				$this->sql .= ', ';

			$this->sql .= $orderValue . ' ' . $order;
		}

		return $this;
	}

	public function extraSql ($sql) {
		if (empty($this->sql))
			return FALSE;
		else 
			$this->sql .= ' ' . $sql;

		return $this;
	}

	public function find () {
		$this->sql .= ';';

		$con = self::connect();
		$stmt = $con->prepare($this->sql);

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
		// Retorna um objeto da classe definida pela tabela ($this->table)
		return $stmt->fetchAll(PDO::FETCH_CLASS, $this->table);
		// $result = $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna um array
	}


	public static function isNew ($object) {
		$arrVars = $object->getObjVars();
		$table = $object->getTableVar();

		$sql = 'SELECT * FROM ' . $table . ' WHERE id = :id;';

		$con = self::connect();
		$stmt = $con->prepare($sql);
		$stmt->bindValue(':id', (int) $object->getId());

		$stmt->execute();

		if ($stmt->rowCount() > 0)
			return FALSE;
		else 
			return TRUE;
	}	

	public static function connect () {
		return new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
	}
}
