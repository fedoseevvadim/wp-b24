<?php


namespace B24;


final class Deal implements B24Object {

    const TITLE     = "Новый заказ с сайта";
    const STATUS_ID = "NEW";

    public $rest = "crm.deal.add.json";
    private $connector;

    use MapFields;

    private static $arrDealFields = [
        ["CONTACT_ID",  "CONTACT_ID"],
        ["SOURCE_ID",   "SOURCE_ID"],
        ["CATEGORY_ID", "CATEGORY_ID"],
    ];


    public function __construct( $connector ) {

        if ( empty ( $connector ) ) {
            throw new \InvalidArgumentException('Connector to B24 must me set');
        }

        $this->connector = $connector;
    }

    /**
     * get - get element
     * @param   string $item - kind of property
     */
    public function get( $item ) {

        if ( empty ( $item ) ) {
            throw new \InvalidArgumentException('Item is empty');
        }

    }

    /**
     * setTitle - set up title
     * @param   string $title - new title name
     */
    private function setTitle( string $title ): string {

        // Set up title
        if ( $title ) {
            $title = $title;
        } else {
            $title = self::TITLE;
        }

        return $title;

    }

    /**
     * get - get element
     * @param   string $item - kind of property
     */
    public function set( array $data ) {

        if ( empty ( $data ) ) {
            throw new \InvalidArgumentException('Data is empty');
        }

        $data["auth"]                 = $this->connector->accessToken;
        $data["fields"]['TITLE']      = $this->setTitle( $data["fields"]["TITLE"] ) . $data['client_name'];
        $data["fields"]['NAME']       = $data['name'];
        $data["fields"]['STATUS_ID']  = self::STATUS_ID;

        $response = $this->connector->buildQuery( $data, $this->rest );

        return $response['result'];

    }


    /**
    * getDealUserFileds - get user fields
    * @param   no params
    * @return $response['result']
    */
    public function getDealUserFileds () {

        $restQuery      = "crm.deal.userfield.list";

        $params["auth"] = $this->connector->accessToken;
        $response       = $this->connector->buildQuery( $params, $restQuery );
        return $response['result'];
    }

}