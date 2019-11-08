<?php


namespace B24;


class Terms
{

	public $arrTerms = []; // array of terms, get from plugin settings
	public $defaultCategory = 0;

	function __construct ( $sOption )
	{

		if ( !$sOption ) {

			throw new \InvalidArgumentException( 'No options have past to class' );

		}

		$arrLines = explode (
			\Parser\Struct::DELIMETER_FOR_LINES,
			$sOption
		);

		foreach ( $arrLines as $line ) {

			$this->arrTerms[] = explode (
				\Parser\Struct::DELIMETER,
				$line
			);

		}

	}

	public function getTerm ( $termID )
	{

		if ( !$termID ) {

			throw new \InvalidArgumentException( 'No term ID has been past' );

		}

		$termID = array_search ( $termID, array_column ( $this->arrTerms, "0" ) );

		if ( $termID === false ) {
			return $this->defaultCategory;
		}

		return $this->arrTerms[$termID][1];

	}


}