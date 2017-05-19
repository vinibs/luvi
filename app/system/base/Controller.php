<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

abstract class Controller {
	public function getRequest () {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

}