<?php

namespace App\Core;

/**
 * Class Model
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
abstract class Model {
    /**
     * O objeto com as configurações de BD importadas do JSON
     * @var object
     */
    protected $dbConfig;

    /**
     * Inicializa o valor dos atributos
     * 
     * @return void
     */
    public function __construct () {
        // Importa a configuração global do BD para a classe
        global $dbConfig;
        $this->dbConfig = $dbConfig;
    }
}