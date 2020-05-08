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
     * Instância da classe (padrão singleton)
     * @var I18n
     */
    private static $instance;

    /**
     * Strings do idioma atual
     * @var array
     */
    private $currentLangStrings;

    /**
     * Identificação do idioma atual
     * @var string
     */
    private $currentLocale;

    /**
     * Construtor privado (padrão singleton)
     * 
     * @return void
     */
    private function __construct () { }

    /**
     * Obtém ou cria a instância do singleton
     * 
     * @return I18n
     */
    public static function getInstance () : I18n
    {
        // Não foi criada uma instância ainda?
        if (is_null(self::$instance)) {
            // Cria uma nova instância
            self::$instance = new self;
        }

        // Retorna a instância da classe
        return self::$instance;
    }

    // Obtém as strings do idioma informado
    /**
     * @param string $locale The locale of the needed strings
     * 
     * @return object
     * 
     * @throws \Exception If the locale file could not be found
     */
    private static function getLocaleStrings (string $locale) : object
    {
        // Obtém a instância da classe
        $i18n = self::getInstance();

        // O objeto já está armazenando os dados do idioma atual?
        if (
            is_null($i18n->currentLangStrings) ||
            strcmp($i18n->currentLocale, $locale) !== 0
        ) {
            // Não, então se prepara para carregar o JSON do idioma
            $fileLocation = __DIR__ . '/../lang/' . $locale . '.json';

            // O arquivo para esse idioma existe?
            if (!file_exists($fileLocation)) {
                // Não, gera uma exceção com mensagem de erro em inglês
                $errorMessage = 
                    'Locale file not found for "' . $locale . '"';
                throw new \Exception($errorMessage);
            }

            // Lê o arquivo
            $localeStrings = file_get_contents($fileLocation);
            // Processa o arquivo JSON
            $localeStrings = json_decode($localeStrings);

            // Armazena as strings vindas do JSON
            $i18n->currentLangStrings = $localeStrings;
            // Armazena qual é o idioma atualmente na memória
            $i18n->currentLocale = $locale;
        }

        // Retorna as strings do idioma selecionado
        return $i18n->currentLangStrings;
    }

    /**
     * Obtém a string para o idioma padrão ou o selecionado
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
        // Foi passado o idioma?
        if (is_null($locale)) {
            // Não, procura pelo valor na sessão e, se 
            // não encontrar, usa o default do sistema
            $locale = Session::get('locale') ?? defaultLocale;
        }

        // Obtém a lista de strings no idioma
        $strings = self::getLocaleStrings($locale);
        // Prepara a variável para definir a string a ser retornada
        $string = null;

        // Quebra o token para ler as subseções do arquivo
        $tokenParts = explode('.', $token);

        $errorMessage = 'Token not found: "' . $token . '"';

        // Itera sobre as partes (sub-objetos)
        foreach ($tokenParts as $i => $part) {
            // É o sub-objeto inicial?
            if ($i === 0) {
                // Existe o elemento na lista?
                if (isset($strings->$part)) {
                    // Sim, define o valor inicial da string
                    $string = $strings->$part;
                    continue;
                }
                // Não, gera exceção
                throw new \Exception($errorMessage);
            }

            // Não, pega o próximo objeto da lista
            if (isset($string->$part)) {
                $string = $string->$part;
                continue;
            }
            // Não encontrou, gera exceção
            throw new \Exception($errorMessage);
        }

        // Há parâmetros?
        if (is_null($params)) {
            // Não, retorna a string do token diretamente
            return $string;
        }

        // Processa os parâmetros na string
        foreach ($params as $key => $param) {
            // Monta a identificação do parâmetro
            $paramId = '{' . $key . '}';
            // Substitui a identificação do parâmetro pelo valor
            $string = str_replace($paramId, $param, $string);
        }

        return $string;
    }
}