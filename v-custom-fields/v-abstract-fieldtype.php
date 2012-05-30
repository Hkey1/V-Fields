<?php
include_once ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-functions.php');

abstract class FieldType {
	//Имя поля
	protected $name;
	//Шапка поля
	protected $head;
	//Уровень доступа
	protected $user_level;
	//Функция вывода HTML  в настройках плагина при нажатии "Add field"
	abstract function NewOptions();
	//Функция загрузки HTML поля и его значений при открытии настройки плагина
	abstract function LoadOptions($data);
	//Проверка корректности при сохранении опций в настройках плагина
	abstract function ValidateOptions($array);
	//Пока просто заглушка для будущих проверок
	abstract function ValidateField();
	//Проверка валидности при сохранении самих полей при создании/редактировании постов
	abstract function ValidatePostField($array = NULL,$data = NULL);
	//Вывод поля в создании/редактировании поста
	abstract function OutField($array, $post_id = 0);
	//Сохрание опций в настройках плагина(Сохраняет все поля при нажатии на кнопку Save)
	abstract function SaveOptions($array);
	//Вывод поля в самой теме
	abstract function Out($array = NULL,$params = NULL);
	//Перегрузка поиска. Для каждого поля можно натсроить свой поиск по определенным критериям
	abstract function Mysql_Where($pieces = NULL,$param = NULL,$value = NULL);
	//Конструктор. Создает HTML шапку поля в настройках плагина
	function __construct() {
		global $user_ID;
		$this->user_level = get_userdata($user_ID)->user_level;
		if(!$this->user_level)
			$this->user_level = 0;
		$selection = make_select_list ( get_class ( $this ) );
		$this->head = "<h3>
				     <span class=\"container\">
				     	<input type=\"hidden\" name=\"hr\" value=\"true\">
						<input type=\"text\" name=\"fieldname\" class=\"name\" />
						<span>
						<select class=\"select\" name=\"select\">$selection</select>
						<span class=\"ui-icon ui-icon-closethick\"></span>
						</span>
					</span>		
				</h3>";
	}

}
?>