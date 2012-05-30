<?php
include ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-abstract-fieldtype.php');

class Select extends FieldType {
	/////////////////
	private $default = "Select Value";
	/////////////////
	

	//Методы доступа/
	function get_default() {
		return $this->default;
	}
	/////////////////
	//Валидатор////
	function ValidateOptions($array) {
		$err_status = 0;
		$str = iconv("utf-8", "windows-1251", $array['fieldname']);
		if (strlen ( $str ) > 20) {
			echo "<strong>NAME'S LENGTH IS MORE THAN 20 SYMBOLS</strong></br>";
			$err_status ++;
		}
			if (! preg_match ( "/^[0-9a-zA-ZА-я\-_ \s]+$/", $str )) {
			echo "<strong>NOT VALID STRING \"" . $array ['fieldname'] . "\". ONLY DIGITS, SPACES AND UNDERLINES AVAILIBLE</strong></br>";
			$err_status ++;
		}
		$select_items = explode ( "\n", $array ['default'] );
		foreach ( $select_items as $row ) {
			if (strlen ( $row ) > 20) {
				echo $row . " TOO LONG</br>";
				$err_status ++;
			}
		}
		return $err_status;
	}
	function ValidateField() {
		return 0;
	}
	/////////////////////////////
	function ValidatePostField($array = NULL,$data = NULL)
	{
		if(!$array)
			return "<div class=\"error\"><p>This or that went wrong</p></div>";
		$options = unserialize($array['options']);
		return true;
	}
	//////////////////////
	function NewOptions() {
		$str = "<div class=\"section\">
				$this->head
				<div class=\"field_settings\">
					<p>Select items(each string is item):<br/> <textarea name=\"default\" cols=80 rows=10></textarea></p>
					<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>
				</div>
			</div>";
		return $str;
	}
	//Сохранение нового поля
	function SaveOptions($array) {
		$err_status = $this->ValidateOptions ( $array );
		if ($err_status)
			return 1;
		else {
			global $db,$wpdb;
			$fieldtype = $array ['select'];
			$result = mysql_query ( "SELECT id FROM  ".$wpdb->prefix."v_field_types WHERE name='$fieldtype'", $db );
			$fieldid = mysql_result ( $result, 0 );
			$name = $array ['fieldname'];
			$translit = translit ( $name );
			$result2 = mysql_query ( "SELECT name FROM  ".$wpdb->prefix."v_field_options WHERE name='$name'", $db );
			if (strlen ( mysql_result ( $result2, 0 ) ))
				return 1;
			$options = serialize ( $array );
			$isearch = $array['isearch'];
			if(strlen($isearch))
				$isearch = 1;
			else
				$isearch = 0;	
			mysql_query ( "INSERT INTO  ".$wpdb->prefix."v_field_options (fieldtype,name,translit,options,isearch) VALUES($fieldid,'$name','$translit','$options',$isearch)" );
			return 0;
		}
	}
	
	//Функция загрузки полей
	function LoadOptions($data) {
		$options = $data ['options'];
		if (! strlen ( $options ))
			return 0;
		$id = $data ['id'];
		$array = unserialize ( $options );
		$select = $array ['select'];
		$name = $array ['fieldname'];
		$default = $array ['default'];
		$search = $array['search'];
		if($search == 1)
		{
			$search_checked = "checked=\"true\"";
		}
		else
			$search_checked = "";
		$selection = make_select_list ( $select );
		$str = "<div class=\"section\">
				<h3>
				     <span class=\"container\">
				     	<input type=\"hidden\" name=\"hr\" value=\"true\">
				     	<input type=\"hidden\" name=\"field_post_connect\" value=\"$field_post_connect\">
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
		return $str;
	}
	//Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$select_str = $array ['default'];
		$value = -1;
		$name = $array ['fieldname'];
		if ($post_id) {
			global $db,$wpdb;
			$value = 0;
			$query_result = mysql_query ( "SELECT data FROM  ".$wpdb->prefix."v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! is_numeric ( $value ))
				$value = -1;
		}
		$select_list = explode ( "\n", $select_str );
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<select name=\"$translit\">";
		if($value == -1)
		{
			$i = 0;
			$result = $result . "<option value=\"-1\" selected=\"true\"><b>select item</b></option>";
			foreach ( $select_list as $row ) {
			$result = $result . "<option value=\"$i\">" . $row . "</option>";
			$i ++;
		}			
		}
		else
		{
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
	///////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "<p>Select items(each string is item):<br/> <textarea name=\"default\" cols=80 rows=10>" . $this->get_default () . "</textarea></p>
				<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>";
		return $str;
	}
	////////////////////////////////////////////////////////////////////
	////////////////////////////////////////////////////////////////////
	function Out($array = NULL,$params = NULL)
	{
		global $wpdb;
		$id = $array['id'];
		$key = $array['data'];
		if($key == -1)
			return;
		$result =  "";
		$options = unserialize($array['options']);
		$select_values = explode("\n",$options['default']);
		if($this->user_level != 10)
		{
			$result .= "<span>".$select_values[$key]."</span></br>";
			$result .= "</br>";
			return $result;
		}
		$selection = "<select>";
		$i = 0;
		foreach ( $select_values as $row ) {
			if ($i == $key)
				$selection = $selection . "<option value=\"$i\" selected=\"true\">" . $row . "</option>";
			else
				$selection = $selection . "<option value=\"$i\">" . $row . "</option>";
			$i ++;
		}
		$selection = $selection."</select>";		
		$result .= "<span id=\"$id\" class=\"select\">".$select_values[$key]."</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<input type=\"hidden\" id=\"selected\" value=\"$key\" />
		$selection
		</div>";
		$result .= "</br>";
		return $result;
	}
	///////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid,$data)
	{
				
		if ($this->user_level != 10)
			return;
		global $wpdb;
		$options_result = $wpdb->get_row("SELECT options FROM  ".$wpdb->prefix."v_field_options WHERE translit = (SELECT translit FROM v_fields WHERE id = $fieldid)");
		$options = unserialize($options_result->options);
		$select_values = explode("\n",$options['default']);
		if($data > count($select_values))
			return;
		$wpdb->query("UPDATE  ".$wpdb->prefix."v_fields SET data = '$data' WHERE id = $fieldid");
		//Обновляем метаданные вордпресса
		$result = $wpdb->get_results("SELECT translit,post_id FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
		$fieldname = $result[0]['translit'];
		$post_id = $result[0]['post_id'];
		if(ctype_digit($post_id) && strlen($fieldname))
			update_post_meta($post_id,$fieldname,$data);
		//
		echo $select_values[$data];
	}
	////////////////////////////////////////////////////////////////////////
	function Mysql_Where($pieces = NULL,$param = NULL,$value = NULL)
	{
		return $pieces;
	}
}
?>