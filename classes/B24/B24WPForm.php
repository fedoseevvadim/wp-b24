<?php

namespace B24;

class B24WPForm {

    const path = "/wp-content/plugins/b24-plugin/tpl/";

    private static $arrForm = [

        ["Хост системы",        "host",         "text", "yourdomain.bitrix24.ru"],
        ["Логин пользователя",  "login",        "text", "User login"],
        ["Пароль пользователя", "password",     "text", "User password"],
        ["Client ID",           "client_id",    "text", "Client ID"],
        ["Client Secret Key",   "client_secret","text", "Client Secret Key"],

    ];

    /*
    *
    */
    private function getTpl () {

        $tpl_input = file_get_contents($_SERVER["DOCUMENT_ROOT"].self::path."input.html");

        return $tpl_input;
    }



    public function buildForm () {

        $form = "";

        foreach (self::$arrForm as $key => $item ) {

            if ( $item[2] === "text" ) {

                    $tpl = $this->getTpl();

                    $tpl = str_replace("[NAME]",            $item[0],   $tpl);
                    $tpl = str_replace("[VAR_NAME]",        $item[1],   $tpl);
                    $tpl = str_replace("[VALUE]",           "",   $tpl);
                    $tpl = str_replace("[PLACE_HOLDER]",    $item[3],   $tpl);
                    $tpl = str_replace("[TYPE]",            $item[2],   $tpl);

                    $form .= $tpl;

            }
        }

        return $form;

    }

    public function getOptions ():array {

        $array = [];

        $arrElements = array_column(self::$arrForm, 1);

        foreach ( $arrElements as $elem ) {
            $array[$elem] = get_option($elem);
        }

        return $array;

    }

}