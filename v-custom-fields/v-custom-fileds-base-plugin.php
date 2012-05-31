<?php
/*
 * Plugin Name: V Custom Fields: base plugin Plugin URI: http://no-site.org/
 * Description: Base plugin Author: Oleg Kovalyov Version: 1.0 Author URI:
 * http://vkontakte.ru/id4354304
 */
error_reporting ( E_ERROR );
//
add_action ( 'admin_menu', 'v_add_menu_items' );
include (__DIR__ . "/v-custom-fields.class.php");

//
add_action ( "wp_head", "wp_head_js_to_content", 0 );

function wp_head_js_to_content() {
	echo '<link rel="stylesheet" href="/wp-content/plugins/v-custom-fields/css/custom-theme/jquery-ui-1.8.16.custom.css">';
	wp_deregister_script ( 'jquery' );
	wp_register_script ( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js' );
	wp_enqueue_script ( 'jquery' );
	wp_deregister_script ( 'jqueryui' );
	wp_register_script ( 'jqueryui', '/wp-content/plugins/v-custom-fields/js/jquery-ui.js' );
	wp_enqueue_script ( 'jqueryui' );
	wp_register_script ( 'spin', '/wp-content/plugins/v-custom-fields/js/jquery-spin.js' );
	wp_enqueue_script ( 'spin' );
	wp_register_script ( 'mousewheel', '/wp-content/plugins/v-custom-fields/js/jquery.mousewheel.min.js' );
	wp_enqueue_script ( 'mousewheel' );
	wp_deregister_script ( 'v_content' );
	wp_register_script ( 'v_content', '/wp-content/plugins/v-custom-fields/js/v-content.js' );
	wp_enqueue_script ( 'v_content' );
}
//
function v_add_menu_items() {
	global $plugin_name;
	global $snippet;
	// Creating new point of menu;
	$plugin_name = add_submenu_page ( 'options-general.php', 'Custom Fields', 'Custom Fields', 8, __FILE__, v_view_admin_page );
	$snippet = add_submenu_page ( 'themes.php', "Snippets", "Snippets", 8, "v_snippets", "v_display_snippets" );
	// Here we want to launch adding Javascript;
	add_action ( "admin_enqueue_scripts", v_load_scripts );
	// Here we want to launch adding CSS;
	add_action ( "admin_head-{$plugin_name}", v_load_styles );
	add_action ( "admin_head-{$snippet}", v_load_styles );

}

// Adding Javascript on Plugin Options page;
function v_load_scripts($hook_suffix) {
	global $plugin_name;
	global $snippet;
	if ($plugin_name == $hook_suffix) {
		wp_deregister_script ( 'jquery' );
		wp_register_script ( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js' );
		wp_enqueue_script ( 'jquery' );
		wp_deregister_script ( 'jqueryui' );
		wp_register_script ( 'jqueryui', '/wp-content/plugins/v-custom-fields/js/jquery-ui.js' );
		wp_enqueue_script ( 'jqueryui' );
		wp_register_script ( 'jquery_form', '/wp-content/plugins/v-custom-fields/js/jquery.form.js' );
		wp_enqueue_script ( 'jquery_form' );
		wp_deregister_script ( 'v_adminpage' );
		wp_register_script ( 'v_adminpage', '/wp-content/plugins/v-custom-fields/js/v-adminpage.js' );
		wp_enqueue_script ( 'v_adminpage' );
	}
	if ($snippet == $hook_suffix) {
		wp_deregister_script ( 'jquery' );
		wp_register_script ( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js' );
		wp_enqueue_script ( 'jquery' );
		wp_deregister_script ( 'jqueryui' );
		wp_register_script ( 'jqueryui', '/wp-content/plugins/v-custom-fields/js/jquery-ui.js' );
		wp_enqueue_script ( 'jqueryui' );
		wp_register_script ( 'jquery_form', '/wp-content/plugins/v-custom-fields/js/jquery.form.js' );
		wp_enqueue_script ( 'jquery_form' );
		wp_deregister_script ( 'v_snippets' );
		wp_register_script ( 'v_snippets', '/wp-content/plugins/v-custom-fields/js/v-snippets.js' );
		wp_enqueue_script ( 'v_snippets' );
		wp_deregister_script ( 'tiny_mce' );
		wp_register_script ( 'tiny_mce', '/wp-content/plugins/v-custom-fields/js/tiny_mce/tiny_mce.js' );
		wp_enqueue_script ( 'tiny_mce' );
	}
}

// Adding CSS on Plugin Options page;
function v_load_styles() {
	?>
<link rel="stylesheet"
	href="/wp-content/plugins/v-custom-fields/css/custom-theme/jquery-ui-1.8.16.custom.css">
<?php
}

// Main function viewing admin page
function v_view_admin_page() {
	include_once ("v-template.php");
}

// View Snipets
function v_display_snippets() {
	include_once ("v-template-snippets.php");
}

// ///////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////
// /////////// Custom Fields//////////////
// ///////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////
add_action ( 'add_meta_boxes', 'v_view_custom_fields' );
// добавляем хук для вывода ошибки
add_action ( 'admin_notices', 'custom_fields_error' );
// функция вывода ошибки, когда сохраняется пост
function custom_fields_error() {
	if (! session_id ())
		session_start ();
	echo $_SESSION ['field_errors'];
	unset ( $_SESSION ['field_errors'] );
}
// добавляем хук для сохранения поля
add_action ( 'save_post', 'v_custom_fields_save_postdata' );
register_activation_hook ( __FILE__, 'v_custom_fields_activate' );
register_deactivation_hook ( __FILE__, 'v_custom_fields_deactivate' );

function v_view_custom_fields() {
	wp_deregister_script ( 'jquery' );
	wp_register_script ( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js' );
	wp_enqueue_script ( 'jquery' );
	wp_register_script ( 'spin', '/wp-content/plugins/v-custom-fields/js/jquery-spin.js' );
	wp_enqueue_script ( 'spin' );
	wp_register_script ( 'mousewheel', '/wp-content/plugins/v-custom-fields/js/jquery.mousewheel.min.js' );
	wp_enqueue_script ( 'mousewheel' );
	wp_deregister_script ( 'tiny_mce' );
	wp_register_script ( 'tiny_mce', '/wp-content/plugins/v-custom-fields/js/tiny_mce/tiny_mce.js' );
	wp_enqueue_script ( 'tiny_mce' );
	add_meta_box ( 'myplugin_sectionid', __ ( 'Custom Fields', 'myplugin_textdomain' ), 'v_view_custom_fields_html', 'post' );
	add_meta_box ( 'myplugin_sectionid', __ ( 'Custom Fields', 'myplugin_textdomain' ), 'v_view_custom_fields_html', 'page' );
}

function v_view_custom_fields_html() {
	include_once ('v-functions.php');
	global $post;
	global $wpdb;
	
	wp_nonce_field ( plugin_basename ( __FILE__ ), 'v_custom_fields_noncename' );
	
	$is_new_article = $wpdb->get_var ( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'auto-draft' AND ID=$post->ID" );
	$fields = $wpdb->get_results ( "SELECT * FROM  " . $wpdb->prefix . "v_field_options", ARRAY_A );
	foreach ( $fields as $row ) {
		$fieldtype = $row ['fieldtype'];
		$fieldclass = $wpdb->get_var ( "SELECT name FROM  " . $wpdb->prefix . "v_field_types WHERE id = $fieldtype" );
		$current_class = new ReflectionClass ( $fieldclass );
		$current_object = $current_class->newInstance ();
		if ($is_new_article) {
			print_r ( $current_object->OutField ( $row ) );
		} else
			print_r ( $current_object->OutField ( $row, $post->ID ) );
	}
	// print_r($post);
}

function v_custom_fields_save_postdata($post_id) {
	session_start ();
	$_SESSION ['field_errors'];
	if (defined ( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE)
		return $post_id;
	
	if ('page' == $_POST ['post_type']) {
		if (! current_user_can ( 'edit_page', $post_id ))
			return $post_id;
	} else {
		if (! current_user_can ( 'edit_post', $post_id ))
			return $post_id;
	}
	
	if (! wp_is_post_revision ( $post_id )) {
		global $wpdb;
		
		$wpdb->query ( "DELETE FROM  " . $wpdb->prefix . "v_fields WHERE post_id=$post_id" );
		$wpdb->query ( "DELETE FROM  " . $wpdb->prefix . "postmeta WHERE post_id=$post_id" );
		$fields = $wpdb->get_results ( "SELECT * FROM  " . $wpdb->prefix . "v_field_options", ARRAY_A );
		foreach ( $fields as $row ) {
			$data = $_POST [$row ['translit']];
			$translit = $row ['translit'];
			$fieldclass = $wpdb->get_var ( "SELECT name FROM " . $wpdb->prefix . "v_field_types WHERE id=" . $row ['fieldtype'] );
			$current_class = new ReflectionClass ( $fieldclass );
			$current_object = $current_class->newInstance ();
			$error = $current_object->ValidatePostField ( $row, &$data );
			unset ( $current_object );
			if ($error != 1) {
				if ($error == false)
					continue;
				$_SESSION ['field_errors'] = $_SESSION ['field_errors'] . $error;
			}
			$wpdb->query ( "INSERT INTO  " . $wpdb->prefix . "v_fields (translit,post_id,data) VALUES ('$translit',$post_id,'$data')" );
			if (! update_post_meta ( $post_id, $translit, $data ))
				add_post_meta ( $post_id, $translit, $data );
		}
	}
}
// ///////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////
// ///////////////////////////////////////////////////////////////////////////////////////
function v_custom_fields_activate() {
	global $wpdb;
	$wpdb->query ( "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "v_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `translit` varchar(100) NOT NULL,
  `post_id` int(11) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`),
  KEY `field_id` (`translit`),
  KEY `field_id_2` (`translit`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;" );
	
	$wpdb->query ( "

CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "v_field_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fieldtype` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `translit` varchar(100) NOT NULL,
  `options` text NOT NULL,
  `isearch` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  KEY `id_2` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0;" );
	
	$wpdb->query ( "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "v_field_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6;" );
	$wpdb->query ( "INSERT INTO `" . $wpdb->prefix . "v_field_types` (`id`, `name`) VALUES
(1, 'Digit'),
(2, 'Text'),
(3, 'String'),
(4, 'Bool'),
(5, 'Select');" );
	
	$wpdb->query ( "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "v_snippets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=0 ;" );
	
	// Если существует файл с опциями то поля загружаем из него при активации
	// плагина
	if (is_file ( __DIR__ . "/field_options.ini" )) {
		$fh = fopen ( __DIR__ . "/field_options.ini", "r" ) or die ( "File ($file) does not exist!" );
		$options = fread ( $fh, filesize ( __DIR__ . "/field_options.ini" ) );
		include (__DIR__ . "/v-ajax.php");
		SaveOptions ( $options );
		fclose ( $fh );
	}
	// $options =
// "hr=true&field_post_connect=&fieldname=price&select=Digit&default=20&step=10&min=0&max=9000000&search=1&isearch=1&hr=true&fieldname=description&select=Text&max_length=1000&default=&html_editor=1&search=1";

}

function v_custom_fields_deactivate() {
	global $wpdb;
	$wpdb->query ( "DROP TABLE  " . $wpdb->prefix . "v_fields" );
	$wpdb->query ( "DROP TABLE  " . $wpdb->prefix . "v_field_options" );
	$wpdb->query ( "DROP TABLE  " . $wpdb->prefix . "v_field_types" );
	$wpdb->query ( "DROP TABLE  " . $wpdb->prefix . "v_snippets" );
	return 0;
}

// /////////////////////////////////////////////
// ////SEARCH CUSTOM FIELDS/////////////////////
// /////////////////////////////////////////////
function custom_search_join($join) {
	if (is_search () && isset ( $_GET ['s'] )) {
		global $wpdb;
		
		$join = " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
	}
	return ($join);
}
add_filter ( 'posts_join', 'custom_search_join' );

function custom_search_groupby($groupby) {
	if (is_search () && isset ( $_GET ['s'] )) {
		global $wpdb;
		$groupby = " $wpdb->posts.ID ";
	}
	return ($groupby);
}
add_filter ( 'posts_groupby', 'custom_search_groupby' );

function custom_search_where($where) {
	$old_where = $where;
	if (is_search () && isset ( $_GET ['s'] )) {
		global $wpdb;
		$fields = $wpdb->get_results ( "SELECT * FROM  " . $wpdb->prefix . "v_field_options", OBJECT );
		// return " AND (CAST($wpdb->postmeta.meta_value AS SIGNED) > 6) AND
		// ($wpdb->posts.post_status = 'publish') ";
		/*
		 * INDEX SEARCH
		 */
		if (isset ( $_GET ['isearch'] )) {
			$where = "";
			foreach ( $fields as $row ) {
				$options = unserialize ( $row->options );
				if ($options ['isearch'] == 1)
					$where = $where . "";
			}
			return " AND  $wpdb->postmeta.meta_value = 4";
		}
		
		/*
		 * END INDEX SEARCH
		 */
		$customs = Array ();
		foreach ( $fields as $row ) {
			$options = unserialize ( $row->options );
			if ($options ['search'] == 1)
				$customs [] = $row->translit;
		}
		$query = '';
		$var_q = stripslashes ( $_GET ['s'] );
		if ($_GET ['sentence']) {
			$search_terms = array ($var_q );
		} else {
			preg_match_all ( '/".*?("|$)|((?<=[\\s",+])|^)[^\\s",+]+/', $var_q, $matches );
			$search_terms = array_map ( create_function ( '$a', 'return trim($a, "\\"\'\\n\\r ");' ), $matches [0] );
		}
		$n = ($_GET ['exact']) ? '' : '%';
		$searchand = '';
		foreach ( ( array ) $search_terms as $term ) {
			$term = addslashes_gpc ( $term );
			$query .= "{$searchand}(";
			$query .= "($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$query .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
			foreach ( $customs as $custom ) {
				$query .= " OR (";
				$query .= "($wpdb->postmeta.meta_key = '$custom')";
				$query .= " AND ($wpdb->postmeta.meta_value  LIKE '{$n}{$term}{$n}')";
				$query .= ")";
			}
			$query .= ")";
			$searchand = ' AND ';
		}
		$term = $wpdb->escape ( $var_q );
		if (! $_GET ['sentense'] && Count ( $search_terms ) > 1 && $search_terms [0] != $var_q) {
			$search .= " OR ($wpdb->posts.post_title LIKE '{$n}{$term}{$n}')";
			$search .= " OR ($wpdb->posts.post_content LIKE '{$n}{$term}{$n}')";
		}
		
		if (! empty ( $query )) {
			$where = " AND ({$query}) AND ($wpdb->posts.post_status = 'publish') ";
		}
	}
	
	return ($where);
}
add_filter ( 'posts_where', 'custom_search_where' );

/*
 * INDEX SEARCH
 */

add_filter ( 'posts_clauses', 'intercept_query_clauses', 20, 1 );

function intercept_query_clauses($pieces) {
	// Если это индексный поиск
	if ($_GET ['isearch'] == 1) {
		// Очищаем условие поиска
		$pieces ['where'] = "";
		global $wpdb;
		// проходим по всем GET параметрам в поисках соответствий на cf_xxx_xxx
		foreach ( $_GET as $key => $value ) {
			// Массив, одним из элементов которого является имя поля(после
			// распарсивания)
			$parts = array ();
			// сортировка
			if ($key == "cf_order_by") {
				// Очищаем условие сортировки
				$pieces ['orderby'] = "";
				$order_parts = explode ( "||", $value );
				foreach ( $order_parts as $row ) {
					$arr = explode ( ",", $row );
					// Имя поля, по которому будет идти сортировка
					$order_field = $arr [0];
					// Тип сортировки
					$order_type = $arr [1];
					/*
					 * Если имя поля и условия сортировки - строки и если такое
					 * поле существует и метод сортировки верный, то в условие
					 * ORDER BY ставим то, что нужно...в противном случае
					 * забиваем на сортировку и едем дальше
					 */
					if (is_string ( $order_field ) && is_string ( $order_type )) {
						$result = $wpdb->get_results ( "SELECT id FROM  " . $wpdb->prefix . "v_field_options WHERE name = '$order_field'", OBJECT );
						if (is_object ( $result [0] )) {
							if (strtolower ( $order_type ) == "asc" || strtolower ( $order_type ) == "desc")
								if (! strlen ( $pieces ['orderby'] ))
									$pieces ['orderby'] .= "wp_postmeta.meta_value $order_type";
								else
									$pieces ['orderby'] .= " ,wp_postmeta.meta_value $order_type";
						}
					
					}
				}
				continue;
			}
			/*
			 * Здесь вытягиваем паресром имя поля, проверяем существует ли такое
			 * поле..Если существует, то создаем объект это класса и условие
			 * поиска уже создает сам объект..Управление переходит к
			 * нему....Передаем и принимаем уже готовый wordpress'овский массив
			 * $pieces...Правим лишь элеменм 'where'.
			 */
			if (preg_match ( '/^cf_([a-zA-Z0-9]*)/i', $key, $parts )) {
				$fieldname = strtolower ( $parts [1] );
				$result = $wpdb->get_results ( "SELECT name FROM  " . $wpdb->prefix . "v_field_types WHERE id=(SELECT fieldtype FROM  " . $wpdb->prefix . "v_field_options WHERE name = '$fieldname')", OBJECT );
				$fieldclass = $result [0]->name;
				if (! $fieldclass)
					continue;
				$current_class = new ReflectionClass ( $fieldclass );
				$current_object = $current_class->newInstance ();
				$pieces = $current_object->Mysql_Where ( $pieces, $key, $value );
			} else
				continue;
		}
		// Добавляеем условие что ищем тока по полям с включенным индесным
		// поиском
		$pieces ['where'] .= " AND  " . $wpdb->prefix . "v_field_options.isearch = 1";
		// Сопоставляем кастомное поле из wp_postmeta и v_field_options(одна из
		// таблиц нашего плагина)
		$pieces ['join'] .= " LEFT JOIN  " . $wpdb->prefix . "v_field_options ON  " . $wpdb->prefix . "postmeta.meta_key =   " . $wpdb->prefix . "v_field_options.name";
	}
	
	return $pieces;
}
/*
 * END INDEX SEARCH
 */
// /////////////////////////////////////////////
// /////////////////////////////////////////////
// /////////////////////////////////////////////

?>