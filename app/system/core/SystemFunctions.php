<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/* System Functions */

function dump ($object) {
	echo '<pre>';
	var_dump($object);
	echo '</pre>';
}

function dd ($object) {
	dump($object);
	die;
}

/**
 * TODAS AS FUNÇÕES ABAIXO SÃO FUNÇÕES ESSENCIAIS PARA O FUNCIONAMENTO DO SISTEMA OU ALIAS DELAS
 */

function filterPost () {
	return filter_var_array($_POST, FILTER_SANITIZE_STRING);
}

function filterPut () {
	return filter_var_array($_PUT, FILTER_SANITIZE_STRING);
}

function filterGet () {
	return filter_var_array($_GET, FILTER_SANITIZE_STRING);
}

function redirect ($url = '', $status = 200) {
	return App::redirect($url, $status);
}

function back () {
	return App::back();
}

// Funções referentes ao uso de sessão, cookie e flash session
function session ($name, $data = null) {
	return (new App)->session($name, $data);
}

function cookie ($name, $data = null) {
	return (new App)->cookie($name, $data);
}

function unsetcookie ($name) {
	return (new App)->unsetcookie($name);
}

function flash ($name, $data = null) {
	return (new App)->flash($name, $data);
}

// Função para preencher o campo de formulário com o último valor inserido (usando flash)
function oldVal ($fieldName) {
	return (new App)->oldVal($fieldName);
}

// Exibe a mensagem de erro quando retornado da função de validação
function getInputErrors ($fieldName) {
	return (new App)->getInputErrors($fieldName);
}


// Recebe o array de arquivos vindo do formulário e reordena os elementos
function orderFiles (array $files) {
	return (new App)->orderFiles($files);
}

function load ($filePath) {
	return (new App)->load($filePath);
}

function view ($viewName, $data = null) {
	return (new App)->view($viewName, $data);
}

function model ($modelName) {
	return (new App)->model($modelName);
}