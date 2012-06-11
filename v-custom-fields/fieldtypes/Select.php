<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Select extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => "" );
		$this->default_options = array ("default" => array ("type" => "text", "value" => "", "label" => "Select items(each string is item)" ), "search_enabled" => true, "isearch_enabled" => false );
		$this->text_search = true;
		$this->index_search = false;
	}
	
	function ValidateOptions($array) {
		//$err_status = parent::ValidateOptions($array);
		return $err_status;
	}
	// ///////////////////////////
	function ValidatePostField($array = NULL, $data = NULL) {
		if (! $array)
			return "<div class=\"error\"><p>This or that went wrong</p></div>";
		$options = unserialize ( $array ['options'] );
		return true;
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
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
}
?>