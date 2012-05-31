<?php
include ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-abstract-fieldtype.php');

class String extends FieldType {
	
	/////////////////
	private $maxlength = 40;
	private $default = "";
	/////////////////
	

	//Методы доступа/
	function get_maxlength() {
		return $this->maxlength;
	}
	function get_default() {
		return $this->default;
	}
	/////////////////
	//Валидатор////
	function ValidateOptions($array) {
		$err_status = 0;
		$str = iconv ( "utf-8", "windows-1251", $array ['fieldname'] );
		if (strlen ( $str ) > 20) {
			echo "<strong>NAME'S LENGTH IS MORE THAN 20 SYMBOLS</strong></br>";
			$err_status ++;
		}
		if (! preg_match ( "/^[0-9a-zA-ZА-я\-_ \s]+$/", $str )) {
			echo "<strong>NOT VALID STRING \"" . $array ['fieldname'] . "\". ONLY DIGITS, SPACES AND UNDERLINES AVAILIBLE</strong></br>";
			$err_status ++;
		}
		if (! is_numeric ( $array ['max_length'] )) {
			echo "<strong>IS NOT NUMERIC \"" . $array ['max_length'] . "\"</strong></br>";
			$err_status ++;
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
		if(!strlen(trim($data)))
		{
			return false;
		}
		if(strlen($data) > $options['max_length'])
		{
			return "<div class=\"error\"><p>\"".$array['name']."\" is longer than \"".$options['max_length']."\" symbols.</p></div>";
		}
		return true;		
	}
	//////////////////////
	function NewOptions() {
		$str = "<div class=\"section\">
				$this->head
				<div class=\"field_settings\">
					<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->get_maxlength () . "\"/></p>					
					<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"" . $this->get_default () . "\" /></p>
					<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>
				</div>
			</div>";
		return $str;
	}
	//Сохранение нового поля
	function SaveOptions($array) {
		$err_status = $this->ValidateOptions ( $array );
		if ($err_status)
			return $err_status;
		else {
			global $db,$wpdb;
			$fieldtype = $array ['select'];
			$result = mysql_query ( "SELECT id FROM  ".$wpdb->prefix."v_field_types WHERE name='$fieldtype'", $db );
			$fieldid = mysql_result ( $result, 0 );
			$name = $array ['fieldname'];
			$translit = translit ( $name );
			$result2 = mysql_query ( "SELECT name FROM  ".$wpdb->prefix."v_field_options WHERE name='$name'", $db );
			if (strlen ( mysql_result ( $result2, 0 ) ))
				return 0;
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
		$array = unserialize ( $options );
		$select = $array ['select'];
		$name = $array ['fieldname'];
		$max_length = $array ['max_length'];
		$default = $array ['default'];
		$selection = make_select_list ( $select );
		$search = $array['search'];
		if($search == 1)
		{
			$search_checked = "checked=\"true\"";
		}
		else
			$search_checked = "";
		$str = "<div class=\"section\">
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
		return $str;
	}
	//Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$max_length = $array ['max_length'];
		$name = $array ['fieldname'];
		$value = $array ['default'];
		if ($post_id) {
			global $db,$wpdb;
			$query_result = mysql_query ( "SELECT data FROM  ".$wpdb->prefix."v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! $value)
				$value = "";
		}	

		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<input type=\"text\" name=\"$translit\"  maxlength=\"$max_length\" value=\"$value\"/></br></br>";
		
		return $result;
	}
	///////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "<p>Max Length:<br/> <input type=\"text\" name=\"max_length\" class=\"default\" value=\"" . $this->get_maxlength () . "\"/></p>
		<p>Default:<br/> <input type=\"text\" name=\"default\" value=\"\" /></p>
		<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>";
		return $str;
	}
	////////////////////////////////////////////////////////////////////
	function Out($array = NULL,$params = NULL)
	{
		if(!$array)
			return;
		$result = "";
		$id = $array['id'];
		$data = $array['data'];
		$options = unserialize($array['options']);
		$max_length = $options['max_length'];
		if($this->user_level != 10)
		{
			$result .= "<span>".$array['data']."</span></br>";
			$result .= "</br>";
			return $result;
		}
		$result .= "<span id=\"$id\" class=\"string\">".$array['data']."</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<input type=\"text\" maxlength=$max_length value=\"$data\">
		</div>";
		$result .= "</br>";
		return $result;
	}
	///////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid,$data,$params = NULL)
	{		
		if ($this->user_level != 10)
			return;
		global $wpdb;
		$options_result = $wpdb->get_row("SELECT options FROM  ".$wpdb->prefix."v_field_options WHERE translit = (SELECT translit FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid)");
		$options = unserialize($options_result->options);
		if(strlen($data) > $options['max_length'] )
		{
			$result = $wpdb->get_results("SELECT data FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
			echo $result[0]['data'];
			return;
		}
		$wpdb->query("UPDATE v_fields SET data = '$data' WHERE id = $fieldid");
		//Обновляем метаданные вордпресса
		$result = $wpdb->get_results("SELECT translit,post_id FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
		$fieldname = $result[0]['translit'];
		$post_id = $result[0]['post_id'];
		if(ctype_digit($post_id) && strlen($fieldname))
			update_post_meta($post_id,$fieldname,$data);
		//
		echo $data;
	}
	///////////////////////////////////////////////////////////////////////////
	function Mysql_Where($pieces = NULL,$param = NULL,$value = NULL)
	{
		return $pieces;
	}
}
?>