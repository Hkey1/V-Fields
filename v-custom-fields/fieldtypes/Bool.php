<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Bool extends FieldType {
	
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => false );
		$this->text_search = false;
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
		// return "<div class=\"error\"><p>This or that went wrong</p></div>";
		return true;
	}
	
	// Функция загрузки полей
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( 'Bool' );
		// Если вызываем функции для загрузки опций существующего поля
		if ($load_type == "load") {
			if ($data != NULL) {
				$options = $data ['options'];
				if (! strlen ( $options ))
					return 0;
				$array = unserialize ( $options );
				$select = $array ['select'];
				$name = $array ['fieldname'];
				$value = $array ['default'];
				$value ? $checked = "checked=\"true\"" : $checked = "";
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
				Default is checked?</br>
				<input type=\"checkbox\" name=\"default\" $checked value=\"1\" />
				</div>
				</div>";
			}
		} else {
			$this->default_options ['default'] ? $checked = "checked=\"true\"" : $checked = "";
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
						Default is checked?</br>
						<input type=\"checkbox\" name=\"default\" $checked value=\"1\" />
					</div>
					</div>";
			
			} elseif ($load_type == "change") {
				$str = "
						Default is checked?</br>
						<input type=\"checkbox\" name=\"default\" $checked value=\"1\" />";
			}
		}
		return $str;
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
	// /////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "Default is checked?</br>
				<input type=\"checkbox\" name=\"checked\" value=\"1\" />";
		return $str;
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