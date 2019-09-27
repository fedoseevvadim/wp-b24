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

    function activate() {
        echo "Activated";
    }

    function deactivate () {

    }

    function uninstall () {

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

add_action('admin_menu', 'b24_add_page');
add_action('woocommerce_order_status_completed', 'b24_createLeadWhenCompleted'); // Хук статуса заказа = "Выполнен"

$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );

if ( $http_post === true ) {

    //$hidden = $_POST["b24_crm_hidden"];

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

}


function b24_toplevel_page() {

    echo "<h2>Битрикс 24</h2>";

    //$test = B24\B24Struct::$arrVals;

    $b24Struct = new B24\B24Struct();
    $arrOptions = $b24Struct->arrOptions;

    ?>

    <form name="form1" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>" accept-charset="utf-8">
        <input type="hidden" name="<?=$arrOptions["hidden"]?>" value="Y">

        <p>Хост системы
            <input type="text" name="<?=$arrOptions["host"]?>"
                   value="<?=$arrOptions["host"]?>" size="64" required placeholder="yourdomain.bitrix24.ru">
        </p>

        <p class="submit">
            <input type="submit" name="Submit" value="Подтвердить" />
        </p>
    </form>
<?
}
?>

