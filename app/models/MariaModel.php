<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

class MariaModel extends Model {
	
	private $name;
	private $gender;

	public function getName(){
		return $this->name;
	}
	
	public function getGender(){
		return $this->gender;
	}

	public function __construct()
	{
		$this->name = "Maria";
		$this->gender = "Fem";
		echo $this->jsonSerialize();
	}

}