<?php

namespace App\Core;

use App\Core\I18n;

/**
 * Class View
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
class View {
    /**
     * A instância da classe (modelo singleton)
     * @var View
     */
    private static $instance;

    /**
     * Define o caminho para o diretório de views
     * @var string
     */
    private static $viewPath = __DIR__ . '/../view/';

    /**
     * Define o nome da pasta pública
     * @var string
     */
    private $publicFolder;
   

    /**
     * Define os atributos da classe 
     * 
     * @return void
     */
    private function __construct () 
    { 
        global $appConfig;
        $this->publicFolder = $appConfig->publicFolder ?? 'public';
    }

    /**
     * Obtém ou cria a instância do singleton
     * 
     * @return View
     */ 
    public static function getInstance () : View
    {
        // Não foi criada uma instância ainda?
        if (is_null(self::$instance)) {
            // Cria uma nova instância
            self::$instance = new self;
        }

        // Retorna a instância da classe
        return self::$instance;
    }


    /**
     * Carrega a view dentro do contexto de objeto
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
        // Define o status HTTP da resposta
        http_response_code($httpStatus);
        // Retorna o resultado da inclusão do arquivo
        return require_once $filePath;
    }

    /**
     * Inclui uma view em uma outra view já carregada
     * (É um atalho - alias - para o método estático load())
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
     * Carrega uma view com base no caminho/nome, a partir da
     * raiz do diretório de views
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
        // Identifica o arquivo com o prefixo ".view"
        $viewFile = self::$viewPath . $viewFilePath;
        // Busca arquivos .php ou .html para renderizar
        $extensions = [
            '.php', 
            '.phtml', 
            '.html',
            '.view.php',
            '.view.phtml',
            '.view.html'
        ];

        // Busca identificar o arquivo com base na extensão
        foreach ($extensions as $ext) {
            // O arquivo com a extensão atual existe?
            if (file_exists($viewFile . $ext)) {
                // Sim, então define o status HTTP, 
                // realiza o require dele e retorna
                return View::getInstance()
                    ->loadFile($viewFile . $ext, $httpStatus, $viewData);
            }
        }

        // Não encontrou o arquivo, retorna status 500 e 
        // lança uma exceção com mensagem de erro
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
     * Obtém o caminho absoluto para um asset
     * 
     * @param string $assetPath The path to the asset inside public folder
     * 
     * @return string|bool
     */
    public function asset (string $assetPath) : ?string
    {
        // Retira barra no início do caminho do arquivo, se houver
        if (substr($assetPath, 0, 1) === '/') {
            $assetPath = substr($assetPath, 1);
        }

        // Constrói o caminho absoluto para verificar o arquivo
        $fullAssetPath = __DIR__ . '/../../' . $this->publicFolder
            . '/' . $assetPath;

        // Retorna o caminho absoluto (com base na raiz do
        // servidor - pasta pública) se o arquivo existir
        if (file_exists($fullAssetPath)) {
            return '/' . $assetPath;
        }

        // Retorna false se não encontrar o arquivo
        return false;
    }
}