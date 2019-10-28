<?php

namespace B24;

class Struct {

    private static $arrVals = [
        "b24_crm_host" => "host",
        "b24_crm_port" => "port",
        "b24_crm_hidden" => "hidden"
    ];

    public $arrOptions = [];

    public function __construct() {

        foreach ( self::$arrVals as $key => $value ) {
            $this->arrOptions[$value]  = $this->$key;
        }

    }

    public function __get($name) {
        return get_option($name);
    }

}
