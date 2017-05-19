<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class JoseModel extends Model {
	private $nome;
	private $sexo;
	private $maria;

	public function getNome(){
		return $this->nome;
	}

	public function getSexo(){
		return $this->sexo;
	}

	public function getMaria(){
		return $this->maria;
	}
	
	public function __construct(MariaModel $maria)
	{
		$this->nome = "";
		$this->sexo = "";
		$this->maria = $maria;

		echo $this->jsonSerialize();
	}
}