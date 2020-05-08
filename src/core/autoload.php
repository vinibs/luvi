<?php

/**
 * Autoloader de configurações (em /config) e classes (em /src)
 * 
 * @author Vinicius Baroni Soares <hiviniciusbs@gmail.com>
 * @copyright 2020
 */
spl_autoload_register(NULL, FALSE);

// Função para definir a constante de prefixo do namespace
function setNamespacePrefix (?string $configNamePrefix) : void
{
    // Carrega o namespace base com base na configuração (se houver)
    $namespacePrefix = null;
    if (!is_null($configNamePrefix)) {
        $namespacePrefix = $configNamePrefix;

        // Adiciona a '\' se precisar
        if (substr($configNamePrefix, -1) !== '\\') {
            $namespacePrefix .= '\\';
        }
    }
    else {
        $namespacePrefix = 'App\\';
    }
    
    // Define a constante com o prefixo do namespace
    define('namespacePrefix', $namespacePrefix);
}


// Inclui todos os arquivos JSON na raiz de '/config'
foreach (glob(__DIR__ . '/../../config/*.json') as $file) {
    // Obtém a posição na string onde começa o nome do arquivo
    $fileNameStart = strrpos($file, '/') + 1;
    // Obtém o nome do arquivo sendo lido
    $fileName = substr($file, $fileNameStart);

    // Separa o nome do arquivo nos pontos
    $fileNameParts = explode('.', $fileName); 
    
    // Define que o nome da variável será o primeiro 
    // bloco do nome do arquivo
    $varName = $fileNameParts[0] . 'Config';

    // Obtém o conteúdo do arquivo
    $content = json_decode(file_get_contents($file));

    // Define a constante com o valor do arquivo
    $$varName = $content;
}

// Inclui todos os arquivos PHP na raiz de '/config'
foreach (glob(__DIR__ . '/../../config/*.php') as $file) {
    include_once $file;
}

// Depois de incluir os arquivos de configuração, chama a
// função para definir a constante de prefixo do namespace
setNamespacePrefix($appConfig->namespacePrefix ?? null);

// Carrega automaticamente todas as classes dentro de '/src'
spl_autoload_register(function (string $fullClass) : void
{
    // Obtém o diretório atual
    $baseDir = __DIR__ . '/../';

    // A classe usa o prefixo no namespace?
    $len = strlen(namespacePrefix);
    if (strncmp(namespacePrefix, $fullClass, $len) !== 0) {
        // Não, então segue para o próximo autoloader registrado
        return;
    }

    // Obtém as partes do caminho resultante para padronizar
    $class_parts = explode('\\', $fullClass);
    $standarized_class = '';

    // Remove as iniciais maiúsculas do caminho até a classe
    foreach ($class_parts as $index => $part) {
        // Se for o último item da lista (o nome da classe),
        // inclui diretamente o texto na string padronizada
        if ($index === count($class_parts) - 1) {
            $standarized_class .= $part;
        }
        // Se for parte do caminho, retira as maiúsculas
        else {
            $standarized_class .= strtolower($part) . '/';
        }
    }

    // Substitui o separador de namespace '\' por barra
    $className = str_replace('\\', '/', substr($fullClass, $len));

    
    $classFile = $baseDir . $className;

    // Ajusta para buscar os arquivos .php e .class.php
    $classExtensions = ['.php', '.class.php'];

    // Verifica em cada formato/extensão de arquivo para importar
    foreach ($classExtensions as $extension) {
        // Se a classe existir, faz um include
        if (file_exists($classFile . $extension)) {
            include_once $classFile . $extension;
            break;
        }
    }
});

// Tenta carregar as classes no diretório "/vendor" (se existir)
spl_autoload_register(function ($vendorClass) : void {
    // Caminho do diretório base
    $vendorPath = __DIR__ . '/../../vendor/';
    // Caminho do arquivo, trocando "\" por "/"
    $filePath = $vendorPath . str_replace('\\', '/', $vendorClass) . '.php';

    // O caminho aponta para um arquivo válido?
    if (file_exists($filePath)) {
        // Sim, inclui
        include_once $filePath;
    }
});