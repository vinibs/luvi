<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

abstract class Controller {
	public function getRequest () {
		return getRequest();
	}

    public static function make () {
        $class = get_called_class();
        return new $class;
    }
}