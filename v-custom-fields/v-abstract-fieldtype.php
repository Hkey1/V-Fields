<?php

abstract class FieldType {
	// Имя поля
	public $name;
	// Дефолтные опции поля
	public $default_options;
	// Текстовый поиск
	public $text_search;
	// Индексный поиск
	public $index_search;
	// Пользователь
	protected $user_level;
	
	/*
	 * Проверка валидности при сохранении самих полей при
	 * создании/редактировании постов
	 */
	abstract function ValidatePostField($array = NULL, $data = NULL);
	// Вывод поля в создании/редактировании поста
	abstract function OutField($array, $post_id = 0);
	// Обновление значения поля на лету(во фронтэнде сайта)
	abstract function UpdateField($fieldid, $data, $params = NULL);
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
	
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	abstract function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL);
	// Конструктор базового класса. Инициализирует уровень доступа пользователя
	function __construct() {
		global $user_ID;
		$this->user_level = get_userdata ( $user_ID )->user_level;
	}
	/*
	 * 1. Функция загрузки HTML поля и его значений при открытии настройки
	 * плагина ( $load_type = 'load' ) 2. Функция создания нового поля. Выдает
	 * HTML поля с дефолтными значениями ( $load_type = 'new' ) 3. Функция смены
	 * типа поля. Выдает HTML сменяемого поля, его нижнюю часть ( $load_type =
	 * 'change' )
	 */
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( get_class ( $this ) );
		// Если вызываем функции для загрузки опций существующего поля
		if ($load_type == "load") {
			if ($data != NULL) {
				$options = $data ['options'];
				if (! strlen ( $options ))
					return 0;
				$array = unserialize ( $options );
				$this->name = $array ['fieldname'];
				$search = $array ['search'];
				$isearch = $array ['isearch'];
				($search == 1) ? $search_checked = "checked=\"true\"" : $search_checked = "";
				($isearch == 1) ? $isearch_checked = "checked=\"true\"" : $isearch_checked = "";
				$html = "
				<div class=\"section\">
					<h3>
						<span class=\"container\">
						<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"" . $this->name . "\" />
						<span>
						<select class=\"select\" name=\"select\">$selection</select>
						<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
						</span>
					</h3>
				<div class=\"field_settings\">";
				
				foreach ( $this->default_options as $key => $value ) {
					foreach ( $array as $key_array => $value_array ) {
						if ($key_array == $key) {
							switch ($value ['type']) {
								case "int" :
								case "string" :
									$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><input type=\"text\" name=\"" . $key_array . "\" value=\"" . $value_array . "\"/></p>";
									break;
								case "text" :
									$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><textarea name=\"default\" cols=80 rows=10>" . $value_array . "</textarea></p>";
									break;
								case "bool" :
									if ($value_array == 1)
										$checked = "checked=\"true\"";
									else
										$checked = "";
									$html .= "<input type=\"hidden\" name=\"$key\" value=\"0\">";
									$html .= "<p>" . ucfirst ( $value ['label'] ) . ":<input type=\"checkbox\" name=\"$key\" class=\"html_editor\" value=\"1\" $checked /></p>";
									break;
								default :
									break;
							}
						}
					}
				}
				if ($this->default_options ['search_enabled'])
					$html .= "<p><b>text search</b> <input type=\"checkbox\"  name=\"search\" value=\"1\" $search_checked /></p>";
				if ($this->default_options ['isearch_enabled'])
					$html .= "<p><b>index search</b> <input type=\"checkbox\"  name=\"isearch\" value=\"1\" $isearch_checked /></p>";
				$html .= "</div></div>";
			
			}
		} else {
			$this->text_search ? $search_checked = "checked=\"true\"" : $search_checked = "";
			$this->index_search ? $isearch_checked = "checked=\"true\"" : $isearch_checked = "";
			// Если создаем новое поле
			if ($load_type == "new") {
				$html = "				
				<div class=\"section\">
					<h3>
						<span class=\"container\">
						<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"" . $this->name . "\" />
						<span>
						<select class=\"select\" name=\"select\">$selection</select>
						<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
						</span>
					</h3>
				<div class=\"field_settings\">";
				foreach ( $this->default_options as $key => $value ) {
					switch ($value ['type']) {
						case "int" :
						case "string" :
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><input type=\"text\" name=\"$key\" value=\"" . $value ['value'] . "\"/></p>";
							break;
						case "text" :
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><textarea name=\"default\" cols=80 rows=10>" . $value ['value'] . "</textarea></p>";
							break;
						case "bool" :
							$html .= "<input type=\"hidden\" name=\"$key\" value=\"0\">";
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":<input type=\"checkbox\" name=\"$key\" value=\"1\" /></p>";
							break;
						default :
							break;
					}
				}
				if ($this->default_options ['search_enabled'])
					$html .= "<p><b>text search</b> <input type=\"checkbox\"  name=\"search\" value=\"1\" $search_checked /></p>";
				if ($this->default_options ['isearch_enabled'])
					$html .= "<p><b>index search</b> <input type=\"checkbox\"  name=\"isearch\" value=\"1\" $isearch_checked /></p>";
				$html .= "</div></div>";
			} 			// Если меняем существующее поле
			elseif ($load_type == "change") {
				$html = "<div class=\"field_settings\">";
				foreach ( $this->default_options as $key => $value ) {
					switch ($value ['type']) {
						case "int" :
						case "string" :
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><input type=\"text\" name=\"$key\" value=\"" . $value ['value'] . "\"/></p>";
							break;
						case "text" :
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":</br><textarea name=\"default\" cols=80 rows=10>" . $value ['value'] . "</textarea></p>";
							break;
						case "bool" :
							$html .= "<input type=\"hidden\" name=\"$key\" value=\"0\">";
							$html .= "<p>" . ucfirst ( $value ['label'] ) . ":<input type=\"checkbox\" name=\"$key\" class=\"html_editor\" value=\"1\" /></p>";
							break;
						default :
							break;
					}
				}
				if ($this->default_options ['search_enabled'])
					$html .= "<p><b>text search</b> <input type=\"checkbox\"  name=\"search\" value=\"1\" $search_checked /></p>";
				if ($this->default_options ['isearch_enabled'])
					$html .= "<p><b>index search</b> <input type=\"checkbox\"  name=\"isearch\" value=\"1\" $isearch_checked /></p>";
				$html .= "</div></div>";
			}
		}
		return $html;
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
	// Поиск cf_field=value
	function Mysql_Where($pieces = NULL, $fieldname = NULL, $value = NULL) {
		global $wpdb;
		if (is_numeric ( $value )) {
			$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value = '$value')";
		} else {
			$values = explode ( "||", $value );
			if (is_numeric ( $values [0] ) || is_numeric ( $values [1] )) {
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND ( " . $wpdb->prefix . "postmeta.meta_value = '$values[0]' OR  " . $wpdb->prefix . "postmeta.meta_value = '$values[1]' ))";
			} else {
				if ($value == "_void_")
					$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value = '')";
			}
		}
		return $pieces;
	}
	
	// Поиск cf_field_no=value
	function Mysql_Where_No($pieces = NULL, $fieldname = NULL, $value = NULL) {
		global $wpdb;
		if (is_numeric ( $value ))
			$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value <> '$value')";
		return $pieces;
	}
}
?>