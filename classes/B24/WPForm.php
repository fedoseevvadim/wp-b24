<?php

namespace B24;

class WPForm
{

	const PATH = "/wp-content/plugins/b24-plugin/tpl/";
	const PREFIX = "_ru";

	// Settings
	private static $arrForm = [

		[ "Хост системы", "host" . self::PREFIX, "text", "yourdomain.bitrix24.ru" ],
		[ "Логин пользователя", "login" . self::PREFIX, "text", "User login" ],
		[ "Пароль пользователя", "password" . self::PREFIX, "text", "User password" ],
		[ "Название сделки", "deal_name" . self::PREFIX, "text", "Deal name" ],
		[ "Client ID", "client_id" . self::PREFIX, "text", "Client ID" ],
		[ "Client Secret Key", "client_secret" . self::PREFIX, "text", "Client Secret Key" ],
		[ "Наименование источника", "source_name" . self::PREFIX, "text", "название сайта (например)" ],
		[ "Связь по полям", "field_link" . self::PREFIX, "textarea", "поле битрикс []->[] " ],
		[ "Направление сделок", "lead_terms" . self::PREFIX, "textarea", "Связь по направлениям сделок [Категория товара]->[ID Направления в Б24]" ],
		[ "Поля Контакта", "contact" . self::PREFIX, "textarea", "Связь по полям у контакта [ID B24]->[Значение]" ],
		[ "Поля при создании контакта (из форм обратной связи - Contact Form 7)", "contact_create" . self::PREFIX, "textarea", "Связь по полям у контакта [ID B24]->[Значение]" ],
		[ "Укажите ID пользователя", "user_id" . self::PREFIX, "text", "от имени которого создавать сущности в Б24" ],
		[ "Укажите 1 если нужно передавать пользователей в Б24 при регистрации", "reg_user" . self::PREFIX, "text", "регистрировать пользователя" ]
	];

	/*
	* get template
	*/
	private function getTpl ( $type )
	{

		switch ( $type ) {

			case "text":

				$tpl_input = file_get_contents ( $_SERVER["DOCUMENT_ROOT"] . self::PATH . "input.html" );

				break;

			case "textarea":

				$tpl_input = file_get_contents ( $_SERVER["DOCUMENT_ROOT"] . self::PATH . "textarea.html" );

				break;
		}


		return $tpl_input;
	}


	/*
	* replace values in template form
	*/
	public function buildForm ( array $arrOptions )
	{

		$form = "";

		foreach ( self::$arrForm as $key => $item ) {

			if ( $item[2] === "text" ) {

				$tpl = $this->getTpl ( "text" );

				$tpl = str_replace ( "[NAME]", $item[0], $tpl );
				$tpl = str_replace ( "[VAR_NAME]", $item[1], $tpl );
				$tpl = str_replace ( "[VALUE]", $arrOptions[$item[1]], $tpl );
				$tpl = str_replace ( "[PLACE_HOLDER]", $item[3], $tpl );
				$tpl = str_replace ( "[TYPE]", $item[2], $tpl );

				$form .= $tpl;

			}

			if ( $item[2] === "textarea" ) {

				$tpl = $this->getTpl ( "textarea" );

				$tpl = str_replace ( "[NAME]", $item[0], $tpl );
				$tpl = str_replace ( "[VAR_NAME]", $item[1], $tpl );
				$tpl = str_replace ( "[VALUE]", $arrOptions[$item[1]], $tpl );

				$form .= $tpl;
			}
		}

		return $form;

	}

	/*
	* get all options
	*/
	public function getOptions (): array
	{

		$array = [];

		$arrElements = array_column ( self::$arrForm, 1 );

		foreach ( $arrElements as $elem ) {
			$array[$elem] = get_option ( $elem );
		}

		return $array;

	}

}