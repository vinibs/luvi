<?php

namespace App\Model;

use App\Core\Model;
use App\Core\I18n;

class Dog extends Model {
    private $bark;

    public function __construct () {
        parent::__construct();
        $this->bark = I18n::get('bark');
    }

    public function bark ($times) {
        for ($i = 0; $i < $times; $i++) {
            if ($i === 0)
                echo ucfirst($this->bark);
            else
                echo $this->bark;

            if ($i < $times-1)
                echo ', ';
        }
    }
}