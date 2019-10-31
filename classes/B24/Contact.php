<?php


namespace B24;


use Parser\Struct;

final class Contact implements B24Object {

    public $list    = "crm.contact.list.json";
    public $add     = "crm.contact.add.json";
    public $uf      = "crm.contact.userfield.list";

    const STATUS_ID = "NEW";

    private $connector;

    use MapFields;

    // map fileds
    // First Elem - WP
    // Second - B24 fileds
    private static $arrContactFields = [

        ["first_name",       "NAME"],
        ["last_name",        "LAST_NAME"],
        ["billing_email",    "EMAIL"] ,
        ["billing_phone",    "WORK_PHONE"]

    ];

    public function __construct( $connector ) {

        if ( empty ( $connector ) ) {
            throw new \InvalidArgumentException('Connector to B24 must me set');
        }

        $this->connector = $connector;
    }

    /**
    * get - добавляет контакт в базу
    * @param   array $data - array of data
    * @return $response
    */
    public function get( $email ) {

        if ( empty ( $email ) ) {
            throw new \InvalidArgumentException('Email is empty');
        }

        $params["auth"]     = $this->connector->accessToken;
        $params["filter"] = ["EMAIL" => $email];

        $response = $this->connector->buildQuery( $params, $this->list );

        return $response['result'];
    }

    /**
    * addLead - добавляет контакт в базу
    * @param   array $data - array of data
    * @return $response
    */
    public function set( array $data ): int {

        if ( empty ( $data ) ) {
            throw new \InvalidArgumentException('Data is empty');
        }

        $result = $this->get( $data['billing_email'][0] );

        if ( is_array( $result) AND count($result) > 0 ) {

            $userId = (int) $result[0]["ID"];


            return $userId;
        }

        // Or add contact
        $parser = new \Parser\Settings ();

        $uf = $this->getUF();
        $parser->setUser($data);
        $data = $parser->parseFields($data["contact"], $data);

        foreach (self::$arrContactFields as $key => $item ) {

            $params["fields"][$item[1]] = $data[$item[0]][0];

        }

        $params["fields"]['STATUS_ID']              = self::STATUS_ID;
        $params["fields"]['PHONE']                  = [[
            "VALUE" => $data['billing_phone'][0],
            "VALUE_TYPE" => "WORK"
        ]];

        $params["fields"]['EMAIL']                  = [[
            "VALUE" => $data['billing_email'][0],
            "VALUE_TYPE" => "WORK"
        ]];

        $params = $this->map ( $uf, $data, $params);

        $params["auth"]  = $this->connector->accessToken;

        $response = $this->connector->buildQuery( $params, $this->add );

        return $response['result']; //return contact id

    }

    /**
     * getUF - get contact user fields from B24
     * @return $response['result']
     */
    public function getUF () {

        $params["auth"]     = $this->connector->accessToken;
        $response           = $this->connector->buildQuery( $params, $this->uf );

        return $response['result']; //return contact id
    }

}