<?php

/**
 * Configurations (in /config) and class (in /src) autoloader
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020 - Luvi
 * @link https://github.com/vinibs/luvi
 * @license MIT License
 */
spl_autoload_register(NULL, FALSE);

// Function to define the namespace prefix constant
function setNamespacePrefix (?string $configNamePrefix) : void
{
    // Loads the base namespace, based on the configuration, if any
    $namespacePrefix = null;
    if (!is_null($configNamePrefix)) {
        $namespacePrefix = $configNamePrefix;

        // Adds a "\" if needed
        if (substr($configNamePrefix, -1) !== '\\') {
            $namespacePrefix .= '\\';
        }
    }
    else {
        $namespacePrefix = 'App\\';
    }
    
    // Defines the namespace prefix constant
    define('namespacePrefix', $namespacePrefix);
}

// Includes all JSON files in /config root directory
foreach (glob(__DIR__ . '/../../config/*.json') as $file) {
    // Gets the string position where begins the file name
    $fileNameStart = strrpos($file, '/') + 1;
    // Gets the name of the file that is currently being read
    $fileName = substr($file, $fileNameStart);

    // Splits the file name in its dots (".")
    $fileNameParts = explode('.', $fileName); 
    
    // Defines that the variable name will be the
    // first block from the file name
    $varName = $fileNameParts[0] . 'Config';

    // Gets the file's content
    $content = json_decode(file_get_contents($file));

    // Defines the constant with the file's value
    $$varName = $content;
}

// Includes all PHP files that are in /config root directory
foreach (glob(__DIR__ . '/../../config/*.php') as $file) {
    include_once $file;
}

// After including the config files, calls the function to
// set the namespace prefix constant
setNamespacePrefix($appConfig->namespacePrefix ?? null);

// Automatically loads all classes inside the /src folder
spl_autoload_register(function (string $fullClass) : void
{
    // Gets the current direcory
    $baseDir = __DIR__ . '/../';

    // Does the class use the namespace's prefix?
    $len = strlen(namespacePrefix);
    if (strncmp(namespacePrefix, $fullClass, $len) !== 0) {
        // No, so goes to the next registered autoload
        return;
    }

    // Gets the resultant's path parts to standardize
    $class_parts = explode('\\', $fullClass);
    $standarized_class = '';

    // Removes the first uppercase characters from the class' path
    foreach ($class_parts as $index => $part) {
        // If it is the last item in the list (the class name),
        // includes directly the text in the standardized string
        if ($index === count($class_parts) - 1) {
            $standarized_class .= $part;
        }
        // If it is part of the path, removes the uppercase characters
        else {
            $standarized_class .= strtolower($part) . '/';
        }
    }

    // Replaces the namespace separator ("\") with a slash
    $className = str_replace('\\', '/', substr($fullClass, $len));

    
    $classFile = $baseDir . $className;

    // Sets to search file with ".php" and ".class.php" files
    $classExtensions = ['.php', '.class.php'];

    // Checks for every file format/extension to import
    foreach ($classExtensions as $extension) {
        // If the class exists, include it
        if (file_exists($classFile . $extension)) {
            include_once $classFile . $extension;
            break;
        }
    }
});

// Tries to load the classes in the /vendor directory (if it exists)
spl_autoload_register(function ($vendorClass) : void {
    // Path of the base directory
    $vendorPath = __DIR__ . '/../../vendor/';
    // File path, replacing "\" with "/"
    $filePath = $vendorPath . str_replace('\\', '/', $vendorClass) . '.php';

    // Does the path points to a valid file?
    if (file_exists($filePath)) {
        // Yes, includes it
        include_once $filePath;
    }
});