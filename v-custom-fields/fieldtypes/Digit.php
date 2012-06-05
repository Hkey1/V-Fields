<?php
include (__DIR__ . '/../v-abstract-fieldtype.php');

class Digit extends FieldType {
	function __construct() {
		parent::__construct ();
		$this->default_options = array ("default" => 0, "step" => 10, "min" => 0, "max" => 1000 );
		$this->text_search = true;
		$this->index_search = true;
	}
	
	function ValidateOptions($array) {
		$err_status = "";
		$err_status .= parent::Validate ( $array ['fieldname'], "length", 20 );
		$err_status .= parent::Validate ( $array ['fieldname'], "string" );
		$err_status .= parent::Validate ( $array ['max'], "isdigit" );
		$err_status .= parent::Validate ( $array ['min'], "isdigit" );
		$err_status .= parent::Validate ( $array ['step'], "isdigit" );
		$err_status .= parent::Validate ( $array ['default'], "isdigit" );
		return $err_status;
	}
	
	function ValidatePostField($array = NULL, $data = NULL) {
		if (! $array)
			return "<div class=\"error\"><p>This or that went wrong</p></div>";
		$options = unserialize ( $array ['options'] );
		if (! strlen ( trim ( $data ) ))
			return false;
		if (! is_numeric ( $data )) {
			$data = $options ['default'];
			return "<div class=\"error\"><p>\"" . $array ['name'] . "\" is not digit. Will be used default value \"" . $options ['default'] . "\".</p></div>";
		}
		
		if ($data > $options ['max']) {
			$data = $options ['default'];
			return "<div class=\"error\"><p>\"" . $array ['name'] . "\" is more than max. Will be used default value \"" . $options ['default'] . "\".</p></div>";
		}
		
		if ($data < $options ['min']) {
			$data = $options ['default'];
			return "<div class=\"error\"><p>\"" . $array ['name'] . "\" is less than min. Will be used default value \"" . $options ['default'] . "\".</p></div>";
		}
		return true;
	}
	
	function LoadOptions($load_type = "new", $data = NULL) {
		$str = "";
		$selection = make_select_list ( 'Digit' );
		// Если вызываем функции для загрузки опций существующего поля
		if ($load_type == "load") {
			if ($data != NULL) {
				$options = $data ['options'];
				if (! strlen ( $options ))
					return 0;
				$array = unserialize ( $options );
				$id = $data ['id'];
				$select = $array ['select'];
				$name = $array ['fieldname'];
				$min = $array ['min'];
				$max = $array ['max'];
				$step = $array ['step'];
				$default = $array ['default'];
				$search = $array ['search'];
				$isearch = $array ['isearch'];
				($search == 1) ? $search_checked = "checked=\"true\"" : $search_checked = "";
				($isearch == 1) ? $isearch_checked = "checked=\"true\"" : $isearch_checked = "";
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
						<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"$default\"/></p>
						<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"$step\"/></p>
						<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"$min\"/></p>
						<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"$max\"/></p>
						<p><b>text search</b> <input type=\"checkbox\"  name=\"search\" value=\"1\" $search_checked /></p>
						<p><b>index search</b> <input type=\"checkbox\"  name=\"isearch\" value=\"1\" $isearch_checked /></p>
					</div>
				</div>";
			}
		} else {
			$this->text_search ? $search_checked = "checked=\"true\"" : $search_checked = "";
			$this->index_search ? $isearch_checked = "checked=\"true\"" : $isearch_checked = "";
			// Если создаем новое поле
			if ($load_type == "new") {
				$str = "
				<div class=\"section\">
					<h3>
						<span class=\"container\">
							<input type=\"hidden\" name=\"hr\" value=\"true\">
							<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"\" />
						<span>
							<select class=\"select\" name=\"select\">$selection</select>
							<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
						</span>
					</h3>
					<div class=\"field_settings\">
						<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"" . $this->default_options ['default'] . "\"/></p>
						<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"" . $this->default_options ['step'] . "\"/></p>
						<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"" . $this->default_options ['min'] . "\"/></p>
						<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"" . $this->default_options ['max'] . "\"/></p>
						<p><b>text search</b> <input type=\"checkbox\"  name=\"search\" value=\"1\" $search_checked /></p>
						<p><b>index search</b> <input type=\"checkbox\"  name=\"isearch\" value=\"1\" $isearch_checked /></p>
					</div>
				</div>";
			} 			// Если меняем существующее поле на Digit
			elseif ($load_type == "change") {
				$str = "
					<div class=\"field_settings\">
						<p>Default:<br/> <input type=\"text\" name=\"default\" class=\"default\" value=\"" . $this->default_options ['default'] . "\"/></p>
						<p>Step:<br/> <input type=\"text\" name=\"step\" class=\"step\" value=\"" . $this->default_options ['step'] . "\"/></p>
						<p>Min value:<br/> <input type=\"text\" name=\"min\" class=\"min\" value=\"" . $this->default_options ['min'] . "\"/></p>
						<p>Max value:<br/> <input type=\"text\" name=\"max\" class=\"max\" value=\"" . $this->default_options ['max'] . "\"/></p>
						<p><b>text search</b> <input type=\"checkbox\"   name=\"search\" value=\"1\" $search_checked /></p>
						<p><b>index search</b> <input type=\"checkbox\"   name=\"isearch\" value=\"1\" $isearch_checked /></p>
					</div>
				";
			}
		}
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
			global $db, $wpdb;
			$value = 0;
			$query_result = mysql_query ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE post_id=$post_id AND translit='$translit'", $db );
			if ($query_result)
				$value = mysql_result ( $query_result, 0 );
			if (strlen ( trim ( $value ) ) == 0)
				$value = $array ['default'];
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
	
	function make_dec_div($params, $value) {
		$data = $value;
		if ($params ['division']) {
			if (ctype_digit ( $params ['dec'] )) {
				$del = 1;
				if ($params ['dec'] == 1)
					$del = 10;
				if ($params ['dec'] == 2)
					$del = 100;
				if ($params ['dec'] == 3)
					$del = 1000;
				if ($params ['dec'] == 4)
					$del = 10000;
				if ($params ['dec'] == 5)
					$del = 100000;
				$data = floor ( $data * $del ) / $del;
				$data = number_format ( $data, $params ['dec'], ".", " " );
				if (ctype_digit ( $value ))
					$data = $value;
			}
		} else {
			if (ctype_digit ( $params ['dec'] )) {
				$del = 1;
				if ($params ['dec'] == 1)
					$del = 10;
				if ($params ['dec'] == 2)
					$del = 100;
				if ($params ['dec'] == 3)
					$del = 1000;
				if ($params ['dec'] == 4)
					$del = 10000;
				if ($params ['dec'] == 5)
					$del = 100000;
				$data = floor ( $data * $del ) / $del;
				$data = number_format ( $data, $params ['dec'], ".", "" );
				if (ctype_digit ( $value ))
					$data = $value;
			}
		}
		// ////Спряжения
		// $declensions = explode("\n",$params['declensions']);
		$declensions = $params ['declensions'];
		// Если у нас три формы заданы в админке
		if (count ( $declensions ) == 3) {
			// Если дробь то вторая форма
			if (strpos ( $value, "." )) {
				$data .= " " . $declensions [1];
			} else {
				// Вытягиваем последние две цифры из числа
				$last_digits = substr ( $value, strlen ( $value ) - 2 );
				// Вытягиваем последнюю цифрц числа
				$last_digit = $value % 10;
				// При данном условии выводим в третей форме
				if ((intval ( $last_digits ) >= 11 && intval ( $last_digits ) <= 19) || (intval ( $last_digit ) >= 5 && intval ( $last_digit ) <= 9) || intval ( $last_digit ) == 0)
					$data .= " " . $declensions [2];
				else {
					// Если же число = 1,или заканчивается на 1(но не на 11), то
					// выводим в первой форме
					// Во всех остальных случаях выводим во второй форме
					if (intval ( $value ) == 1 || $last_digits % 10 == 1)
						$data .= " " . $declensions [0];
					else
						$data .= " " . $declensions [1];
				}
			}
		} 		// Если две формы заданы в админке
		elseif (count ( $declensions ) == 2) {
			// Если число = 1, то первая форма, в противном случае всегда во
			// второй
			if (intval ( $value ) == 1)
				$data .= "  " . $declensions [0];
			else
				$data .= "  " . $declensions [1];
		}
		return $data;
	}
	
	function Out($array = NULL, $params = NULL) {
		if (! $array)
			return;
		global $wpdb;
		$id = $array ['id'];
		$data = $array ['data'];
		$options = unserialize ( $array ['options'] );
		// /Количество цифр дробной части
		$data = $this->make_dec_div ( $params, $array ['data'] );
		// ////Конец спряжениям
		
		$result = "";
		$backup_params = "";
		if ($this->user_level != 10) {
			$result .= "<span>" . $data . "</span></br>";
			$result .= "</br>";
			return $result;
		}
		/*
		 * Добавляем скрытое поле backup_params. В нем хранится зашифрованное
		 * значение параметров передаваемых в функцию out, для того, чтобы когда
		 * на лету изменяли значения на сайте, действовали изначальные параметры
		 * передаваемые в OUT() (разряд, спряжение, округление)
		 */
		if ($params != NULL) {
			$backup_params = "<input type=\"hidden\" class=\"backup_params\" name=\"backup_params\" value=\"" . urlencode ( serialize ( $params ) ) . "\"/>";
		}
		$edit_data = $array ['data'];
		$result .= "<span id=\"$id\" class=\"digit\">" . $data . "</span>";
		$result .= "<div style=\"display:none;\" class=\"$id \" title=\"Edit value\">
		<input type=\"text\" class=\"edit_digit\" value=\"$edit_data\">
		" . $backup_params . "
		</div>";
		$result .= "</br>";
		return $result;
	}
	
	function UpdateField($fieldid, $data, $params = NULL) {
		if ($this->user_level != 10)
			return;
		global $wpdb;
		if (! is_numeric ( $data )) {
			$result = $wpdb->get_results ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
			echo $result [0] ['data'];
			return;
		}
		$options_result = $wpdb->get_row ( "SELECT options FROM  " . $wpdb->prefix . "v_field_options WHERE translit = (SELECT translit FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid)" );
		$options = unserialize ( $options_result->options );
		if ($data > $options ['max'] || $data < $options ['min']) {
			$result = $wpdb->get_results ( "SELECT data FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
			echo $result [0] ['data'];
			return;
		}
		$wpdb->query ( "UPDATE  " . $wpdb->prefix . "v_fields SET data = '$data' WHERE id = $fieldid" );
		// Обновляем метаданные вордпресса
		$result = $wpdb->get_results ( "SELECT translit,post_id FROM  " . $wpdb->prefix . "v_fields WHERE id = $fieldid LIMIT 1", ARRAY_A );
		$fieldname = $result [0] ['translit'];
		$post_id = $result [0] ['post_id'];
		if (ctype_digit ( $post_id ) && strlen ( $fieldname ))
			update_post_meta ( $post_id, $fieldname, $data );
			//
		if ($params != NULL)
			$params = unserialize ( urldecode ( $params ) );
		$data = $this->make_dec_div ( $params, $data );
		echo $data;
	}
	
	/*
	 * Поиск
	 */
	function Mysql_Where($pieces = NULL, $fieldname = NULL, $value = NULL) {
		global $wpdb;
		if (is_numeric ( $value )) {
			$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value = '$value')";
		} else {
			$values = explode ( "||", $value );
			if (is_numeric ( $values [0] ) || is_numeric ( $values [1] )) {
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND ( " . $wpdb->prefix . "postmeta.meta_value = '$values[0]' OR  " . $wpdb->prefix . "postmeta.meta_value = '$values[1]' ))";
			} else {
				if ($value == "_void_")
					$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value = '')";
			}
		}
		return $pieces;
	}
	
	function Mysql_Where_No($pieces = NULL, $fieldname = NULL, $value = NULL) {
		global $wpdb;
		if (is_numeric ( $value ))
			$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value <> '$value')";
		return $pieces;
	}
	
	function Mysql_Where_Special($pieces = NULL, $param = NULL, $fieldname = NULL, $value = NULL) {
		global $wpdb;
		switch ($param) {
			case "more" :
				if (! is_numeric ( $value ))
					break;
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value + 0 > '$value')";
				break;
			case "less" :
				if (! is_numeric ( $value ))
					break;
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value + 0 < '$value')";
				break;
			case "from" :
				if (! is_numeric ( $value ))
					break;
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value + 0 >= '$value')";
				break;
			case "to" :
				if (! is_numeric ( $value ))
					break;
				$pieces ['where'] .= " AND ( " . $wpdb->prefix . "postmeta.meta_key = '$fieldname' AND  " . $wpdb->prefix . "postmeta.meta_value + 0.0 <= '$value')";
				break;
			case "default" :
				break;
		}
		return $pieces;
	}
}
?>