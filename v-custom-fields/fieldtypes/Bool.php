<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Bool extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => array ("type" => "bool", "value" => false, "label" => "Is checked default" ), "search_enabled" => true, "isearch_enabled" => false );
		$this->text_search = false;
		$this->index_search = false;
	}
	function ValidateOptions($array) {
		$err_status = parent::ValidateOptions($array);
		return $err_status;
	}
	// ///////////////////////////
	function ValidatePostField($array = NULL, $data = NULL) {
		return true;
	}
	
	// Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$name = $array ['fieldname'];
		$value = $array ['default'];
		if ($post_id) {
			global $db, $wpdb;
			$query_result = mysql_query ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! $value)
				$value = NULL;
		}
		// Если статья ещё не создана т.е. ещё нету сохраненных значений полей
		// то выводим дефолтные значение
		if ($value)
			$checked = "checked=\"true\"";
		else
			$checked = "";
		
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<input type=\"checkbox\" name=\"$translit\" $checked  value=\"1\"/></br></br>";
		
		return $result;
	}
	
	// //////////////////////////////////////////////////////////////////
	function Out($array = NULL, $tostring = NULL) {
		$id = $array ['id'];
		$data = $array ['data'];
		$options = unserialize ( $array ['options'] );
		if ($this->user_level != 10) {
			if (! $data)
				echo "<span><input  type=\"checkbox\" id=\"$id\" disabled=\"true\" /></span>";
			else
				echo "<span><input  type=\"checkbox\" id=\"$id\" checked=\"true\" disabled=\"true\" /></span>";
			return;
		} else {
			if (! $data) {
				echo "<span><input class=\"edit_bool\" id=\"$id\" class=\"bool\" value=\"0\" type=\"checkbox\" id=\"$id\"   /></span>";
			} else {
				echo "<span><input class=\"edit_bool\" id=\"$id\" class=\"bool\" value=\"1\" type=\"checkbox\" id=\"$id\" checked=\"true\"  /></span>";
			}
			echo "</br>";
		}
		echo "</br>";
	}
	// /////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid, $data, $params = NULL) {
		echo $fieldid . " __ " . $data;
		
		if ($this->user_level != 10)
			return;
		if ($data == 0 || $data == 1) {
			global $wpdb;
			$wpdb->query ( "UPDATE  " . $wpdb->prefix . "v_fields SET data = '$data' WHERE id = $fieldid" );
		}
	}
	// /////////////////////////////////////////////////////////////////////////
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
}
?>