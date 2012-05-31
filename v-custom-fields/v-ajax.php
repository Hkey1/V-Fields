<?php
include_once ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-functions.php');
//////////////////////////////////////////
//Это тип действия...например вывести настройки типа поля...сохранить поля и.т.д
////////////////////////////////////////////////////////////////////////////////
$operation = $_POST ['operation'];

switch ($operation) {
	case "new_options" :
		NewOptions ( $_POST ['classname'] );
		break;
	case "new_snippet" :
		NewSnippet ();
		break;
	case "save_snippets" :
		SaveSnippets ($_POST[data]);
		break;
	case "save_fields" :
		SaveOptions ( $_POST ['data'] );
		break;
	case "change_type" :
		ChangeType ( $_POST ['data'] );
		break;
	case "update_field" :
		UpdateField( $_POST['fieldtype'],$_POST['fieldid'], $_POST['data'], $_POST['params'] );
		break;
	default :
		echo "ERROR";
		break;
}

//Функция для вывода настроек типа поля...Она создает объект определенного класса по его имени а тот класс уже
//непосредственно выводит всё используя свой OutField()
function NewOptions($classname) {
	//Creating new object from string
	$current_class = new ReflectionClass ( $classname );
	$current_object = $current_class->newInstance ();
	//Объект выводит свои настройки типа поля в HTML
	echo $current_object->NewOptions ();
	unset ( $current_object );
}

//Функция сохранения полей в базе данных. Принцип- удаляем все имеющиеся поля и заново сохраняем только что принятые
function SaveOptions($data) {
	global $db,$wpdb;
	$err_status = 1;
	//разбиваем всю строку на массивы....каждый элемент это Поле
	$fields = explode ( "hr=true", $data );
	//очищаем массив от пустых элементов
	$fields = clear_array_empty ( $fields );
	mysql_query ( "DELETE FROM  ".$wpdb->prefix."v_field_options", $db );
	mysql_query ( "ALTER_TABLE  ".$wpdb->prefix."v_field_options AUTO_INCREMENT = 0", $db );
	foreach ( $fields as $row ) {
		//разбиваем каждое поле на элементы
		$types = explode ( "&", $row );
		//очищаем мусор из элементов
		$types = clear_array_empty ( $types );
		$final_field = array ();
		foreach ( $types as $row2 ) {
			$elems = explode ( "=", $row2 );
			//$elems = сlear_array_empty($elems);
			$final_field [$elems [0]] = urldecode ( $elems [1] );
		
		}
		//Создаем объект переданного типа...тот что в ['select']
		//и вызываем метод сохранения в базу, передавая ему в качестве ассоциативный массив
		if (! strlen ( $final_field ['select'] ))
			continue;
		$current_class = new ReflectionClass ( $final_field ['select'] );
		$current_object = $current_class->newInstance ();
		//Если валидатор сработал то пользователю выдастся ошибка		

		$err_status = $err_status + $current_object->SaveOptions ( $final_field );
		
		//удаляем только что созданный объект, т.к. он нам ни к чему
		unset ( $current_object );
	}
	//echo $err_status;
	if ($err_status == 1)
		echo "SAVED";
}
//Функция которая создает объект класса и передает управление ему.Тот в свою очередь выводит поля опций.
function ChangeType($fieldtype) {
	if (! strlen ( $fieldtype ))
		return 0;
	$current_class = new ReflectionClass ( $fieldtype );
	$current_object = $current_class->newInstance ();
	echo $current_object->ChangeType ();
	unset ( $current_object );

}
//

function UpdateField($fieldtype = NULL, $fieldid = NULL, $data = NULL,$params = NULL)
{	
	if(!$fieldtype || !$fieldid)
		return;
	if(!ctype_digit($fieldid))
		return;
	$current_class = new ReflectionClass ( $fieldtype );
	$current_object = $current_class->newInstance ();
	$current_object->UpdateField($fieldid,$data,$params);
	unset($current_object);
	
}
///
function NewSnippet()
{
	$result = "<div class=\"section\">
				<h3>
				     <span class=\"container\">
				     	<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" />
						<span>
						<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
					</span>		
				</h3>
				<div class=\"field_settings\">
						Data:
						<p><textarea name=\"value\"></textarea></p>
				</div>
			</div>";
	echo $result;
}



function SaveSnippets($data)
{
	
	global $db,$wpdb;
	$err_status = 1;
	//разбиваем всю строку на массивы....каждый элемент это Поле
	$fields = explode ( "hr=true", $data );
	//очищаем массив от пустых элементов
	$fields = clear_array_empty ( $fields );
	mysql_query ( "DELETE FROM  ".$wpdb->prefix."v_snippets", $db );
	mysql_query ( "ALTER_TABLE  ".$wpdb->prefix."v_snippets AUTO_INCREMENT = 0", $db );
	foreach ( $fields as $row ) {
		//разбиваем каждое поле на элементы
		$types = explode ( "&", $row );
		//очищаем мусор из элементов
		$types = clear_array_empty ( $types );
		$final_field = array ();
		foreach ( $types as $row2 ) {
			$elems = explode ( "=", $row2 );
			//$elems = сlear_array_empty($elems);
			$final_field [$elems [0]] = urldecode ( $elems [1] );
		
		}
		$str = iconv ( "utf-8", "windows-1251", $final_field['fieldname'] );
		if (! preg_match ( "/^[0-9a-zA-ZА-я\-_ \s]+$/", $str )) {
			echo "<strong>NOT VALID STRING \"" . $final_field['fieldname'] . "\". ONLY DIGITS, SPACES AND UNDERLINES AVAILIBLE</strong></br>";
			$err_status ++;
			continue;
		}
		$final_field['fieldname'] = translit($final_field['fieldname']);
	 	mysql_query("INSERT INTO  ".$wpdb->prefix."v_snippets (name,data) VALUES('" . $final_field['fieldname'] . "','".$final_field['value']."')",$db);
	}
	//echo $err_status;
    if ($err_status == 1)
		echo "SAVED";		
}



?>