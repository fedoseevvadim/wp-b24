<?php

namespace B24;

class WPForm {

    const path = "/wp-content/plugins/b24-plugin/tpl/";

    // Settings
    private static $arrForm = [

        ["Хост системы",            "host",         "text",     "yourdomain.bitrix24.ru"],
        ["Логин пользователя",      "login",        "text",     "User login"],
        ["Пароль пользователя",     "password",     "text",     "User password"],
        ["Название сделки",         "deal_name",    "text",     "Deal name"],
        ["Client ID",               "client_id",    "text",     "Client ID"],
        ["Client Secret Key",       "client_secret","text",     "Client Secret Key"],
        ["Наименование источника",  "source_name",  "text",     "название сайта (например)"],
        ["Связь по полям",          "field_link",   "textarea", "поле битрикс []->[] "],
        ["Направление сделок",      "lead_terms",   "textarea", "Связь по направлениям сделок [Категория товара]->[ID Направления в Б24]"],
        ["Поля Контакта",           "contact",      "textarea", "Связь по полям у контакта [ID B24]->[Значение]"]
    ];

    /*
    * get template
    */
    private function getTpl ($type) {

        switch ( $type ) {

            case "text":

                $tpl_input = file_get_contents($_SERVER["DOCUMENT_ROOT"].self::path."input.html");

                break;

            case "textarea":

                $tpl_input = file_get_contents($_SERVER["DOCUMENT_ROOT"].self::path."textarea.html");

                break;
        }



        return $tpl_input;
    }


    /*
    * replace values in template form
    */
    public function buildForm ( array $arrOptions ) {

        $form = "";

        foreach (self::$arrForm as $key => $item ) {

            if ( $item[2] === "text" ) {

                    $tpl = $this->getTpl("text");

                    $tpl = str_replace("[NAME]",            $item[0],                   $tpl);
                    $tpl = str_replace("[VAR_NAME]",        $item[1],                   $tpl);
                    $tpl = str_replace("[VALUE]",           $arrOptions[$item[1]],      $tpl);
                    $tpl = str_replace("[PLACE_HOLDER]",    $item[3],                   $tpl);
                    $tpl = str_replace("[TYPE]",            $item[2],                   $tpl);

                    $form .= $tpl;

            }

            if ( $item[2] === "textarea" ) {

                $tpl = $this->getTpl("textarea");

                $tpl = str_replace("[NAME]",            $item[0],                   $tpl);
                $tpl = str_replace("[VAR_NAME]",        $item[1],                   $tpl);
                $tpl = str_replace("[VALUE]",           $arrOptions[$item[1]],      $tpl);

                $form .= $tpl;
            }
        }

        return $form;

    }

    /*
    * get all options
    */
    public function getOptions ():array {

        $array = [];

        $arrElements = array_column(self::$arrForm, 1);

        foreach ( $arrElements as $elem ) {
            $array[$elem] = get_option($elem);
        }

        return $array;

    }

}