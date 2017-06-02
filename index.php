<?php 
define('INITIALIZED', TRUE); // Allow to access the other files via require/include

require_once 'app/SystemData.php';
require_once 'app/system/core/RequestRouter.php';

$request = new RequestRouter();