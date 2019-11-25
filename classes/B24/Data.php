<?php

namespace B24;

class Data
{

	/**
	 * getPaymentID - добавляет контакт в базу
	 *
	 * @param string $paymentMethod
	 * @param array  $arrUserFields
	 *
	 * @return $response
	 */
	function getPaymentID ( $paymentMethod, $arrUserFields )
	{

		$ID = 0;

		foreach ( $arrUserFields as $field ) {

			if ( $field["USER_TYPE_ID"] === "enumeration" ) {

				foreach ( $field["LIST"] as $list ) {

					if ( $list["VALUE"] ) {

						$value = strtolower ( $list["VALUE"] );
						$pos = strpos ( $value, $paymentMethod );

						if ( $pos !== false ) {

							return [ $field["FIELD_NAME"], $list["ID"] ];
						}

					}
				}

			}
		}


		return $ID;

	}
}