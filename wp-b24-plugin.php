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

defined ( 'ABSPATH' ) || exit;


class B24Plugin
{

	function __construct ()
	{

		add_action('admin_menu', function()  {
			global  $funcAddPage;

			add_menu_page ( 'Bitrix24', 'Bitrix24', 'manage_options', __FILE__, 'b24_toplevel_page' );
		});

		add_action ( 'woocommerce_order_status_completed', 'b24_createLeadWhenCompleted' ); // Хук статуса заказа = "Выполнен"

		add_action ( 'wpcf7_submit', 'wpcf7_submit', 10, 2 );

		//add_action ( "wpcf7_before_send_mail", "wpcf7_do_something_else" );

		add_action ( 'user_register', 'add_contact_b24', 10, 1 );

	}

	function activate ()
	{

	}

	function deactivate ()
	{

	}

	function uninstall ()
	{

	}

	function custom_post_type ()
	{

		//register_post_type('b24', ['public' => 'true', 'label' => 'Books'] );

	}

}

if ( class_exists ( 'B24Plugin' ) ) {
	$b24Plugin = new B24Plugin();
}

// activation
register_activation_hook ( __FILE__, [ $b24Plugin, 'activate' ] );

// deactivation
register_deactivation_hook ( __FILE__, [ $b24Plugin, 'deactivate' ] );


\spl_autoload_register (
	function ( $className ) {
		$file = __DIR__ . '/classes/' . \str_replace ( '\\', '/', $className ) . '.php';

		if ( file_exists ( $file ) )
			require_once $file;
	}
);


// Save options
$http_post = ( 'POST' == $_SERVER['REQUEST_METHOD'] );

if ( $http_post === true ) {

	if ( isset( $_POST["b24_crm_hidden"] ) && $_POST["b24_crm_hidden"] === 'Y' ) {

		foreach ( $_POST as $key => $value ) {
			update_option ( $key, $value );
		}

	}

}




//${$funcAddPage} = function()  {
//
//	add_menu_page ( 'Bitrix24', 'Bitrix24', 'manage_options', __FILE__, 'b24_toplevel_page_ru' );
//
//};
//
//if(function_exists($funcAddPage)) {
//	call_user_func($funcAddPage);
//}


//var_dump (${$funcAddPage});

//function b24_add_page_ru ()
//{
//	add_menu_page ( 'Bitrix24', 'Bitrix24', 'manage_options', __FILE__, 'b24_toplevel_page_ru' );
//}

/**
 * @param mixed  $order_id
 * @param string $debug
 *
 * @return void
 */
function b24_createLeadWhenCompleted ( $order_id, $debug = '' )
{

	if ( $order_id > 0 ) {

		$b24Form = new WPForm();

		$arrOptions = $b24Form->getOptions ();

		$B24 = new \B24\Connector (
			$arrOptions["host" . WPForm::PREFIX],
			$arrOptions["login" . WPForm::PREFIX],
			$arrOptions["password" . WPForm::PREFIX],
			$arrOptions["client_id" . WPForm::PREFIX],
			$arrOptions["client_secret" . WPForm::PREFIX]
		);

		$b24Deal = new \B24\Deal( $B24 );
		$b24Contact = new \B24\Contact( $B24 );
		$parser = new \Parser\Settings ();

		$ORDER = \WC\Order::get ( $order_id ); // get order data
		$arrPOST_ORDER = get_post_meta ( $order_id );

		// Go through every order item
		if ( is_array ( $ORDER ) ) {

			$iTitle = 1;
			$i = 0;

			foreach ( $ORDER as $orderItem ) {

				$item = new WC_Order_Item_Product( $orderItem->order_item_id );
				$product_id = $item->get_product_id ();
				$B24Terms = new \B24\Terms( $arrOptions["lead_terms" . WPForm::PREFIX ] );
				$arrORDER_TERMS = get_post_meta ( $product_id );
				$arrPOST_TERMS = wp_get_object_terms ( $product_id, 'product_cat' );

				$arrPOST_ORDER["_quantity"][0] = $item->get_quantity ();
				$arrPOST_ORDER["_total"][0] = $item->get_subtotal ();
				$arrPOST_ORDER["_comments"][0] = \WC\Order::rsp_get_wc_order_notes ( $order_id );

				$categoryId = $B24Terms->getTerm ( $arrPOST_TERMS[0]->term_id );

				if ( is_array ( $arrPOST_ORDER ) AND is_array ( $ORDER ) ) {

					$id = $arrPOST_ORDER["_customer_user"][0];
					$arrUser = get_user_meta ( $id );

					// Before we start, let's check connection to server
					$bCheckConnection = $B24->checkConnection ( $arrOptions["host" . WPForm::PREFIX ] );
					$parser->setOrder ( $ORDER[$i] );
					$parser->setPostOrder ( $arrPOST_ORDER );
					$parser->setTerms ( $arrORDER_TERMS );

					if ( $B24->accessToken ) {
						$arrUser["contact"] = $arrOptions["contact" . WPForm::PREFIX];
						$arrUser["typem"] = $arrPOST_ORDER["typem"][0];

						$contactID = $b24Contact->set ( $arrUser );
					}

					if ( $contactID > 0 AND $bCheckConnection === true ) {

						if ( count ( $ORDER ) > 1 ) {
							$title = "/" . $iTitle;
						} else {
							$title = "";
						}

						// Standard fields
						$arrData["CATEGORY_ID"] = $categoryId;
						$arrData["CONTACT_ID"] = $contactID;
						$arrData["TITLE"] = $arrOptions["deal_name" . WPForm::PREFIX] . " #" . $ORDER[$i]->order_id . $title;

						$paymentMethod = strtolower ( $arrPOST_ORDER["_payment_method_title"][0] );

						$arrData = $parser->parseFields (
							$arrOptions["field_link" . WPForm::PREFIX ],
							$arrData
						);

						$arrUserFields = $b24Deal->getDealUserFileds ();
						$params = [];
						$params = $b24Deal->map ( $arrUserFields, $arrData, $params );

						// Search payment method
						// UF_CRM_1569421090=>РК: Альфа Банк
						$paymentKeyVal = \B24\Data::getPaymentID ( $paymentMethod, $arrUserFields );

						if ( is_array ( $paymentKeyVal ) ) {
							$params["fields"][$paymentKeyVal[0]] = (int)$paymentKeyVal[1];
						}

						$b24Deal->set ( $params );

					}

				}

				$i++;
				$iTitle++;

			}

		}

	}

}

//function wpcf7_do_something_else ( $cf7 )
//{
//
//	$submission = WPCF7_Submission::get_instance ();
//
//	if ( $submission ) {
//		$posted_data = $submission->get_posted_data ();
//		var_dump ( $posted_data );
//	}
//}


function b24_toplevel_page ()
{

	$setupPage = new Page\SetupPage();

}

function array_search_partial ( array $arr, string $keyword ): int
{
	$index = 0;

	foreach ( $arr as $index => $string ) {

		if ( strpos ( $string, $keyword ) !== false )
			break;
	}

	return $index;

}



function add_contact_b24 ( $user_id )
{

	if ( $user_id ) {

		$b24Form = new WPForm();

		$arrOptions = $b24Form->getOptions ();

		if ( $arrOptions["reg_user" . WPForm::PREFIX ] === 1 ) {

			$B24 = new \B24\Connector (
				$arrOptions["host" . WPForm::PREFIX ],
				$arrOptions["login" . WPForm::PREFIX ],
				$arrOptions["password" . WPForm::PREFIX ],
				$arrOptions["client_id" . WPForm::PREFIX ],
				$arrOptions["client_secret" . WPForm::PREFIX ]
			);

			$b24Contact = new \B24\Contact( $B24 );

			$arrUser = get_user_meta ( $user_id );

			if ( $B24->accessToken ) {

				$contactID = $b24Contact->set ( $arrUser );

			}
		}

	}

}

// Connect all forms to b24
function wpcf7_submit ( $result )
{

	$prefix = '_wpcf7';
	//$status     = 'mail_sent';
	$arrData = [];

	//if ( $result["status"] === $status ) {

	if ( isset ( $GLOBALS["_POST"] ) ) {

		$arrPost = (array)$GLOBALS["_POST"];
		$arrKeys = array_keys ( $arrPost );

		$searchPrefix = array_search ( $prefix, $arrKeys );

		if ( $searchPrefix !== false ) {

			// try to find in post results from submitted form
			$keyName = array_search_partial ( $arrKeys, "name" );
			$keyEmail = array_search_partial ( $arrKeys, "email" );
			$keyMenu = array_search_partial ( $arrKeys, "menu" );
			$keyChk = array_search_partial ( $arrKeys, "checkbox" );

			$b24Form = new WPForm();
			$arrOptions = $b24Form->getOptions ();

			$B24 = new \B24\Connector (
				$arrOptions["host" . WPForm::PREFIX ],
				$arrOptions["login" . WPForm::PREFIX],
				$arrOptions["password" . WPForm::PREFIX],
				$arrOptions["client_id" . WPForm::PREFIX ],
				$arrOptions["client_secret" . WPForm::PREFIX ]
			);

			$b24Contact = new \B24\Contact( $B24 );

			// map fields
			if ( $keyName !== false ) {
				$arrData["first_name"][0] = $arrPost[$arrKeys[$keyName]];
			}

			if ( $keyEmail !== false ) {
				$arrData['billing_email'][0] = $arrPost[$arrKeys[$keyEmail]];
			}

			if ( $keyMenu !== false ) {

				$arrData["typem"][0] = "";

				if ( $arrPost[$arrKeys[$keyMenu]] === B24\Contact::$arrGenderRU[0] ) {
					$arrData["typem"][0] = "men";
				}

			}

			$arrData["contact"] = $arrOptions["contact_create" . WPForm::PREFIX ];

			if ( $keyChk !== false ) {

				$arrCheckBox = $arrPost[$arrKeys[$keyChk]];

				$arrData["checkbox"][0] = $arrCheckBox;

			}

			$contactID = $b24Contact->set ( $arrData );

		}

	}

	//}
}