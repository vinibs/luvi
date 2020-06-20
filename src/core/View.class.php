<?php

namespace App\Core;

use App\Core\I18n;

/**
 * Class View
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020 - Luvi
 * @link https://github.com/vinibs/luvi
 * @license MIT License
 */
class View {
    /**
     * Classe's instance (singleton model)
     * 
     * @var View
     */
    private static $instance;

    /**
     * Define the path to the views' directory
     * 
     * @var string
     */
    private static $viewPath = __DIR__ . '/../view/';

    /**
     * Defines the public folder's name
     * 
     * @var string
     */
    private $publicFolder;
   

    /**
     * Defines the classe's attributes
     * 
     * @return void
     */
    private function __construct () 
    { 
        global $appConfig;
        $this->publicFolder = $appConfig->publicFolder ?? 'public';
    }

    /**
     * Gets or creates the singleton's instance
     * 
     * @return View
     */ 
    public static function getInstance () : View
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
     * Loads the view in the object context
     * 
     * @param string $filePath The file path
     * @param int $httpStatus The HTTP status
     * @param null|array $viewData The data to be used within the view
     * 
     * @return bool|int
     */ 
    private function loadFile (
        string $filePath, 
        int $httpStatus, 
        array $viewData = null
    ) : int
    {
        // Defines the response HTTP status
        http_response_code($httpStatus);
        // Returns the result of file include
        return require_once $filePath;
    }

    /**
     * Includes a view in another, already loaded, view
     * (It is an alias to the static load() method)
     *  
     * @param string $viewFilePath The included view's file path
     * @param null|array $includedData
     * 
     * @return bool|int
     * 
     * @throws \Exception If the view file could not be found
     */
    private function include (
        string $viewFilePath, 
        array $includedData = null
    ) : int 
    {
        return View::load($viewFilePath, 200, $includedData);
    }

    /**
     * Loads a view based on its path/name, from the views
     * directory's root
     * 
     * @param string $viewFilePath The view's file path
     * @param int $httpStatus The HTTP status
     * @param null|array $viewData The data to be used within the view
     * 
     * @return bool|int
     * 
     * @throws \Exception If the view file could not be found
     */
    public static function load (
        string $viewFilePath, 
        int $httpStatus = 200, 
        array $viewData = null
    ) : int 
    {
        // Identify the file with ".view" prefix
        $viewFile = self::$viewPath . $viewFilePath;
        // Search for .php or .html files to render
        $extensions = [
            '.php', 
            '.phtml', 
            '.html',
            '.view.php',
            '.view.phtml',
            '.view.html'
        ];

        // Tries to identify the file based on its extension
        foreach ($extensions as $ext) {
            // There is a file with current extension?
            if (file_exists($viewFile . $ext)) {
                // Sets the HTTP header for HTML content
                header('Content-Type: text/html');

                // Yes. Then, defines the HTTP status,
                // requires the file and returns
                return View::getInstance()
                    ->loadFile($viewFile . $ext, $httpStatus, $viewData);
            }
        }

        // Didn't find the file. Returns HTTP 500 status
        // and throws an exception with error message
        http_response_code(500);
        $tokenParams = [
            'viewName' => $viewFilePath
        ];
        $errorMessage = I18n::get(
            'error.file_not_found_loading_view', 
            $tokenParams
        );
        throw new \Exception($errorMessage);
    }

    /**
     * Gets the absolute path to a public asset
     * 
     * @param string $assetPath The path to the asset inside public folder
     * 
     * @return string|bool
     */
    public function asset (string $assetPath) : ?string
    {
        // Removes slash at the start of file's path, if needed
        if (substr($assetPath, 0, 1) === '/') {
            $assetPath = substr($assetPath, 1);
        }

        // Builds the absolute path to verify the file
        $fullAssetPath = __DIR__ . '/../../' . $this->publicFolder
            . '/' . $assetPath;

        // Returns the absolute path (based on the server's 
        // root - the public folder) if the file exists
        if (file_exists($fullAssetPath)) {
            return '/' . $assetPath;
        }

        // Returns false if the file doesn't exist
        return false;
    }
}