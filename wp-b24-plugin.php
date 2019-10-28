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

use B24\WPForm;
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

    if ( $order_id > 0 ) {

        $b24Form    = new WPForm();

        $arrOptions = $b24Form->getOptions();

        $B24 = new \B24\Connector (
            $arrOptions["host"],
            $arrOptions["login"],
            $arrOptions["password"],
            $arrOptions["client_id"],
            $arrOptions["client_secret"]
        );

        $b24Deal    = new \B24\Lead($B24);
        $b24Contact = new \B24\Contact($B24);


        $ORDER          = \WC\Order::get($order_id); // get order data
        $arrPOST_ORDER  = get_post_meta( $order_id );

        // Go through every order item
        if ( is_array( $ORDER ) ) {

            $i = 0;

            foreach ( $ORDER as $orderItem ) {


                $item               = new WC_Order_Item_Product($orderItem->order_item_id);
                $product_id         = $item->get_product_id();
                $B24Terms           = new \B24\Terms( $arrOptions["lead_terms"] );
                $arrORDER_TERMS     = get_post_meta($product_id);
                $arrPOST_TERMS      = wp_get_object_terms($product_id, 'product_cat');

                $categoryId = $B24Terms->getTerm( $arrPOST_TERMS[$i]->term_id );

                if (is_array($arrPOST_ORDER) AND is_array($ORDER)) {

                    $id = $arrPOST_ORDER["_customer_user"][0];
                    $arrUser = get_user_meta($id);

                    // Before we start, let's check connection to server
                    $bCheckConnection = $B24->checkConnection($arrOptions["host"]);

                    if ($B24->accessToken) {
                        $contactID = $b24Contact->set( $arrUser );
                    }

                    if ($contactID > 0 AND $bCheckConnection === true) {

                        // Standard fileds
                        $arrData["CATEGORY_ID"] = $categoryId;
                        $arrData["CONTACT_ID"]  = $contactID;
                        $arrData["TITLE"]       = $arrOptions["deal_name"] . " #" . $ORDER[0]->order_id;

                        $paymentMethod = strtolower($arrPOST_ORDER["_payment_method"][0]);

                        $parser = new \Parser\Settings ();
                        $arrData = $parser->parseSettings(
                            $arrOptions["field_link"],
                            $arrData,
                            $ORDER,
                            $arrORDER_TERMS,
                            $arrPOST_ORDER
                        );

                        // Search payment method
                        // UF_CRM_1569421090=>РК: Альфа Банк
                        $arrUserFields = $b24Deal->getDealUserFileds();
                        $paymentKeyVal = \B24\Data::getPaymentID( $paymentMethod, $arrUserFields );

                        if ( is_array($paymentKeyVal) ) {
                            $arrData[$paymentKeyVal[0]] = (int) $paymentKeyVal[1];
                        }

                        $b24Deal->set($arrData);

                    }

                }


                $i++;

            }

        }

    }

}



function b24_toplevel_page () {

    $setupPage = new Page\SetupPage();

}



?>

