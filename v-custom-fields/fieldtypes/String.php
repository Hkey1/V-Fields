<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class String extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => "", "max_length" => 300, "wysiwyg" => false );
		$this->text_search = true;
		$this->index_search = false;
	}
	function ValidateOptions($array) {
		$err_status = "";
		$err_status .= parent::Validate ( $array ['fieldname'], "length", 20 );
		$err_status .= parent::Validate ( $array ['fieldname'], "string" );
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
	
	// Функция загрузки полей
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( 'String' );
		// Если вызываем функции для загрузки опций существующего поля
		if ($load_type == "load") {
			if ($data != NULL) {
				$options = $data ['options'];
				if (! strlen ( $options ))
					return 0;
				$array = unserialize ( $options );
				$select = $array ['select'];
				$name = $array ['fieldname'];
				$max_length = $array ['max_length'];
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
						<select class=\"select\" name=\"select\">" . $selection . "</select>
						<span class=\"ui-icon ui-icon-closethick\"></span>
					</span>
					</span>
				</h3>
				<div class=\"field_settings\">
					<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $max_length . "\"/></p>					
					<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"$default\" /></p>
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
					<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->default_options ['max_length'] . "\"/></p>					
					<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"" . $this->default_options ['default'] . "\" /></p>
					<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
				</div>
				</div>";
			
			} elseif ($load_type == "change") {
				$str = "					
			<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->default_options ['max_length'] . "\"/></p>					
			<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"" . $this->default_options ['default'] . "\" /></p>
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