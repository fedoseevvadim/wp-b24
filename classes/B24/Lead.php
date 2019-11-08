<?php


namespace B24;


final class Lead implements B24Object
{

	use MapFields;

	public $rest = "crm.lead.add.json";

	private $connector;

	const STATUS_ID = "NEW";
	const TITLE = "Новый заказ с сайта"; // Default name

	public function __construct ( $connector )
	{

		if ( empty ( $connector ) ) {
			throw new \InvalidArgumentException( 'Connector to B24 must me set' );
		}

		$this->connector = $connector;
	}

	public function get ( $item )
	{

		if ( empty ( $item ) ) {
			throw new \InvalidArgumentException( 'Item is empty' );
		}

	}

	public function set ( array $data )
	{

		if ( empty ( $data ) ) {
			throw new \InvalidArgumentException( 'Data is empty' );
		}

	}

}