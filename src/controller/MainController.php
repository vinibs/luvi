<?php

namespace App\Controller;

use App\Core\View;
use App\Core\Session;

class MainController {
    public function index ($params) {
        View::load('page/default');
    }

    public function param ($params) {
        echo 'Tem parâmetros!<br><pre>';
        var_dump($params);
        echo '</pre>';
    }

    public function edit ($params) {
        echo 'Editar!<br><pre>';
        var_dump($params);
        echo '</pre>';
    }

    public function dogBark ($params) {
        echo 'O cãozinho vai latir: <br><br>';
        $dog = new \App\Model\Dog();
        $dog->bark(4);
    }
}