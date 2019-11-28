<?php


namespace B24;


use Parser\Struct;

final class Contact implements B24Object
{

	public $list = "crm.contact.list.json";
	public $add = "crm.contact.add.json";
	public $update = "crm.contact.update";
	public $uf = "crm.contact.userfield.list";
	public $mobileType = "MOBILE";
	public $emailType = "HOME";

	const STATUS_ID = "NEW";

	private $connector;

	use MapFields;

	// map fileds
	// First Elem - WP
	// Second - B24 fileds
	private static $arrContactFields = [

		[ "first_name", "NAME" ],
		[ "last_name", "LAST_NAME" ],
		[ "billing_email", "EMAIL" ],
		[ "billing_phone", "WORK_PHONE" ]

	];

	public static $arrGenderRU = [
		"Мужчина",
		"Женщина"
	];

	public function __construct ( $connector )
	{

		if ( empty ( $connector ) ) {
			throw new \InvalidArgumentException( 'Connector to B24 must me set' );
		}

		$this->connector = $connector;
	}

	/**
	 * get - добавляет контакт в базу
	 *
	 * @param array $data - array of data
	 *
	 * @return $response
	 */
	public function get ( $email )
	{

		if ( empty ( $email ) ) {
			throw new \InvalidArgumentException( 'Email is empty' );
		}

		$params["auth"] = $this->connector->accessToken;
		$params["filter"] = [ "EMAIL" => $email ];

		$response = $this->connector->buildQuery ( $params, $this->list );

		return $response['result'];
	}

	// Here is function only working with wordpress data

	/**
	 * addLead - добавляет контакт в базу
	 *
	 * @param array $data - array of data
	 *
	 * @return $response
	 */
	public function set ( array $data )
	{

		if ( empty ( $data ) ) {
			throw new \InvalidArgumentException( 'Data is empty' );
		}

		try {

			$parser = new \Parser\Settings ();
			$uf = $this->getUF ();
			$userId = 0;

			$data = \B24\Struct::removeNestedArray ( $data ); // remove data like this $data["contact"][0] convert it to $data["contact"]
			$parser->setUser ( $data );
			$data = $parser->parseFields ( $data["contact"], $data );

			$result = $this->get ( $data['billing_email'] );


		} catch ( \Exception $e ) {

			echo 'Caught exeption: ' . $e->getMessage ();
		}


		if ( is_array ( $result ) AND count ( $result ) > 0 ) {

			$userId = (int)$result[0]["ID"];

		}


		try {

			// Or add contact
			foreach ( self::$arrContactFields as $key => $item ) {

				$params["fields"][$item[1]] = $data[$item[0]];

			}

			$params["fields"]['STATUS_ID'] = self::STATUS_ID;
			$params["fields"]['PHONE'] = [
				[
					"VALUE"      => $data['billing_phone'],
					"VALUE_TYPE" => $this->mobileType
				]
			];

			$params["fields"]['EMAIL'] = [
				[
					"VALUE"      => $data['billing_email'],
					"VALUE_TYPE" => $this->emailType
				]
			];

		} catch ( \Exception $e ) {

			echo 'Caught exeption: ' . $e->getMessage ();
		}


		// Working with checkboxes from WEB FORM

		if ( is_array ( $data["checkbox"] ) ) {

			foreach ( $data["checkbox"] as $chk ) {

				// try to find by field name
				foreach ( $uf as $field ) {

					$chkName = $field["SETTINGS"]["LABEL_CHECKBOX"];
					$key = $field["FIELD_NAME"];

					if ( $chk === $chkName ) {
						$data[$key] = 1;
						break;
					}
				}
			}
		}

		if ( is_array ( $data["raw_checkbox"] ) ) {

			foreach ( $data["raw_checkbox"] as $chk ) {

				// try to find by field name
				foreach ( $uf as $field ) {

					$chkName = $field["SETTINGS"]["LABEL_CHECKBOX"];
					$key = $field["FIELD_NAME"];

					if ( $chk === $chkName ) {

						if ( $data[$key] !== 1 ) {
							unset ( $data[$key] );
							break;
						}
					}
				}

			}
		}


		try {

			// Try to find id gender by typem
			foreach ( $data as $key => $elem ) {

				if ( $elem === "typem" ) {

					if ( $data["typem"] === "men" ) {
						$data[$key] = self::$arrGenderRU[0];
					} else {
						$data[$key] = self::$arrGenderRU[1];
					}
				}
			}

			$params = $this->map ( $uf, $data, $params );

			$params["auth"] = $this->connector->accessToken;

			if ( $userId > 0 ) {

				$params["id"] = $userId;
				// while updating contact, SOURCE_ID should not be changed
				unset( $params["fields"]["SOURCE_ID"] );

				$response = $this->connector->buildQuery ( $params, $this->update );
			} else {
				$response = $this->connector->buildQuery ( $params, $this->add );
				$userId = $response['result'];
			}

		} catch ( \Exception $e ) {

			echo 'Caught exeption: ' . $e->getMessage ();
		}


		return $userId;

	}

//	/**
//	 * update - update contact user fields in B24
//	 *
//	 */
//	public function update ( int $id, array $params )
//	{
//		if ( empty ( $id ) ) {
//			throw new \InvalidArgumentException( 'ID must me set' );
//		}
//
//		if ( empty ( $params ) ) {
//			throw new \InvalidArgumentException( 'Params must me set' );
//		}
//
//		$params["auth"] = $this->connector->accessToken;
//
//		$response = $this->connector->buildQuery ( $params, $this->update );
//
//		return $response['result'];
//	}

	/**
	 * getUF - get contact user fields from B24
	 *
	 * @return $response['result']
	 */
	public function getUF ()
	{

		$params["auth"] = $this->connector->accessToken;
		$response = $this->connector->buildQuery ( $params, $this->uf );

		return $response['result']; //return contact id
	}


}