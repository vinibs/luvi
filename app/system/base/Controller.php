<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

abstract class Controller {
	public function getRequest () {
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

}