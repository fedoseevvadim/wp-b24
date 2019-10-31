<?php

namespace B24;

trait MapFields {


    /**
    * map - map fileds in B24
    * @param   $fieldsToMap B24 Fields
    * @param   $data   -
    * @param   $params - array for updating or adding fields in B24
    * @return  array
    */
    public function map( array $fieldsToMap, array $data, array $params ):array {

        if ( empty ( $fieldsToMap ) ) {
            throw new \InvalidArgumentException('FieldsToMap cannot be empty');
        }

        if ( empty ( $data ) ) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

//        if ( empty ( $params ) ) {
//            throw new \InvalidArgumentException('Params cannot be empty');
//        }

        foreach ( $data as $key => $item ) {

            if ( strpos( $key, "UF_CRM_" ) === false ) {
                $params["fields"][$key] = $item;
                continue;

            }

            // search element in B24
            $bFieldFound = false;

            foreach ($fieldsToMap as $field ) {

                if ( $field["FIELD_NAME"] === $key ) {

                    $bFieldFound = true;

                    switch ( $field["USER_TYPE_ID"] ) {

                        // map date
                        case "date":
                            $params["fields"][$key] = $data[$key];
                            continue;

                            break;

                        // find ID in B24 values
                        case "enumeration":

                            $listID = array_search( $item, array_column( $field["LIST"], 'VALUE') );

                            if ( $listID === false ) {
                                continue;
                            }

                            $ID = (int) $field["LIST"][$listID]["ID"];
                            $params["fields"][$key] = $ID;
                            continue;

                            break;

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

}

