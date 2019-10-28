<?php

namespace Parser;


class Settings {


    public function getArray( $value ):array {

        return explode(Struct::delimeter, $value);

    }


    private function parseTwoVal ( $arrElem, &$arrData, &$arrORDER_TERMS ) {

        $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[1]][0];

        switch ($arrElem[1]) {

            // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
            case "TERMS":
                $this->parseTerms();
                $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];

                break;

            default:

                $arrData[$arrElem[0]] = $arrElem[1];
                break;
        }


    }


    private function parseThreeVal ($arrElem, &$arrData, &$arrORDER_TERMS, $ORDER, $arrPOST_ORDER) {

        switch ($arrElem[1]) {

            // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
            case "TERMS":

                $arrData[$arrElem[0]] = $arrORDER_TERMS[$arrElem[2]][0];

                break;

            // UF_CRM_1569421314=>ORDER=>order_item_name
            case "ORDER":

                $itemID = $arrElem[2];
                $value = $ORDER[0]->$itemID;
                $arrData[$arrElem[0]] = $value;

                break;

            case "POST_ORDER":

                $itemID = $arrElem[2];
                $value = $arrPOST_ORDER[$itemID][0];
                $arrData[$arrElem[0]] = $value;

                break;

        }

    }

    public function parseSettings ( $fields, $arrData, $ORDER, $arrORDER_TERMS, $arrPOST_ORDER ) {

        $arrayOfLines = explode(
            Struct::delimeterLines,
            $fields
        );

        if ( is_array( $arrayOfLines ))  {

            foreach ($arrayOfLines as $value) {

                $arrElem = $this->getArray( $value );

                // OPPORTUNITY=>_price
                if ( count( $arrElem ) === 2 ) {
                    $this->parseTwoVal( $arrElem, $arrData,$arrORDER_TERMS );
                }

                // UF_CRM_1569421314=>ORDER=>order_item_name
                if ( count( $arrElem) === 3 ) {
                    $this->parseThreeVal( $arrElem, $arrData,$arrORDER_TERMS, $ORDER, $arrPOST_ORDER );
                }
            }
        }

        return $arrData;

    }


}