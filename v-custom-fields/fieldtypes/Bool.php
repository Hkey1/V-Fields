<?php
include ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-abstract-fieldtype.php');

class Bool extends FieldType {
	private $default = 0;
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
		return $err_status;
	}
	function ValidateField() {
		return 0;
	}
	/////////////////////////////
	function ValidatePostField($array = NULL,$data = NULL)
	{
		//return "<div class=\"error\"><p>This or that went wrong</p></div>";
		return true;		
	}
	//////////////////////
	function NewOptions() {
		$str = "<div class=\"section\">
				$this->head
				<div class=\"field_settings\">
				 Default is checked?</br>
				<input type=\"checkbox\" name=\"default\" value=\"1\" />
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
		$value = $array ['default'];
		if ($value)
			$checked = "checked=\"true\"";
		else
			$checked = "";
		$selection = make_select_list ( $select );
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
				Default is checked?</br>
				<input type=\"checkbox\" name=\"default\" $checked value=\"1\" />
				</div>
			</div>";
		return $str;
	}
	//Функция вывода кастомного поля в создании/редактировании поста
	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$name = $array ['fieldname'];
		$value = $array ['default'];
		if ($post_id) {
			global $db,$wpdb;
			$query_result = mysql_query ( "SELECT data FROM  ".$wpdb->prefix."v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (! $value)
				$value = NULL;
		}
		//Если статья ещё не создана т.е. ещё нету сохраненных значений полей то выводим дефолтные значение
		if ($value)
			$checked = "checked=\"true\"";
		else
			$checked = "";
		
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<input type=\"checkbox\" name=\"$translit\" $checked  value=\"1\"/></br></br>";
		
		return $result;
	}
	///////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "Default is checked?</br>
				<input type=\"checkbox\" name=\"checked\" value=\"1\" />";
		return $str;
	}
	////////////////////////////////////////////////////////////////////
	function Out($array = NULL,$tostring = NULL)
	{
		$id = $array['id'];
		$data = $array['data'];
		$options = unserialize($array['options']);
		if($this->user_level != 10)
		{
			if(!$data)
				echo "<span><input type=\"checkbox\" id=\"$id\" disabled=\"true\" /></span>";
			else 	
				echo "<span><input type=\"checkbox\" id=\"$id\" checked=\"true\" disabled=\"true\" /></span>";
			return;
		}
		else
		{
			if(!$data)
			{
				echo "<span><input id=\"$id\" class=\"bool\" value=\"0\" type=\"checkbox\" id=\"$id\"   /></span>";
			}
			else
			{
				echo "<span><input id=\"$id\" class=\"bool\" value=\"1\" type=\"checkbox\" id=\"$id\" checked=\"true\"  /></span>";
			} 	
			echo "</br>";
		}
		echo "</br>";
	}
	///////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid,$data,$params = NULL)
	{
		echo $fieldid." __ ".$data;
				
		if ($this->user_level != 10)
			return;
		if($data == 0 || $data ==1)
		{
			global $wpdb;
			$wpdb->query("UPDATE  ".$wpdb->prefix."v_fields SET data = '$data' WHERE id = $fieldid");	
		}			
	}
	///////////////////////////////////////////////////////////////////////////
	function Mysql_Where($pieces = NULL,$param = NULL,$value = NULL)
	{
		return $pieces;
	}
}
?>