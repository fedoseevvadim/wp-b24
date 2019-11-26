<?php

namespace Parser;

class Form
{

	public $options;
	public $arrTerms = []; // array of options, get from plugin settings
	public $arrForms = [];

	function __construct ( $sOption )
	{

		if ( !$sOption ) {

			throw new \InvalidArgumentException( 'No options have past to class' );

		}

		$arrLines = explode (
			\Parser\Struct::DELIMETER_FOR_LINES,
			$sOption
		);

		$FORM_ID = 0;

		foreach ( $arrLines as $key => $line ) {

			if ( $line === \Parser\Struct::FORM_DELIMETER ) {

				// Next element should be id of form

				$arrForm = explode (
					\Parser\Struct::DELIMETER,
					$arrLines[$key + 1] // get next line
				);

				$FORM_ID = (int) $arrForm[1]; // id of FORM from settings FORM_ID=>1944

				continue;
			}

			if ( $FORM_ID > 0 ) {

				$this->arrForms[$FORM_ID] .= $line . \Parser\Struct::DELIMETER_FOR_LINES;

				// collect all form data to array
//				$this->arrForms[$FORM_ID][] = explode (
//					\Parser\Struct::DELIMETER,
//					$line
//				);

			}

		}

	}

	/**
	 * setFormOptions - set var options
	 *
	 * @param string options
	 *
	 */
	public function setFormOptions ( array $options )
	{

		if ( empty ( $options ) ) {
			throw new \InvalidArgumentException( 'No options has past' );
		}

		$this->options = \B24\Struct::removeNestedArray ( $options );

	}

}