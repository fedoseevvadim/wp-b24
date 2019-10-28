<?php


namespace B24;


class Deal implements B24Object {

    const dealTitle = "Новый заказ с сайта";

    const statusId = "NEW";
    public $rest = "crm.lead.add.json";
    private $connector;

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
     * get - get element
     * @param   string $item - kind of property
     */
    public function set( array $data ) {

        if ( empty ( $data ) ) {
            throw new \InvalidArgumentException('Data is empty');
        }

        // Set up title
        if ( $data["TITLE"] ) {
            $title = $data["TITLE"];
        } else {
            $title = self::leadTitle;
        }

        $params["auth"]                 = $this->connector->accessToken;
        $params["fields"]['TITLE']      = $title . $data['client_name'];
        $params["fields"]['NAME']       = $data['name'];
        $params["fields"]['STATUS_ID']  = self::statusId;

        $response = $this->connector->buildQuery( $params, $this->rest );

        return $response['result'];

    }


    /**
     * mapFieldsB24 - map fields to B24
     * @param   array $data      - array of data
     * @param   array $params    - array of params for B24
     * @return array $params
     */
    public function mapFieldsB24 ( array $data, array $params ): array {

        $arrUserFields = $this->getDealUserFileds();

        foreach ( $data as $key => $item ) {

            $type = gettype($item);

            if ( strpos( $key, self::prefix ) === false ) {
                $params["fields"][$key] = $item;
                continue;

            }

            // search element in B24
            $bFieldFound = false;

            foreach ( $arrUserFields as $field ) {

                if ( $field["FIELD_NAME"] === $key ) {

                    $bFieldFound = true;

                    switch ( $field["USER_TYPE_ID"] ) {

                        // map date
                        case "date":
                            $params["fields"][$key] = $data[$key];
                            continue;
                            break;

                        // find id in B24 values
                        case "enumeration":

                            $listID = array_search( $item, array_column( $field["LIST"], 'VALUE') );

                            if ( $listID >= 0 ) {
                                $ID = (int) $field["LIST"][$listID]["ID"];
                                $params["fields"][$key] = $ID;
                                continue;
                            }

                        case "string" or "integer" or "boolean":
                            $params["fields"][$key] = $item;
                            break;

                    }
                }
            }

            if ( $bFieldFound === false ) {
                $params["fields"][$key] = $item;
            }

        }

        return $params;
    }


    /**
     * getDealUserFileds - get user fields
     * @param   no params
     * @return result
     */
    public function getDealUserFileds () {

        $restQuery      = "crm.deal.userfield.list";

        $params["auth"] = $this->connector->accessToken;
        $response       = $this->connector->buildQuery( $params, $restQuery );
        return $response['result'];
    }
}