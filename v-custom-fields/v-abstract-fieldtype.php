<?php

abstract class FieldType {
	
	// Дефолтные опции поля
	private $default_options;
	// Текстовый поиск
	private $text_search;
	// Индексный поиск
	private $index_search;
	// Пользователь
	protected $user_level;
	/*
	 * 1. Функция загрузки HTML поля и его значений при открытии настройки
	 * плагина ( $load_type = 'load' ) 2. Функция создания нового поля. Выдает
	 * HTML поля с дефолтными значениями ( $load_type = 'new' ) 3. Функция смены
	 * типа поля. Выдает HTML сменяемого поля, его нижнюю часть ( $load_type =
	 * 'change' )
	 */
	abstract function LoadOptions($load_type = "new", $data = NULL);
	// Проверка данных опций
	abstract function ValidateOptions($array);
	/*
	 * Проверка валидности при сохранении самих полей при
	 * создании/редактировании постов
	 */
	abstract function ValidatePostField($array = NULL, $data = NULL);
	// Вывод поля в создании/редактировании поста
	abstract function OutField($array, $post_id = 0);
	/*
	 * Сохрание опций в настройках плагина(Сохраняет все поля при нажатии на
	 * кнопку Save)
	 */
	// abstract function SaveOptions($array);
	// Вывод поля в самой теме
	abstract function Out($array = NULL, $params = NULL);
	/*
	 * Перегрузка поиска. Для каждого поля можно натсроить свой поиск по
	 * определенным критериям
	 */
	// Поиск cf_field=value
	abstract function Mysql_Where($pieces = NULL, $fieldname = NULL, $value = NULL);
	// Поиск cf_field_no=value
	abstract function Mysql_Where_No($pieces = NULL, $fieldname = NULL, $value = NULL);
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	abstract function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL);
	// Конструктор базового класса. Инициализирует уровень доступа пользователя
	function __construct() {
		global $user_ID;
		$this->user_level = get_userdata ( $user_ID )->user_level;
	}
	// функция сохранения поля
	function SaveOptions($array) {
			global $db, $wpdb;
			$fieldtype = $array ['select'];
			$result = mysql_query ( "SELECT `id` FROM  " . $wpdb->prefix . "v_field_types WHERE name='$fieldtype'", $db );
			$fieldid = mysql_result ( $result, 0 );
			$name = $array ['fieldname'];
			$translit = translit ( $name );
			$result2 = mysql_query ( "SELECT `name` FROM  " . $wpdb->prefix . "v_field_options WHERE name='$name'", $db );
			if (strlen ( mysql_result ( $result2, 0 ) ))
				return 0;
			
			$options = serialize ( $array );
			$isearch = $array ['isearch'];
			if (strlen ( $isearch ))
				$isearch = 1;
			else
				$isearch = 0;
			mysql_query ( "INSERT INTO  " . $wpdb->prefix . "v_field_options (`fieldtype`,`name`,`translit`,`options`,`isearch`) VALUES($fieldid,'$name','$translit','$options',$isearch)" );
			return 0;
	}
	// Проверка корректности при сохранении опций в настройках плагина
	function Validate($str = NULL, $type = NULL, $param = NULL) {
		$err_status = "";
		$str = iconv ( "utf-8", "windows-1251", $str );
		switch ($type) {
			case "length" :
				if (strlen ( $str ) > $param) {
					$err_status .= "<strong>" . $str . " LENGTH IS MORE THAN " . $param . " SYMBOLS</strong></br>";
				}
				break;
			case "string" :
				if (! preg_match ( "/^[0-9a-zA-Z\-_]+$/", $str )) {
					$err_status .= "<strong>NOT VALID STRING \"" . $str . "\". ONLY LATIN SYMBOLS, INTEGER DIGITS AND UNDERLINES AVAILIBLE</strong></br>";		
				}
				break;
			case "isdigit" :
				if (! is_numeric ( $str )) {
					$err_status .= "<strong>\"" . $str . " IS NOT DIGIT\"</strong></br>";
				}
				break;
		}
		return $err_status;
	}
}
?>