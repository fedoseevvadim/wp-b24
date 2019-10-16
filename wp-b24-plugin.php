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



//$B24 = new \B24\B24Connector();

//$B24->addLead();


// Include the main WooCommerce class.
//if ( ! class_exists( 'WooCommerce', false ) ) {
//
//    include_once $_SERVER["DOCUMENT_ROOT"] . "/wp-content/plugins/woocommerce/includes/class-wc-autoloader.php";
//
//
////    include_once $_SERVER["DOCUMENT_ROOT"] . '/wp-content/plugins/woocommerce/includes/class-wc-order-item.php';
////    include_once $_SERVER["DOCUMENT_ROOT"] . '/wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-data.php';
//
//
////    include_once $_SERVER["DOCUMENT_ROOT"] . '/wp-content/plugins/woocommerce/includes/class-wc-order.php';
////    include_once $_SERVER["DOCUMENT_ROOT"] . '/wp-content/plugins/woocommerce/includes/abstracts/abstract-wc-order.php';
//
//    add_action('init', 'my_init', 1);
//
//    function my_init() {
//        $order_id = 2791;
//        $order = new WC_Order($order_id);
//    }
//
//    //my_init();
//
//
//}


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


function b24_toplevel_page() {

    echo "<h2>Битрикс 24</h2>";

    $b24Struct = new B24\B24Struct();
    $b24Form = new B24\B24WPForm();

    $arrOptions = $b24Form->getOptions();
    //$arrOptions = $b24Struct->arrOptions;

    var_dump($arrOptions);


//    $ClassOrder = new \WC\Order;
//    $arrOrder= $ClassOrder->get(2791);

    echo $b24Struct->host;

    ?>

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" accept-charset="utf-8">
        <input type="hidden" name="b24_crm_hidden" value="Y">

        <?php

        echo $b24Form->buildForm();

        ?>

        <p class="submit">
            <input type="submit" name="Submit" value="Сохранить" />
        </p>
    </form>
<?
}
?>

