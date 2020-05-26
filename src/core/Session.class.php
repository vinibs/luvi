<?php

namespace App\Core;

/**
 * Class Session
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
class Session {
    /**
     * The class' instance (singleton model)
     * 
     * @var Session
     */
    private static $instance;
    
    /**
     * Defines the session variable's ID
     * 
     * @var string
     */
    private $sessionID;
   

    /**
     * Private constructor (sigleton pattern)
     * 
     * @return void
     */
    private function __construct () 
    {
        // Checks if the session has already started
        if (session_status() == PHP_SESSION_NONE) {
            // No, then starts it
            session_start();
        }
        global $appConfig;
        $this->sessionID = $appConfig->sessionID ?? 'App';
    }

    /**
     * Gets or creates a singleton's instance
     * 
     * @return Session
     */ 
    public static function getInstance () : Session
    {
        // It wasn't created yet?
        if (is_null(self::$instance)) {
            // Creates a new instance
            self::$instance = new self;
        }

        // Returns the class' instance
        return self::$instance;
    }

    /**
     * Starts a session e returns the instance object
     *
     * @return Session
     */
    public static function start () : Session
    {
        // Alias to get the class' instance, which
        // already starts the session if needed
        return self::getInstance();
    }

    /**
     * Sets a session variable
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return bool
     */
    public static function set (string $name, $value) : bool
    {
        // Gets the class' instance, which
        // starts the session and its attributes
        $session = self::getInstance();
        // Sets the session variable with the given name
        $_SESSION[$session->sessionID][$name] = $value;

        // Returns wether the value was successfully set 
        // to the session or not
        return $_SESSION[$session->sessionID][$name] === $value ? 
            true : false;
    }

    /**
     * Returns the value of a session variable
     * 
     * @param string $name
     * 
     * @return mixed
     */
    public static function get (string $name)
    {
        // Gets the class' instance, which
        // starts the session and its attributes
        $session = self::getInstance();
        // Check if there is an index with the given name
        if (!isset($_SESSION[$session->sessionID][$name])) {
            // There isn't, returns null
            return null;
        }

        // Index exists, returns its value
        return $_SESSION[$session->sessionID][$name];
    }

    /**
     * Defines a flash session variable (if given the $value parameter)
     * or returns the stored value and destroys the variable
     * 
     * @param string $name
     * @param null|mixed $value
     * 
     * @return mixed
     */
    public static function flash (string $name, $value = null)
    {
        // Gets the class' instance, which
        // starts the session and its attributes
        $session = self::getInstance();
        // Generates the flash variable's ID inside
        // the session variable
        $flashId = $session->sessionID . '/flash';

        // Is $value parameter null?
        if (is_null($value)) {
            // Yes. Checks if there is an index with the given name
            if (!isset($_SESSION[$session->sessionID][$flashId][$name])) {
                // Index doesn't exist yet. Returns null
                return null;
            }

            // Index already exists, so gets its value
            // stored in flash with the given name
            $flashValue = $_SESSION[$session->sessionID][$flashId][$name];
            // Remove the flash with the given name
            unset($_SESSION[$session->sessionID][$flashId][$name]);
            // Returns the stored value
            return $flashValue;
        }

        // There isn't the $value attribute, so defines 
        // the flash session variable
        $_SESSION[$session->sessionID][$flashId][$name] = $value;

        // Returns wether the value was successfully set 
        // to the session or not
        return $_SESSION[$session->sessionID][$flashId][$name] === $value ? 
            true : false;
    }

    /**
     * Identifies if a flash variable with a given name exists
     * 
     * @param string $name
     * 
     * @return bool
     */
    public static function hasFlash (string $name) : bool
    {
        // Gets the class' instance, which
        // starts the session and its attributes
        $session = self::getInstance();
        // Generates the flash variable's ID inside
        // the session variable
        $flashId = $session->sessionID . '/flash';

        // Returns wether the session variable exists or not
        return isset($_SESSION[$session->sessionID][$flashId][$name]);
    }
}