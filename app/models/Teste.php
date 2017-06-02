<?php  defined('INITIALIZED') OR exit('You cannot access this file directly');

class Teste extends Model {
	private $id;
	private $nome;

	public function setId($id){
		$this->id = $id; 
	}
	public function getId(){
		return $this->id;
	}

	public function setNome($nome){
		$this->nome = $nome;
	}
	public function getNome(){
		return $this->nome;
	}
}