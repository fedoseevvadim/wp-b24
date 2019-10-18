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

        // Include the main WooCommerce class.
//        if ( ! class_exists( 'WooCommerce', false ) ) {
//            include_once dirname( __FILE__ ) . '/includes/class-woocommerce.php';
//        }

        //add_action( 'init', array( $this, 'custom_post_type'));



        //$B24 = new \B24\B24Connector();

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

    var_dump($order_id);

    //$order = wc_get_order( $order_id );
//
//    $B24 = new \B24\B24Connector();

    //$B24->addLead();
}



function b24_toplevel_page () {

    $setupPage = new Page\SetupPage();

    $ORDER = \WC\Order::get( 2791 );
    //$arrPost = get_post(2777);
    $arrPost = get_post_meta(2777);

//    var_dump($arrPost);


    if ( is_array($arrPost)) {

        $b24Form = new B24WPForm();

        $id = $arrPost["_customer_user"][0];
        $arrUser =  get_user_meta($id);

        $arrOptions = $b24Form->getOptions();

        $B24 = new \B24\B24Connector (
                                        $arrOptions["host"],
                                        $arrOptions["login"],
                                        $arrOptions["password"],
                                        $arrOptions["client_id"],
                                        $arrOptions["client_secret"]
        );


        if ( $B24->accessToken ) {
            $contactID =  $B24->addContact($arrUser);
        }

        if ( $contactID > 0 ) {

            $arrData["CONTACT_ID"] = $contactID;
            $B24->addDeal($arrPost);
        }

    }



    //var_dump($ORDER);

}



?>

