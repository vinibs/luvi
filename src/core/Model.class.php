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
     * Object with DB configurations imported from JSON
     * 
     * @var object
     */
    protected $dbConfig;

    /**
     * Initializes attributes' values
     * 
     * @return void
     */
    public function __construct () {
        // Imports the global DB configuration to the class
        global $dbConfig;
        $this->dbConfig = $dbConfig;
    }
}