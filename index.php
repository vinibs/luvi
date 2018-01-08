<?php
date_default_timezone_set('America/Sao_Paulo');
define('INITIALIZED', TRUE); // Allow to access the other files via require/include

require_once 'app/SystemData.php';

require_once 'app/system/core/Router.php';
$router = new Router();