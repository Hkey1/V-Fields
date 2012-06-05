<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Select extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => "" );
		$this->text_search = true;
		$this->index_search = false;
	}
	
	function ValidateOptions($array) {
		$err_status = "";
		$err_status .= parent::Validate ( $array ['fieldname'], "length", 20 );
		$err_status .= parent::Validate ( $array ['fieldname'], "string" );
		return $err_status;
	}
	/*
	 * function ValidateOptions($array) { $err_status = 0; $str = iconv (
	 * "utf-8", "windows-1251", $array ['fieldname'] ); if (strlen ( $str ) >
	 * 20) { echo "<strong>NAME'S LENGTH IS MORE THAN 20 SYMBOLS</strong></br>";
	 * $err_status ++; } if (! preg_match ( "/^[0-9a-zA-ZА-я\-_ \s]+$/", $str ))
	 * { echo "<strong>NOT VALID STRING \"" . $array ['fieldname'] . "\". ONLY
	 * DIGITS, SPACES AND UNDERLINES AVAILIBLE</strong></br>"; $err_status ++; }
	 * $select_items = explode ( "\n", $array ['default'] ); foreach (
	 * $select_items as $row ) { if (strlen ( $row ) > 20) { echo $row . " TOO
	 * LONG</br>"; $err_status ++; } } return $err_status; }
	 */
	// ///////////////////////////
	function ValidatePostField($array = NULL, $data = NULL) {
		if (! $array)
			return "<div class=\"error\"><p>This or that went wrong</p></div>";
		$options = unserialize ( $array ['options'] );
		return true;
	}
	
	// Функция загрузки полей
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( 'Select' );
		// Если вызываем функции для загрузки опций существующего поля
		if ($load_type == "load") {
			if ($data != NULL) {
				$options = $data ['options'];
				if (! strlen ( $options ))
					return 0;
				$array = unserialize ( $options );
				$id = $data ['id'];
				$select = $array ['select'];
				$name = $array ['fieldname'];
				$default = $array ['default'];
				$search = $array ['search'];
				($search == 1) ? $search_checked = "checked=\"true\"" : $search_checked = "";
				$str = "
				<div class=\"section\">
					<h3>
					     <span class=\"container\">
					     	<input type=\"hidden\" name=\"hr\" value=\"true\">
							<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"$name\" />
							<span>
							<select class=\"select\" name=\"select\">$selection</select>
							<span class=\"ui-icon ui-icon-closethick\"></span>
							</span>
						</span>		
					</h3>
					<div class=\"field_settings\">
						<p>Select items(each string is item):<br/> <textarea name=\"default\" cols=80 rows=10>" . $default . "</textarea></p>
						<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
					</div>
				</div>";
			}
		} else {
			$this->text_search ? $search_checked = "checked=\"true\"" : $search_checked = "";
			if ($load_type == "new") {
				$str = "
			<div class=\"section\">
				<h3>
					<span class=\"container\">
						<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"\" />
					<span>
					<select class=\"select\" name=\"select\">" . $selection . "</select>
					<span class=\"ui-icon ui-icon-closethick\"></span>
					</span>
					</span>
				</h3>
						<div class=\"field_settings\">
							<p>Select items(each string is item):<br/> <textarea name=\"default\" cols=80 rows=10>" . $this->default_options ['default'] . "</textarea></p>
							<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
						</div>
			</div>";
			
			} elseif ($load_type == "change") {
				$str = "
				<p>Select items(each string is item):<br/> <textarea name=\"default\" cols=80 rows=10>" . $this->default_options ['default'] . "</textarea></p>
				<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>";
			}
		}
		return $str;
	}
	// Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$select_str = $array ['default'];
		$value = - 1;
		$name = $array ['fieldname'];
		if ($post_id) {
			global $db, $wpdb;
			$value = 0;
			$query_result = mysql_query ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! is_numeric ( $value ))
				$value = - 1;
		}
		$select_list = explode ( "\n", $select_str );
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<select name=\"$translit\">";
		if ($value == - 1) {
			$i = 0;
			$result = $result . "<option value=\"-1\" selected=\"true\"><b>select item</b></option>";
			foreach ( $select_list as $row ) {
				$result = $result . "<option value=\"$i\">" . $row . "</option>";
				$i ++;
			}
		} else {
			$i = 0;
			$result = $result . "<option value=\"-1\"><b>select item</b></option>";
			foreach ( $select_list as $row ) {
				if ($i == $value)
					$result = $result . "<option value=\"$i\" selected=\"true\">" . $row . "</option>";
				else
					$result = $result . "<option value=\"$i\">" . $row . "</option>";
				$i ++;
			}
		}
		
		$result = $result . "</select></br></br>";
		return $result;
	}
	
	function Out($array = NULL, $params = NULL) {
		global $wpdb;
		$id = $array ['id'];
		$key = $array ['data'];
		if ($key == - 1)
			return;
		$result = "";
		$options = unserialize ( $array ['options'] );
		$select_values = explode ( "\n", $options ['default'] );
		if ($this->user_level != 10) {
			$result .= "<span>" . $select_values [$key] . "</span></br>";
			$result .= "</br>";
			return $result;
		}
		$selection = "<select class=\"edit_select\">";
		$i = 0;
		foreach ( $select_values as $row ) {
			if ($i == $key)
				$selection = $selection . "<option value=\"$i\" selected=\"true\">" . $row . "</option>";
			else
				$selection = $selection . "<option value=\"$i\">" . $row . "</option>";
			$i ++;
		}
		$selection = $selection . "</select>";
		$result .= "<span id=\"$id\"  class=\"select\">" . $select_values [$key] . "</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<input type=\"hidden\" id=\"selected\" value=\"$key\" />
		$selection
		</div>";
		$result .= "</br>";
		return $result;
	}
	// /////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid, $data, $params = NULL) {
		
		if ($this->user_level != 10)
			return;
		global $wpdb;
		$options_result = $wpdb->get_row ( "SELECT options FROM  " . $wpdb->prefix . "v_field_options WHERE translit = (SELECT translit FROM v_fields WHERE id = $fieldid)" );
		$options = unserialize ( $options_result->options );
		$select_values = explode ( "\n", $options ['default'] );
		if ($data > count ( $select_values ))
			return;
		$wpdb->query ( "UPDATE  " . $wpdb->prefix . "v_fields SET data = '$data' WHERE id = $fieldid" );
		// Обновляем метаданные вордпресса
		$result = $wpdb->get_results ( "SELECT translit,post_id FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
		$fieldname = $result [0] ['translit'];
		$post_id = $result [0] ['post_id'];
		if (ctype_digit ( $post_id ) && strlen ( $fieldname ))
			update_post_meta ( $post_id, $fieldname, $data );
			//
		echo $select_values [$data];
	}
	// //////////////////////////////////////////////////////////////////////
	function Mysql_Where($pieces = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
	// Поиск cf_field_no=value
	function Mysql_Where_No($pieces = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
}
?>