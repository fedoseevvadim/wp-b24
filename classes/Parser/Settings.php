<?php

namespace Parser;


class Settings {

    public $terms;
    public $order;
    public $postOrder;
    public $user;

    /**
    * setTerms - set var terms
    * @param   string terms
    *
    */
    public function setTerms( array $terms ) {

        if ( empty ( $terms ) ) {
            throw new \InvalidArgumentException('No terms has past');
        }

        $this->terms = $terms;

    }

    /**
    * setOrder - set var order
    * @param   array
    *
    */
    public function setOrder( array $order ) {

        if ( empty ( $order ) ) {
            throw new \InvalidArgumentException('No order has past');
        }

        $this->order = $order;

    }


    /**
    * setPostOrder - set var post Order
    * @param   array
    *
    */
    public function setPostOrder( array $postOrder ) {

        if ( empty ( $postOrder ) ) {
            throw new \InvalidArgumentException('No postOrder has past');
        }

        $this->postOrder = $postOrder;

    }


    /**
     * setPostOrder - set var post Order
     * @param   array
     *
     */
    public function setUser( array $user ) {

        if ( empty ( $user ) ) {
            throw new \InvalidArgumentException('User date were not past');
        }

        $this->user = $user;

    }



    public function getArray( $value ):array {

        return explode(Struct::DELIMETER, $value);

    }


    private function parseTwoVal ( $arrElem, &$arrData ) {

        $arrData[$arrElem[0]] = $this->terms[$arrElem[1]][0];

        switch ($arrElem[1]) {

            // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
            case "TERMS":
                $this->parseTerms();
                $arrData[$arrElem[0]] = $this->terms[$arrElem[2]][0];

                break;

            default:

                $arrData[$arrElem[0]] = $arrElem[1];
                break;
        }


    }


    private function parseThreeVal ($arrElem, &$arrData) {

        //$this->checkAdditionalValues();

        switch ($arrElem[1]) {

            // UF_CRM_1569421180=>TERMS=>тип_мероприятия - пример из настроек
            case "TERMS":

                $arrData[$arrElem[0]] = $this->terms[$arrElem[2]][0];

                break;

            // UF_CRM_1569421314=>ORDER=>order_item_name
            case "ORDER":

                $itemID = $arrElem[2];
                $value = $this->order[0]->$itemID;
                $arrData[$arrElem[0]] = $value;

                break;

            case "USER":

                $itemID = $arrElem[2];
                $value = $this->user[$itemID][0];
                $arrData[$arrElem[0]] = $value;

                break;

            case "POST_ORDER":

                $itemID = $arrElem[2];
                $value = $this->postOrder[$itemID][0];
                $arrData[$arrElem[0]] = $value;

                break;

        }

    }


    public function parseFields ( string $fields, array $arrData ) : array {

        if ( empty ( $fields ) ) {
            throw new \InvalidArgumentException('Fields cannot be empty');
        }

        if ( empty ( $arrData ) ) {
            throw new \InvalidArgumentException('ArrData cannot be empty');
        }

        $arrayOfLines = explode(
            Struct::DELIMETER_FOR_LINES,
            $fields
        );

        if ( is_array( $arrayOfLines ))  {

            foreach ($arrayOfLines as $value) {

                $arrElem = $this->getArray( $value );

                // OPPORTUNITY=>_price
                if ( count( $arrElem ) === 2 ) {
                    $this->parseTwoVal( $arrElem, $arrData );
                }

                // UF_CRM_1569421314=>ORDER=>order_item_name
                if ( count( $arrElem) === 3 ) {
                    $this->parseThreeVal( $arrElem, $arrData );
                }
            }
        }

        return $arrData;

    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////\
    // CHECK DATA FUNCTIONS

    private function checkAdditionalValues() {

        if ( empty ( $this->terms ) ) {
            throw new \RuntimeException('No Terms were past to function setTerms()');
        }

        if ( empty ( $this->order ) ) {
            throw new \RuntimeException('ORDER was no past to function setOrder()');
        }

        if ( empty ( $this->postOrder ) ) {
            throw new \RuntimeException('POST ORDER was no past to function setPostOrder()');
        }

    }

}