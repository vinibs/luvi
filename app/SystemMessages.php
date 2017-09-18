<?php defined('INITIALIZED') OR exit('You cannot access this file directly');

/* System Messages */


// Validation messages
define('msgValidationRequired', '"%field%" deve ser preenchido');
define('msgValidationInt', '"%field%" deve conter um número inteiro');
define('msgValidationDecimal', '"%field%" deve conter um número decimal');
define('msgValidationAlpha', '"%field%" deve conter apenas letras');
define('msgValidationAlphanum', '"%field%" deve conter apenas letras e números');
define('msgValidationEmail', '"%field%" deve conter um endereço de e-mail válido');
define('msgValidationMin', '"%field%" deve conter ao menos %quant% caracteres');
define('msgValidationMax', '"%field%" deve conter no máximo %quant% caracteres');
define('msgValidationEqual', '"%field%" deve ser igual ao campo "%fieldEq%"');


// Auth messages
define('msgAuthNoUsername', 'Esse nome de usuário não existe');
define('msgAuthIncorrectPass', 'A senha está incorreta');
define('msgAuthUsernameUnavailable', 'Esse nome de usuário não está disponível');
