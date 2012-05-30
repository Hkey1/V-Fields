<?php
include ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-abstract-fieldtype.php');

class Digit extends FieldType {
	/////////////////
	private $default = "0";
	private $min = "0";
	private $max = "1000";
	private $step = "10";
	/////////////////
	


	function get_default() {
		return $this->default;
	}
	function get_min() {
		return $this->min;
	}
	function get_max() {
		return $this->max;
	}
	function get_step() {
		return $this->step;
	}
	/////////////////

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
		if (! is_numeric ( $array ['max'] )) {
			echo "<strong>IS NOT NUMERIC \"" . $array ['max'] . "\"</strong></br>";
			$err_status ++;
		}
		if (! is_numeric ( $array ['min'] )) {
			echo "<strong>IS NOT NUMERIC \"" . $array ['min'] . "\"</strong></br>";
			$err_status ++;
		}
		if (! is_numeric ( $array ['step'] )) {
			echo "<strong>IS NOT NUMERIC \"" . $array ['step'] . "\"</strong></br>";
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
			return false;
		if(!is_numeric($data))
		{
			$data = $options['default'];
			return "<div class=\"error\"><p>\"".$array['name']."\" is not digit. Will be used default value \"".$options['default']."\".</p></div>";			
		}

		if($data > $options['max'])
		{
			$data = $options['default'];
			return "<div class=\"error\"><p>\"".$array['name']."\" is more than max. Will be used default value \"".$options['default']."\".</p></div>";			
		}

		if($data < $options['min'])
		{
			$data = $options['default'];
			return "<div class=\"error\"><p>\"".$array['name']."\" is less than min. Will be used default value \"".$options['default']."\".</p></div>";			
		}
		return true;
	}
	//////////////////////
	function NewOptions() {
		$str = "<div class=\"section\">
				$this->head
				<div class=\"field_settings\">
					<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"" . $this->get_default () . "\"/></p>
					<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"" . $this->get_step () . "\"/></p>
					<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"" . $this->get_min () . "\"/></p>
					<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"" . $this->get_max () . "\"/></p>
					<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>
					<p><b>index search</b> <input type=\"checkbox\" name=\"isearch\" checked=\"checked\" value=\"1\" /></p>
					</div>
			</div>";
		return $str;
	}

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
	

	function LoadOptions($data) {
		$options = $data ['options'];
		if (! strlen ( $options ))
			return 0;
		$id = $data ['id'];
		$array = unserialize ( $options );
		$select = $array ['select'];
		$name = $array ['fieldname'];
		$min = $array ['min'];
		$max = $array ['max'];
		$step = $array ['step'];
		$default = $array ['default'];
		$search = $array['search'];
		$isearch = $array['isearch'];
		if($search == 1)
		{
			$search_checked = "checked=\"true\"";
		}
		else
			$search_checked = "";
		if($isearch == 1)
		{
			$isearch_checked = "checked=\"true\"";
		}
		else
			$isearch_checked = "";
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
					<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"$default\"/></p>
					<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"$step\"/></p>
					<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"$min\"/></p>
					<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"$max\"/></p>
					<p><b>text search</b> <input type=\"checkbox\" name=\"search\" value=\"1\" $search_checked /></p>
					<p><b>index search</b> <input type=\"checkbox\" name=\"isearch\" value=\"1\" $isearch_checked /></p>			
				</div>
			</div>";
		return $str;
	}

	function OutField($array, $post_id = 0) {
		$translit = $array ['translit'];
		$array = unserialize ( $array ['options'] );
		$result = "";
		$min = $array ['min'];
		$max = $array ['max'];
		$value = $array ['default'];
		if (! is_numeric ( $value ))
			$value = 0;
		$step = $array ['step'];
		$name = $array ['fieldname'];
		if ($post_id) {
			global $db,$wpdb;
			$value = 0;
			$query_result = mysql_query ( "SELECT data FROM  ".$wpdb->prefix."v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if(strlen(trim($value)) == 0)
				$value = $array['default'];
		}

		$result = "<script>
						$(document).ready(function(){
						$.spin.imageBasePath = '/wp-content/plugins/v-custom-fields/img/spin2/';
						$('#$translit').spin({
							max: $max,
							min: $min,
							interval: $step
						});						
						$(\"#$translit\").bind(\"mousewheel\", function(event, delta)
						{
						  if (delta > 0)
						  {
						    this.value = parseInt(this.value) + $step;
						  }
						  else 
						  {
						    this.value = parseInt(this.value) - $step;
						  }
						  if(this.value > $max)
						  {
						    this.value = $max;
						    return;	
						  }
						  if(this.value < $min)
						  {
							this.value = $min;
							return;	
						  }
						  return false;
						});						
					    });
  			         </script>";
		$result = $result . "<label for=\"$translit\"><b>$name</b></label></br>";
		$result = $result . "<input type=\"input\" id=\"$translit\" name=\"$translit\" value=\"$value\" /></br></br>";		
		return $result;
	}
	///////////////////////////////////////////////////////////////////
	function ChangeType() {
		$str = "<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"" . $this->get_default () . "\"/></p>
				<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"" . $this->get_step () . "\"/></p>
				<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"" . $this->get_min () . "\"/></p>
				<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"" . $this->get_max () . "\"/></p>
				<p><b>text search</b> <input type=\"checkbox\" name=\"search\" checked=\"checked\" value=\"1\" /></p>
				<p><b>index search</b> <input type=\"checkbox\" name=\"isearch\" checked=\"checked\" value=\"1\" /></p>
				";
		
		return $str;
	}
	////////////////////////////////////////////////////////////////////
	function make_dec_div($params,$value)
	{
		$data = $value;
		if($params['division'] == "1")
			{
				if(ctype_digit($params['dec']))
				{
					$del = 1;
					if($params['dec'] == "1")
						$del = 10;
					if($params['dec'] == "2")
						$del = 100;
				    if($params['dec'] == "3")
						$del = 1000;
					if($params['dec'] == "4")
						$del = 10000;
					if($params['dec'] == "5")
						$del = 100000;
					$data = floor($data*$del)/$del;
					$data = number_format($data,$params['dec'],"."," ");
					if(ctype_digit($value))
						$data = $value;
				}
			}
			else 
			{
				if($params['dec'] != "0")
				{
					$del = 1;
					if($params['dec'] == "1")
						$del = 10;
					if($params['dec'] == "2")
						$del = 100;
				    if($params['dec'] == "3")
						$del = 1000;
					if($params['dec'] == "4")
						$del = 10000;
					if($params['dec'] == "5")
						$del = 100000;
					$data = floor($data*$del)/$del;			
					$data = number_format($data,$params['dec'],".","");
					if(ctype_digit($value))
						$data = $value;
				}
			}
			//////Спряжения
			$declensions = explode("\n",$params['declensions']);
			//Если у нас три формы заданы в админке
			if(count($declensions) == 3)
			{
				//Если дробь то вторая форма
				if(strpos($value,"."))
				{
					$data .= " ".$declensions[1];
				}
				else 
				{
					//Вытягиваем последние две цифры из числа
					$last_digits = substr($value,strlen($value)-2);
					//Вытягиваем последнюю цифрц числа
					$last_digit = $value%10;
					//При данном условии выводим в третей форме
					if((intval($last_digits) >= 11 && intval($last_digits) <= 19) ||
					(intval($last_digit) >=5 && intval($last_digit) <=9 ) || intval($last_digit) == 0)
						$data .= " ".$declensions[2];
					else
					{
						//Если же число = 1,или заканчивается на 1(но не на 11), то выводим в первой форме
						//Во всех остальных случаях выводим во второй форме
						if(intval($value) == 1 || $last_digits%10 == 1 )
							$data .= " ".$declensions[0];
						else
							$data .= " ".$declensions[1];
					}
				}	
			}
			//Если две формы заданы в админке
			elseif(count($declensions) == 2)
			{
				//Если число = 1, то первая форма, в противном случае всегда во второй
				if(intval($value) == 1)
				   $data .= "  ".$declensions[0];
				else 
				   $data .= "  ".$declensions[1];	
			}
			return $data;		
	}
	////////////////////////////////////////////////////////////////////
	function Out($array = NULL,$params = NULL)
	{
		if(!$array)
			return;
		$id = $array['id'];
		$data = $array['data'];
		$options = unserialize($array['options']);
		///Количество цифр дробной части
		$data = $this->make_dec_div($options, $array['data']);
		//////Конец спряжениям
		
		$result = "";
		if($this->user_level != 10)
		{
			$result .= "<span>".$data."</span></br>";
			$result .= "</br>";
			return $result;
		}
		$edit_data = $array['data'];		
		$result .= "<span id=\"$id\" class=\"digit ui-widget-content ui-widget-shadow  ui-corner-all\">".$data."</span>";
		$result .= "<div style=\"display:none;\" class=\"$id\" title=\"Edit value\">
		<input type=\"text\" value=\"$edit_data\">
		</div>";
		$result .= "</br>";
		return $result;
	}
	//////////////////////////////////////////////////////////////////////
	function UpdateField($fieldid,$data)
	{
		if ($this->user_level != 10)
			return;
		global $wpdb;
		if (!is_numeric($data))
		{
			$result = $wpdb->get_results("SELECT data FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
			echo $result[0]['data'];
			return;			
		}
		$options_result = $wpdb->get_row("SELECT options FROM  ".$wpdb->prefix."v_field_options WHERE translit = (SELECT translit FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid)");
		$options = unserialize($options_result->options);
		if($data > $options['max'] || $data < $options['min'])
		{
			$result = $wpdb->get_results("SELECT data FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
			echo $result[0]['data'];
			return;
		}
		$wpdb->query("UPDATE  ".$wpdb->prefix."v_fields SET data = '$data' WHERE id = $fieldid");
		//Обновляем метаданные вордпресса
		$result = $wpdb->get_results("SELECT translit,post_id FROM  ".$wpdb->prefix."v_fields WHERE id = $fieldid LIMIT 1",ARRAY_A);
		$fieldname = $result[0]['translit'];
		$post_id = $result[0]['post_id'];
		if(ctype_digit($post_id) && strlen($fieldname))
			update_post_meta($post_id,$fieldname,$data);
		//
		$data = $this->make_dec_div($options, $data);
		echo $data;
	}
	//////////////////////////////////////////////////////////////////////	
	function Mysql_Where($pieces = NULL,$param = NULL,$value = NULL)
	{
		global $wpdb;
		//$value = (int)$value;
		if($pieces == NULL)
			return;
			$field_param = array();
			/*
			 * Парсим $param...Он представлен в виде cf_xxx_xxx....Вытягиваем имя поля и условие поиска
			 * Проверяем существует ли такое имя. Если существует то в зависимости от условия формируем WHERE
			 * */	 
			if(preg_match('/^cf_(.*)_(.*)/i', $param,$field_param))
			{
				$fieldname = strtolower($field_param[1]);
				$result = $wpdb->get_results("SELECT * FROM  ".$wpdb->prefix."v_field_options WHERE name = '$fieldname'",OBJECT);
				$condition = strtolower($field_param[2]);
				//Если fieldname в GET строке действительно существует, то применяем фильтры
				if(is_object($result[0]))
				{
						switch($condition)
						{
							case "more":
								if(!is_numeric($value))
									break;
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value > '$value')";
								break;
							case "less":
								if(!is_numeric($value))
									break;
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value < '$value')";								
								break;
							case "from":
								if(!is_numeric($value))
									break;
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value >= '$value')";								
								break;
							case "to":
								if(!is_numeric($value))
									break;
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value <= '$value')";								
								break;
							case "no":
								if(is_numeric($value))									
									$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value <> '$value')";								
								else 
								{
									$values = explode("||",$value);
									if(is_numeric($values[0]) || is_numeric($values[1]))
									{
										$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value <> '$values[0]' AND  ".$wpdb->prefix."postmeta.meta_value <> '$values[1]' )";
									}
								}
								break;
							case "default":
								break;
						}

				}			
	
			}
			else 
			{
			/*
				Если поле идет без условия..т.е. cf_xxx=4 Допустим то вытягиваем имя поля...тоже проверяем на наличие такого поля
				и если существует то изменяем соответствующее условие поиска WHERE
			*/
				if(preg_match('/^cf_(.*)/i', $param,$field_param))
				{
					$fieldname = strtolower($field_param[1]);
					$result = $wpdb->get_results("SELECT * FROM  ".$wpdb->prefix."v_field_options WHERE name = '$fieldname'",OBJECT);
					if(is_object($result[0]))
					{
						if(is_numeric($value))
						{
							$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value = '$value')";
						}
						else 
						{
							$values = explode("||",$value);
							if(is_numeric($values[0]) || is_numeric($values[1]))
							{
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND ( ".$wpdb->prefix."postmeta.meta_value = '$values[0]' OR  ".$wpdb->prefix."postmeta.meta_value = '$values[1]' ))";
							}
							else 
							{
								if($value == "_void_")
								$pieces['where'] .= " AND ( ".$wpdb->prefix."postmeta.meta_key = '$fieldname' AND  ".$wpdb->prefix."postmeta.meta_value = '')";
							}
						}
					}
				}
			}
		//Это копия wordpress'овского массива $pieces. Он к нам приходит, мы изменяет элемент ['where] и отправляем назад. 
		return $pieces;
	}
}
?>