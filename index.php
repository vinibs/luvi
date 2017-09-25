<?php
date_default_timezone_set('America/Sao_Paulo');
define('INITIALIZED', TRUE); // Allow to access the other files via require/include

require_once 'app/SystemData.php';

// If set to true, redirect to the same location using HTTPS before load the page
if(REDIR_HTTPS){
    if(json_decode($_SERVER['HTTP_CF_VISITOR'])->scheme != 'https'){
        header('Location: https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        exit;
    }
}

require_once 'app/system/core/RequestRouter.php';
$request = new RequestRouter();