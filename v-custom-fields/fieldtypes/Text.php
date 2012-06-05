<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Text extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => "", "max_length" => 1000, "wysiwyg" => false );
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
	
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( 'Text' );
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
				$wysiwyg = $array ['html_editor'];
				($search == 1) ? $search_checked = "checked=\"true\"" : $search_checked = "";
				($wysiwyg == 1) ? $wysiwyg_checked = "checked=\"true\"" : $wysiwyg_checked = "";
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
						<p>Default:<br/> <textarea name=\"default\" cols=80 rows=10>" . $default . "</textarea></p>
						<p>Wysiwyg: 
							<input type=\"checkbox\" name=\"html_editor\"  class=\"html_editor\" value=\"1\" $wysiwyg_checked />
						</p>
						<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
					</div>
				</div>";
			}
		} else {
			$this->text_search ? $search_checked = "checked=\"true\"" : $search_checked = "";
			$this->default_options ['wysiwyg'] ? $wysiwyg_checked = "checked=\"true\"" : $wysiwyg_checked = "";
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
				<p>Default:<br/> <textarea name=\"default\" cols=80 rows=10>" . $this->default_options ['default'] . "</textarea></p>
				<p>Wysiwyg:
				<input type=\"checkbox\" name=\"html_editor\"  class=\"html_editor\" value=\"1\" $wysiwyg_checked />
				</p>
				<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
				</div>
				</div>";
			
			} elseif ($load_type == "change") {
				$str = "				
				<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->default_options ['max_length'] . "\"/></p>
				<p>Default:<br/> <textarea name=\"default\" cols=80 rows=10>" . $this->default_options ['default'] . "</textarea></p>
				<p>Wysiwyg:
				<input type=\"checkbox\" name=\"html_editor\"  class=\"html_editor\" value=\"1\" $wysiwyg_checked />
				</p>
				<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>";
			}
		}
		return $str;
	}
	
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
		if ($array ['html_editor'] == '1')
			$result = $result . "<script>$(document).ready(function()
		{
		tinyMCE.init({
		mode : \"textareas\",
        theme : \"advanced\",
        theme_advanced_toolbar_location : \"top\",
        elements: \"$translit\",
        mode: \"exact\"			
		});
		});</script>";
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<textarea name=\"$translit\" cols=156 rows=6 maxlength=$max_length>$value</textarea></br></br>";
		return $result;
	}
	
	function Out($array = NULL, $params = NULL) {
		if (! $array)
			return;
		$result = "";
		$id = $array ['id'];
		$data = $array ['data'];
		$options = unserialize ( $array ['options'] );
		$max_length = $options ['max_length'];
		$formatted = "";
		// if($options['view'] == '0' || $options['view'] == '3')
		// $formatted = $array['data'];
		if ($params ['view'] == 'list') {
			$content = explode ( "\n", $array ['data'] );
			foreach ( $content as $row ) {
				$formatted .= "<ul>";
				$formatted .= "<li>" . $row . "</li>";
				$formatted .= "</ul>";
			}
		}
		if ($params ['view'] == 'br') {
			$content = explode ( "\n", $array ['data'] );
			foreach ( $content as $row ) {
				$formatted .= $row . "</br>";
			}
		}
		if ($this->user_level != 10) {
			$result .= "<span>" . $formatted . "</span></br>";
			$result .= "</br>";
			return $result;
		}
		$result .= "<span id=\"$id\" class=\"text\">" . $formatted . "</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<textarea cols=10 rows=5 class=\"edit_text\" maxlength=$max_length>" . $array ['data'] . "</textarea>
		<input type=\"hidden\" class=\"backup_value\" value=\"" . $array ['data'] . "\" />
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
		$max_length = $options ['max_length'];
		$formatted = "";
		if ($options ['view'] == '0' || $options ['view'] == '3')
			$formatted = $data;
		if ($options ['view'] == '1') {
			$content = explode ( "\n", $data );
			foreach ( $content as $row ) {
				$formatted .= "<ul>";
				$formatted .= "<li>" . $row . "</li>";
				$formatted .= "</ul>";
			}
		}
		if ($options ['view'] == '2') {
			$content = explode ( "\n", $data );
			foreach ( $content as $row ) {
				$formatted .= $row . "</br>";
			}
		}
		// ////
		echo $formatted;
	}
	
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