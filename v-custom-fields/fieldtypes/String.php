<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class String extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("max_length" => array ("type" => "int", "value" => 1000, "label" => "Max length" ), "default" => array ("type" => "string", "value" => "", "label" => "Default" ), "search_enabled" => true, "isearch_enabled" => false );
		$this->text_search = true;
		$this->index_search = false;
	}
	function ValidateOptions($array) {
		$err_status = parent::ValidateOptions($array);
		return $err_status;
	}
	
	// ///////////////////////////
	function ValidatePostField($array = NULL, $data = NULL) {
		if (! $array)
			return "<div class=\"error\"><p>This or that went wrong</p></div>";
		$options = unserialize ( $array ['options'] );
		if (! strlen ( trim ( $data ) )) {
			return false;
		}
		if (strlen ( $data ) > $options ['max_length']) {
			return "<div class=\"error\"><p>\"" . $array ['name'] . "\" is longer than \"" . $options ['max_length'] . "\" symbols.</p></div>";
		}
		return true;
	}
	
	
	// Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$max_length = $array ['max_length'];
		$name = $array ['fieldname'];
		$value = $array ['default'];
		if ($post_id) {
			global $db, $wpdb;
			$query_result = mysql_query ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! $value)
				$value = "";
		}
		
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<input type=\"text\" name=\"$translit\"  maxlength=\"$max_length\" value=\"$value\"/></br></br>";
		
		return $result;
	}
	// /////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->get_maxlength () . "\"/></p>
		<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"\" /></p>
		<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>";
		return $str;
	}
	// //////////////////////////////////////////////////////////////////
	function Out($array = NULL, $params = NULL) {
		if (! $array)
			return;
		$result = "";
		$id = $array ['id'];
		$data = $array ['data'];
		$options = unserialize ( $array ['options'] );
		$max_length = $options ['max_length'];
		if ($this->user_level != 10) {
			$result .= "<span>" . $array ['data'] . "</span></br>";
			$result .= "</br>";
			return $result;
		}
		$result .= "<span id=\"$id\" class=\"string\">" . $array ['data'] . "</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<input type=\"text\" class=\"edit_string\" maxlength=$max_length value=\"$data\">
		</div>";
		$result .= "</br>";
		return $result;
	}
	// /////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid, $data, $params = NULL) {
		if ($this->user_level != 10)
			return;
		global $wpdb;
		$options_result = $wpdb->get_row ( "SELECT options FROM  " . $wpdb->prefix . "v_field_options WHERE translit = (SELECT translit FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid)" );
		$options = unserialize ( $options_result->options );
		if (strlen ( $data ) > $options ['max_length']) {
			$result = $wpdb->get_results ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
			echo $result [0] ['data'];
			return;
		}
		$wpdb->query ( "UPDATE v_fields SET data = '$data' WHERE id = $fieldid" );
		// Обновляем метаданные вордпресса
		$result = $wpdb->get_results ( "SELECT translit,post_id FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
		$fieldname = $result [0] ['translit'];
		$post_id = $result [0] ['post_id'];
		if (ctype_digit ( $post_id ) && strlen ( $fieldname ))
			update_post_meta ( $post_id, $fieldname, $data );
			//
		echo $data;
	}
	// /////////////////////////////////////////////////////////////////////////
	// Весь остальной поиск. cf_field_less=value,cf_field_more=value
	function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL) {
		return $pieces;
	}
}
?>