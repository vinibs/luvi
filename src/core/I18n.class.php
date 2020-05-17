<?php

namespace App\Core;

use App\Core\Session;

/**
 * Class I18n
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
class I18n {
    /**
     * The class' instance (singleton model)
     * 
     * @var I18n
     */
    private static $instance;

    /**
     * Current language strings
     * 
     * @var array
     */
    private $currentLangStrings;

    /**
     * Current language identification
     * 
     * @var string
     */
    private $currentLocale;

    /**
     * Private constructor (singleton pattern)
     * 
     * @return void
     */
    private function __construct () { }

    /**
     * Gets or creates a singleton's instance
     * 
     * @return I18n
     */
    public static function getInstance () : I18n
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
     * Gets given language's strings 
     * 
     * @param string $locale The locale of the needed strings
     * @return object
     * @throws \Exception If the locale file could not be found
     */
    private static function getLocaleStrings (string $locale) : object
    {
        // Gets class' instance
        $i18n = self::getInstance();

        // Is the object storing current langage's data?
        if (
            is_null($i18n->currentLangStrings) ||
            strcmp($i18n->currentLocale, $locale) !== 0
        ) {
            // No, then prepares to load the language's JSON
            $fileLocation = __DIR__ . '/../lang/' . $locale . '.json';

            // Does the file for this language exist?
            if (!file_exists($fileLocation)) {
                // No, then generates an exception with error in english
                $errorMessage = 
                    'Locale file not found for "' . $locale . '"';
                throw new \Exception($errorMessage);
            }

            // Reads the file
            $localeStrings = file_get_contents($fileLocation);
            // Processes the JSON file
            $localeStrings = json_decode($localeStrings);

            // Stores the strings that come from the JSON file
            $i18n->currentLangStrings = $localeStrings;
            // Stores which is the current loaded language
            $i18n->currentLocale = $locale;
        }

        // Return ths string of the selected language
        return $i18n->currentLangStrings;
    }

    /**
     * Gets the string from the default or the selected language
     * 
     * @param string $token The needed string's token
     * @param null|array $params The values to be put into the string
     * @param null|string $locale The needed locale of the string
     * 
     * @return string
     */
    public static function get (
        string $token, 
        array $params = null, 
        string $locale = null
    ) : string 
    {
        // There was given a language?
        if (is_null($locale)) {
            // No, search for the value in session and,
            // if cannot find, uses the system default
            $locale = Session::get('locale') ?? defaultLocale;
        }

        // Gets the languages's strings list
        $strings = self::getLocaleStrings($locale);
        // Prepares the variable to set the string to return
        $string = null;

        // Splits the token to read file's subsections
        $tokenParts = explode('.', $token);

        $errorMessage = 'Token not found: "' . $token . '"';

        // Iterate through the parts (sub objects)
        foreach ($tokenParts as $i => $part) {
            // Is it the first sub object?
            if ($i === 0) {
                // Does the element exist in the list?
                if (isset($strings->$part)) {
                    // Yes, defines the string's inital value
                    $string = $strings->$part;
                    continue;
                }
                // No, throws an exception
                throw new \Exception($errorMessage);
            }

            // No, passes to the next object in the list
            if (isset($string->$part)) {
                $string = $string->$part;
                continue;
            }
            // Didn't find, throws an exception
            throw new \Exception($errorMessage);
        }

        // There are parameters?
        if (is_null($params)) {
            // No, returns the token's string directly
            return $string;
        }

        // PRocesses the string's parameters
        foreach ($params as $key => $param) {
            // Builds the parameter's identification
            $paramId = '{' . $key . '}';
            // Replaces the parameter's identification with the value
            $string = str_replace($paramId, $param, $string);
        }

        return $string;
    }
}