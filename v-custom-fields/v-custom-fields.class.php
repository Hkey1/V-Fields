<?php
include_once ($_SERVER ['DOCUMENT_ROOT'] . '/wp-content/plugins/v-custom-fields/v-functions.php');
// Глобальный класс, паттерн Sinleton
class CFields {
	// Наш объект
	protected static $instance;
	public static $current_post_id;
	public static $fields = NULL;
	public static $flag_edit_out = 0;
	
	// Конструктор
	private function __construct() {
	
	}
	
	// Клонируем объект
	private function __clone() {
	
	}
	
	// Функция возвращает нам объект в единственном экземпляре
	public static function getInstance() {
		// проверяем актуальность экземпляра
		if (null === self::$instance) {
			// создаем новый экземпляр
			self::$instance = new self ();
		}
		// возвращаем созданный или существующий экземпляр
		return self::$instance;
	}
	
	/*
	 * Вывод HTML
	 */
	// Функция единовразово считывает все поля, чтобы постоянно не обращаться к
	// базе
	private function LoadFields() {
		global $post;
		if ($post->ID != self::$current_post_id) {
			$data = array ();
			global $wpdb;
			$result = $wpdb->get_results ( "SELECT * FROM  " . $wpdb->prefix . "v_fields WHERE post_id = $post->ID", ARRAY_A );
			foreach ( $result as &$row ) {
				$translit = $row ['translit'];
				$field_type_result = $wpdb->get_row ( "SELECT name FROM  " . $wpdb->prefix . "v_field_types WHERE id=(SELECT fieldtype FROM  " . $wpdb->prefix . "v_field_options WHERE translit = '$translit')" );
				$row ['fieldtype'] = $field_type_result->name;
				$field_options_result = $wpdb->get_row ( "SELECT options FROM  " . $wpdb->prefix . "v_field_options WHERE translit = '$translit'" );
				$row ['options'] = $field_options_result->options;
			}
			self::$current_post_id = $post->ID;
			self::$fields = $result;
		}
	}
	// Выводит HTML
	static function Out($fieldname, $params = NULL) {
		$flag_outed = false;
		self::LoadFields ();
		foreach ( self::$fields as &$row ) {
			if ($row ['translit'] == translit ( $fieldname )) {
				if ($row ['fieldtype'] != NULL) {
					$current_class = new ReflectionClass ( $row ['fieldtype'] );
					$current_object = $current_class->newInstance ();
					echo $current_object->Out ( $row, $params );
					unset ( $current_object );
					$flag_outed = true;
				}
			}
		}
		if (! $flag_outed)
			echo "Fieldname '$fieldname' is invalid </br>";
	}
	// Возвращает HTML
	static function GetHTML($fieldname, $params = NULL) {
		$flag_outed = false;
		self::LoadFields ();
		foreach ( self::$fields as &$row ) {
			if ($row ['translit'] == translit ( $fieldname )) {
				$current_class = new ReflectionClass ( $row ['fieldtype'] );
				$current_object = $current_class->newInstance ();
				unset ( $current_object );
				$flag_outed = true;
				return $current_object->Out ( $row, $params );
			}
		}
		if (! $flag_outed)
			echo "Fieldname '$fieldname' is invalid </br>";
	}
	// Возвращает HTML
	static function Value($fieldname, $params = NULL) {
		$flag_outed = false;
		self::LoadFields ();
		foreach ( self::$fields as &$row ) {
			if ($row ['translit'] == translit ( $fieldname )) {
				$current_class = new ReflectionClass ( $row ['fieldtype'] );
				$current_object = $current_class->newInstance ();
				unset ( $current_object );
				$flag_outed = true;
				return $row ['data'];
			}
		}
		if (! $flag_outed)
			echo "Fieldname '$fieldname' is invalid </br>";
	}
	/*
	 * Конец вывода HTML
	 */
	
	// ////
	// /Функция вывода снипета
	static function Snippet($snippet = NULL) {
		global $wpdb;
		$snippet = translit ( $snippet );
		$result = $wpdb->get_var ( "SELECT data FROM  " . $wpdb->prefix . "v_snippets WHERE name='$snippet'" );
		echo $result;
	}
	// ///
	static function Show_Edit() {
		global $user_level;
		global $post;
		if ($user_level != 10)
			return;
		global $wpdb;
		self::LoadFields ();
		// Если ещё не загружен виджет, то загружаем. Единожды.
		if (! self::$flag_edit_out) {
			$result = "";
			self::$flag_edit_out = 1;
			echo "<div id=\"draggable\" class=\"ui-widget-content ui-widget-shadow  ui-corner-all\"><a href=\"#\">Edit fields</a></div>";
			// echo "<div id=\"edit_fields\" title=\"Fields\"></div>";
			$result .= "<div id=\"edit_fields\" title=\"Fields\">";
			$posts = $wpdb->get_results ( 'SELECT id,post_title FROM ' . $wpdb->posts . ' WHERE post_status = "publish" AND post_type = "post" ORDER BY id DESC', ARRAY_A );
			/*
			 * Делаем запрос к базе, чтобы получить айдишники всех
			 * опубликованных постов. Потов в цикле выводим кастомные поля для
			 * каждого из поста. $posts_id представляет собой двумерный массив
			 * ['индекс_массива'][id]. Далее в зависимости от типа поля создаем
			 * объект того поля и вызываем метод OutField() соответсвующего поля
			 */
			$result .= "<div id=\"accordion\">";
			foreach ( $posts as $post ) {
				$result .= "<h3 ><a href=\"#\">Post \"" . $post ['post_title'] . "\"</a></h3>";
				$result .= "<div id=\"" . $post ['id'] . "\" class=\"postgroup\">";
				foreach ( self::$fields as $row ) {
					if ($row ['fieldtype'] != NULL) {
						$current_class = new ReflectionClass ( $row ['fieldtype'] );
						$current_object = $current_class->newInstance ();
						$result .= preg_replace ( '/<script\b[^>]*>(.*?)<\/script>/is', "", $current_object->OutField ( $row, $post ['id'] ) );
						unset ( $current_object );
					}
				}
				$result .= "</div>";
			}
			// End Accordion
			$result .= "</div>";
			//
			$result .= "</div>";
			echo $result;
		}
	}

}
?>