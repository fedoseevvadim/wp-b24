<?php

namespace B24;

class Struct
{

	// Translation class Connector
	public static $tr_crmUrl = 'No url crm provided';
	public static $tr_crmLogin = 'No crm login provided';
	public static $tr_crmPassword = 'No crm password provided';
	public static $tr_client_id = 'Client id is not provided';
	public static $tr_clientSecret = 'Client Secret is not provided';



	private static $arrVals = [
		"b24_crm_host"   => "host",
		"b24_crm_port"   => "port",
		"b24_crm_hidden" => "hidden"
	];

	public $arrOptions = [];

	public function __construct ()
	{

		foreach ( self::$arrVals as $key => $value ) {
			$this->arrOptions[$value] = $this->$key;
		}

	}

	public function __get ( $name )
	{
		return get_option ( $name );
	}


	// remove nested array from WP_data
	public function removeNestedArray ( array $array ): array
	{

		foreach ( $array as $key => $item ) {

			if ( is_array ( $array[$key] ) ) {
				$arr[$key] = $array[$key][0];
			} else {
				$arr[$key] = $array[$key];
			}

		}

		return $arr;

	}

}
