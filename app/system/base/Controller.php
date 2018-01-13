<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/**
 * Class Controller
 *
 * Offers the base methods to me used by extended controllers
 */
abstract class Controller
{

    /**
     * @return string
     *
     * Obtém o método usado na requisição
     */
	public function getRequest () {
		return getRequest();
	}


    /**
     * @return object
     *
     * Cria uma instância da classe que chamou a função (controllers filhos deste)
     */
    public static function make () {
        $class = get_called_class();
        return new $class;
    }
}