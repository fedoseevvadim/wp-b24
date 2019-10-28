<?php


namespace B24;


class Terms {

    public $arrTerms        = []; // array of terms, get from plugin settings
    public $defaultCategory = 0;

    function __construct( $sOption ) {

        if ( !$sOption ) {

            throw new \InvalidArgumentException('No options have past to class');

        }

        $this->arrTerms = explode(
            \Parser\Struct::delimeterLines,
            $sOption
        );

    }

    public function getTerm ( $termID ) {

        if ( !$termID ) {

            throw new \InvalidArgumentException('No term ID has been past');

        }

        $termID = array_search($termID, $this->arrTerms);

        if ( $termID > 0 ) {
            return $this->arrTerms[$termID];
        } else {
            return $this->defaultCategory;
        }


    }


}