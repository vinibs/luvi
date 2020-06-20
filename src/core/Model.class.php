<?php

namespace App\Core;

/**
 * Class Model
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020 - Luvi
 * @link https://github.com/vinibs/luvi
 * @license MIT License
 */
abstract class Model {
    /**
     * Object with DB configurations imported from JSON
     * 
     * @var object
     */
    protected $dbConfig;

    /**
     * PDO database connection
     * 
     * @var \PDO
     */
    protected $connection;

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

    /**
     * Connects to the database using a given database config object
     * 
     * @param object $dbConfig
     * 
     * @return \PDO
     */
    protected function connect (object $dbConfig) : \PDO
    {
        // Does the driver property exist in dbConfig?
        if (!isset($dbConfig->driver)) {
            // It doesn't, throws exception
            $errorMessage = I18n::get('error.driver_not_found_in_db_config');
            throw new \Exception($errorMessage);
        }

        // Does the host property exist in dbConfig?
        if (!isset($dbConfig->host)) {
            // It doesn't, throws exception
            $errorMessage = I18n::get('error.host_not_found_in_db_config');
            throw new \Exception($errorMessage);
        }

        // Does the database name property exist in dbConfig?
        if (!isset($dbConfig->database)) {
            // It doesn't, throws exception
            $errorMessage = I18n::get('error.database_not_found_in_db_config');
            throw new \Exception($errorMessage);
        }

        // Does the user property exist in dbConfig?
        if (!isset($dbConfig->user)) {
            // It doesn't, throws exception
            $errorMessage = I18n::get('error.user_not_found_in_db_config');
            throw new \Exception($errorMessage);
        }

        // Does the password property exist in dbConfig?
        if (!isset($dbConfig->password)) {
            // It doesn't, throws exception
            $errorMessage = I18n::get('error.password_not_found_in_db_config');
            throw new \Exception($errorMessage);
        }

        // Creates the object's connection variable
        $this->connection = new \PDO(
            "{$dbConfig->driver}:host={$dbConfig->host};"
            . "dbname={$dbConfig->database}",
            $dbConfig->user,
            $dbConfig->password
        );

        return $this->connection;
    }
}