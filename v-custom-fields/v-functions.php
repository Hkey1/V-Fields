<?php

include_once $_SERVER ['DOCUMENT_ROOT'] . "/wp-config.php";
global $db;
$db = mysql_connect ( DB_HOST, DB_USER, DB_PASSWORD );
if (! $db) {
	die ( 'Не удалось соединиться : ' . mysql_error () );
}
mysql_select_db ( DB_NAME, $db );

// Делаем автозагрузку классов типов полей
// ////////////////////////////////////////
spl_autoload_register ( 'autoload' );
function autoload($className) {
	$fileName = $_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/fieldtypes/' . $className . '.php';
	include $fileName;
}
// Функция которая возвращает имя полей в массиве.
// ///////////////////////////////////////////////
function get_field_names() {
	global $db, $wpdb;
	$result = mysql_query ( "SELECT name FROM " . $wpdb->prefix . "v_field_types", $db );
	$types = array ();
	while ( $type = mysql_fetch_assoc ( $result ) ) {
		$types [] = $type ['name'];
	}
	return $types;
}
// функция создания select-листа
function make_select_list($item = "Digit") {
	$result = "";
	$fieldtypes = get_field_names ();
	foreach ( $fieldtypes as $row ) {
		if ($row == $item)
			$result = $result . "<option value=\"$row\" selected=\"true\">" . $row . "</option>";
		else
			$result = $result . "<option value=\"$row\">" . $row . "</option>";
	}
	return $result;
}
// функция заполнения Списка разрядов
function make_dec_list($dec = NULL, $id = NULL) {
	$result = "<p>Round Dec: ";
	if ($dec == NULL || ! is_numeric ( $dec ))
		$dec = 0;
	if ($id != NULL)
		$id_text = " id=\"$id\"";
	else
		$id_text = "";
	$result .= "<script> 
	$(document).ready( function()
	{	
		$(\"#$id\").slider(\"value\",$dec);
	});
	</script>";
	if ($dec == 0)
		$dec_text = "Off";
	else
		$dec_text = $dec;
	$result .= "<input type=\"text\" class=\"dec_text\" readonly=\"readonly\" value=\"$dec_text\"/>";
	$result .= "<input type=\"hidden\" name=\"dec\" class=\"dec\" value=\"$dec\"/>";
	$result .= "<div class=\"slider\" $id_text></div></p>";
	
	return $result;
}
// Функция удаления пустых элементов массива
function clear_array_empty($array) {
	$ret_arr = array ();
	foreach ( $array as $val ) {
		if (! empty ( $val )) {
			$ret_arr [] = trim ( $val );
		}
	}
	return $ret_arr;
}
// функция удаления пустых строк в массиве
function remove_empty_strings_arr($array) {
	$result = array ();
	foreach ( $array as $row ) {
		if (strlen ( $row ) > 1)
			$result [] = $row;
	}
	return $result;
}
// функция которая выводит поля
function show_fields_options() {
	global $db, $wpdb;
	$str = "";
	$result = mysql_query ( "SELECT * FROM  " . $wpdb->prefix . "v_field_options", $db );
	while ( $row = mysql_fetch_assoc ( $result ) ) {
		// print_r($row);
		$field_id = $row ['fieldtype'];
		$class_query_result = mysql_query ( "SELECT name FROM  " . $wpdb->prefix . "v_field_types WHERE id = $field_id LIMIT 1", $db );
		$classname = mysql_result ( $class_query_result, 0 );
		// echo $classname."</br></br>";
		$current_class = new ReflectionClass ( $classname );
		$current_object = $current_class->newInstance ();
		$str = $str . $current_object->LoadOptions ( $row );
		unset ( $current_object );
	}
	return $str;
}
//
function load_snippets() {
	global $wpdb;
	$result = "";
	$snippets = $wpdb->get_results ( "SELECT * FROM " . $wpdb->prefix . "v_snippets", OBJECT );
	foreach ( $snippets as $row ) {
		$name = $row->name;
		$data = $row->data;
		$result .= "<div class=\"section\">
				<h3>
				     <span class=\"container\">
				     	<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" value=\"$name\" />
						<span>
						<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
					</span>		
				</h3>
				<div class=\"field_settings\">
						Data:
						<p><textarea name=\"value\">$data</textarea></p>
				</div>
				</div>";
	}
	return $result;
}
// Функция транслита
function translit($str) {
	$tr = array ("А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i", "Й" => "y", "Ё" => "e", "ё" => "e", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch", "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "", "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j", "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y", "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya", " " => "_", "." => "", "/" => "_" );
	return strtr ( $str, $tr );
}
// функция проверки количества полей
function field_count() {
	$result = 0;
	global $db, $wpdb;
	$query = mysql_query ( "SELECT COUNT(id) FROM  " . $wpdb->prefix . "v_field_options", $db );
	$query_result = mysql_result ( $query, 0 );
	if ($query_result)
		return 1;
	else
		return 0;
}

?>