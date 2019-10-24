<?php

/**
 * @package b24 plugin
 */

/*

Plugin Name: b24 plugin
Plugin URI: https://1cbit.ru

Description: This plugin connects B24 and orders from website
VersionL 1.0.0
Author: 1cBit
Author URI: https://f-vv.ru
License: GPLv2 or later
Text Domain: b24-plugin
*/

//use B24;
//use Page;

use B24\B24WPForm;
use Page\SetupPage;

defined( 'ABSPATH' ) || exit;

class B24Plugin {

    function __construct() {

        add_action('admin_menu', 'b24_add_page');
        add_action('woocommerce_order_status_completed', 'b24_createLeadWhenCompleted'); // Хук статуса заказа = "Выполнен"

    }

    function activate() {

    }

    function deactivate () {

    }

    function uninstall () {

    }

    function custom_post_type () {

        //register_post_type('b24', ['public' => 'true', 'label' => 'Books'] );

    }

}

if ( class_exists('B24Plugin' ) ) {
    $b24Plugin = new B24Plugin();
}

// activation
register_activation_hook( __FILE__, [$b24Plugin, 'activate'] );

// deactivation
register_deactivation_hook( __FILE__, [$b24Plugin, 'deactivate']);


\spl_autoload_register(
    function ($className)
    {
        $file = __DIR__ . '/classes/' . \str_replace('\\', '/', $className) . '.php';

        if (file_exists($file))
            require_once $file;
    }
);


$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );

if ( $http_post === true ) {

    if (isset($_POST["b24_crm_hidden"]) && $_POST["b24_crm_hidden"] === 'Y'){

        foreach ($_POST as $key => $value) {
            update_option($key, $value);
        }

    }

}

function b24_add_page() {
    add_menu_page('Bitrix24', 'Bitrix24', 'manage_options', __FILE__, 'b24_toplevel_page');
}

/**
 * @param mixed  $order_id
 * @param string $debug
 *
 * @return void
 */
function b24_createLeadWhenCompleted ($order_id, $debug = '') {

    if ( $order_id === 1 ) {


        $ORDER = \WC\Order::get($order_id);
        $arrPOST_ORDER = get_post_meta($order_id);
        $arrORDER_TERMS = get_post_meta($ORDER['order_item_id']);

        $arrPOST_TERMS = wp_get_object_terms($ORDER['order_item_id'], 'product_cat');

        // Если выбран тип мероприятия - должна создаваться Сделка + Контакт в воронке Живые Мероприятия.
        // Если выбран Тип цифрового продукта, должна создаваться Сделка + Контакт в воронке "Цифровые продукты".

        $CategoryId = 0;

        if ($arrPOST_TERMS[0]->term_id === 16) { // Живые мероприятия
            $CategoryId = 0;
        }

        if ($arrPOST_TERMS[0]->term_id === 42) { // Цифровые продукты
            $CategoryId = 1;
        }

        if (is_array($arrPOST_ORDER) AND is_array($ORDER)) {

            $b24Form = new B24WPForm();

            $id = $arrPOST_ORDER["_customer_user"][0];
            $arrUser = get_user_meta($id);

            $arrOptions = $b24Form->getOptions();

            $B24 = new \B24\B24Connector (
                $arrOptions["host"],
                $arrOptions["login"],
                $arrOptions["password"],
                $arrOptions["client_id"],
                $arrOptions["client_secret"]
            );

            // Before we start, let's check connection to server
            $bCheckConnection = $B24->checkConnection($arrOptions["host"]);

            if ($B24->accessToken) {
                $contactID = $B24->addContact($arrUser);
            }

            if ($contactID > 0 AND $bCheckConnection === true) {

                // Standard fileds
                $arrData["CATEGORY_ID"] = $CategoryId;
                $arrData["CONTACT_ID"] = $contactID;
                //$arrData["ACCOUNT_CURRENCY_ID"] =

                if ($arrOptions["field_link"]) {

                    $arrayOfLines = explode("\r\n", $arrOptions["field_link"]);

                    if (is_array($arrayOfLines)) {

                        foreach ($arrayOfLines as $value) {

                            $arrElem = explode("=>", $value);

                            // OPPORTUNITY=>_price
                            if (count($arrElem) === 2) {
                                $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[1]][0];

                                switch ($arrElem[1]) {
                                    // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
                                    case "TERMS":

                                        $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];

                                        break;

                                    default:

                                        $arrData[$arrElem[0]] = $arrElem[1];
                                        break;
                                }
                            }

                            if (count($arrElem) === 3) {

                                switch ($arrElem[1]) {

                                    // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
                                    case "TERMS":

                                        $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];

                                        break;

                                    // UF_CRM_1569421314=>ORDER=>order_item_name
                                    case "ORDER":

                                        $itemID = $arrElem[2];
                                        $value = $ORDER[0]->$itemID;
                                        $arrData[$arrElem[0]] = $value;

                                        break;

                                    case "POST_ORDER":

                                        $itemID = $arrElem[2];
                                        $value = $arrPOST_ORDER[$itemID][0];
                                        $arrData[$arrElem[0]] = $value;

                                        break;

                                }
                            }
                        }
                    }
                }

                $B24->addDeal($arrData);

                var_dump($arrData);
            }

        }

    }

}



function b24_toplevel_page () {

    $setupPage = new Page\SetupPage();



//    $ORDER = \WC\Order::get( 3867 );
//    $arrPOST_ORDER   = get_post_meta(3867);
//    $arrORDER_TERMS  = get_post_meta(2798);
//
//    $arrPOST_TERMS = wp_get_object_terms(2798, 'product_cat');
//
//    // Если выбран тип мероприятия - должна создаваться Сделка + Контакт в воронке Живые Мероприятия.
//    // Если выбран Тип цифрового продукта, должна создаваться Сделка + Контакт в воронке "Цифровые продукты".
//
//    $CategoryId = 0;
//
//    if ( $arrPOST_TERMS[0]->term_id === 16 ) { // Живые мероприятия
//        $CategoryId = 0;
//    }
//
//    if ( $arrPOST_TERMS[0]->term_id === 42 ) { // Цифровые продукты
//        $CategoryId = 1;
//    }
//
//    if ( is_array( $arrPOST_ORDER ) AND is_array( $ORDER )) {
//
//        $b24Form = new B24WPForm();
//
//        $id = $arrPOST_ORDER["_customer_user"][0];
//        $arrUser =  get_user_meta($id);
//
//        $arrOptions = $b24Form->getOptions();
//
//        $B24 = new \B24\B24Connector (
//                                        $arrOptions["host"],
//                                        $arrOptions["login"],
//                                        $arrOptions["password"],
//                                        $arrOptions["client_id"],
//                                        $arrOptions["client_secret"]
//        );
//
//        $bCheckConnection = $B24->checkConnection( $arrOptions["host"] );
//
//        if ( $B24->accessToken ) {
//            $contactID =  $B24->addContact($arrUser);
//        }
//
//        if ( $contactID > 0 AND $bCheckConnection === true ) {
//
//            // Standard fileds
//            $arrData["CATEGORY_ID"]         = $CategoryId;
//            $arrData["CONTACT_ID"]          = $contactID;
//            //$arrData["ACCOUNT_CURRENCY_ID"] =
//
//            if ( $arrOptions["field_link"] ) {
//
//                $arrayOfLines = explode("\r\n", $arrOptions["field_link"] );
//
//                if ( is_array($arrayOfLines) ) {
//
//                    foreach ( $arrayOfLines as $value ) {
//
//                        $arrElem = explode("=>", $value );
//
//                        // OPPORTUNITY=>_price
//                        if ( count($arrElem) === 2 ) {
//                            $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[1]][0];
//
//                            switch ($arrElem[1]) {
//                                // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
//                                case "TERMS":
//
//                                    $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];
//
//                                    break;
//
//                                default:
//
//                                    $arrData[$arrElem[0]] = $arrElem[1];
//                                    break;
//                            }
//                        }
//
//                        if ( count($arrElem) === 3 ) {
//
//                            switch ($arrElem[1]) {
//
//                                // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
//                                case "TERMS":
//
//                                    $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];
//
//                                    break;
//
//                                // UF_CRM_1569421314=>ORDER=>order_item_name
//                                case "ORDER":
//
//                                    $itemID = $arrElem[2];
//                                    $value = $ORDER[0]->$itemID;
//                                    $arrData[$arrElem[0]] = $value;
//
//                                    break;
//
//                                case "POST_ORDER":
//
//                                    $itemID = $arrElem[2];
//                                    $value = $arrPOST_ORDER[$itemID][0];
//                                    $arrData[$arrElem[0]] = $value;
//
//                                    break;
//
//                            }
//                        }
//                    }
//                }
//            }
//
//            $B24->addDeal($arrData);
//
//
//
//
//            var_dump($arrData);
//        }




//            echo "Пользовательские поля в сделке (Битрикс 24):<br>";
//
//            foreach ( $arrFields as $key => $value ) {
//                echo $value["FIELD_NAME"] . "<br>";
//            }
//    }

}



?>

