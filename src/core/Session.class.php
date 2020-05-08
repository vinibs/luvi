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
     * A instância da classe (modelo singleton)
     * @var Session
     */
    private static $instance;
    
    /**
     * Define a identificação da variável de sessão
     * @var string
     */
    private $sessionID;
   

    /**
     * Construtor privado (padrão singleton) 
     * 
     * @return void
     */
    private function __construct () 
    {
        // Verifica se a sessão já está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            // Não, então inicia
            session_start();
        }
        global $appConfig;
        $this->sessionID = $appConfig->sessionID ?? 'App';
    }

    /**
     * Obtém ou cria a instância do singleton
     * 
     * @return Session
     */ 
    public static function getInstance () : Session
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
     * Inicia a sesssão e retorna o objeto instanciado
     *
     * @return Session
     */
    public static function start () : Session
    {
        // Atalho para obter a instância da classe,
        // o que já inicia a sessão se necessário
        return self::getInstance();
    }

    /**
     * Define uma variável de sessão
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return bool
     */
    public static function set (string $name, $value) : bool
    {
        // Obtém a instância da classe, que 
        // inicializa a sessão e os atributos
        $session = self::getInstance();
        // Define a variável de sessão para o nome informado
        $_SESSION[$session->sessionID][$name] = $value;

        // Retorna o resultado de ter sido atribuído o valor à sessão ou não
        return $_SESSION[$session->sessionID][$name] === $value ? 
            true : false;
    }

    /**
     * Retorna o valor de uma variável de sessão
     * 
     * @param string $name
     * 
     * @return mixed
     */
    public static function get (string $name)
    {
        // Obtém a instância da classe, que 
        // inicializa a sessão e os atributos
        $session = self::getInstance();
        // Verifica se existe o índice com o nome informado
        if (!isset($_SESSION[$session->sessionID][$name])) {
            // Não existe, retorna nulo
            return null;
        }

        // O índice existe, retorna seu valor
        return $_SESSION[$session->sessionID][$name];
    }

    /**
     * Define uma variável flash de sessão (se passado o parâmetro $value)
     * ou retorna o valor armazenado e destrói a variável em seguida
     * 
     * @param string $name
     * @param null|mixed $value
     * 
     * @return mixed
     */
    public static function flash (string $name, $value = null)
    {
        // Obtém a instância da classe, que 
        // inicializa a sessão e os atributos
        $session = self::getInstance();
        // Gera a identificação da variável de flash, 
        // dentro da variável de sessão
        $flashId = $session->sessionID . '/flash';

        // O parâmetro $value é nulo?
        if (is_null($value)) {
            // Sim, então verifica se existe o índice com o nome informado
            if (!isset($_SESSION[$session->sessionID][$flashId][$name])) {
                // O índice não existe ainda, retorna nulo
                return null;
            }

            // O índice existe, então obtém o valor 
            // armazenado na flash do nome informado
            $flashValue = $_SESSION[$session->sessionID][$flashId][$name];
            // Remove a flash com o nome informado
            unset($_SESSION[$session->sessionID][$flashId][$name]);
            // Retorna o valor armazenado
            return $flashValue;
        }

        // Há o atributo $value, então define a variável de sessão para a flash
        $_SESSION[$session->sessionID][$flashId][$name] = $value;

        // Retorna o resultado de ter sido atribuído o valor à sessão ou não
        return $_SESSION[$session->sessionID][$flashId][$name] === $value ? 
            true : false;
    }
}