<?php
// Удалить каноническую ссылку - SEO by Yoast
function at_remove_dup_canonical_link()
{
	return false;
}
add_filter('wpseo_canonical', 'at_remove_dup_canonical_link');
/**
 * WP Diary functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

if (!function_exists('wp_diary_setup')):
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function wp_diary_setup()
	{
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on WP Diary, use a find and replace
		 * to change 'wp-diary' to the name of your theme in all the template files.
		 */
		load_theme_textdomain('wp-diary', get_template_directory() . '/languages');

		// Add default posts and comments RSS feed links to head.
		add_theme_support('automatic-feed-links');

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support('title-tag');

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support('post-thumbnails');
		add_theme_support('default-attachment');



		//set_post_thumbnail_size( 768, 432, true );
		// add_image_size( 'wp-diary-full-width', 1160, 653, true );
		// add_image_size( 'wp-diary-post', 600, 400, true );
		// add_image_size( 'wp-diary-post-auto', 600, 9999, false );
		// add_image_size( 'wp-diary-slider-post', 1200, 700, true );



		/**
		 * Enable support for post formats
		 *
		 * @link https://developer.wordpress.org/themes/functionality/post-formats/
		 */
		add_theme_support('post-formats', array('gallery', 'quote', 'audio', 'image', 'video'));

		// This theme uses wp_nav_menu() in one location.
		register_nav_menus(
			array(
				'primary_menu' => esc_html__('Primary', 'wp-diary'),
				'footer_menu' => esc_html__('Footer', 'wp-diary'),
			)
		);

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support('html5', array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
		));

		// Set up the WordPress core custom background feature.
		add_theme_support('custom-background', apply_filters('wp_diary_custom_background_args', array(
			'default-color' => 'ffffff',
			'default-image' => '',
		)));

		// Add theme support for selective refresh for widgets.
		add_theme_support('customize-selective-refresh-widgets');

		/**
		 * Add support for core custom logo.
		 *
		 * @link https://codex.wordpress.org/Theme_Logo
		 */
		add_theme_support('custom-logo', array(
			'height' => 250,
			'width' => 250,
			'flex-width' => true,
			'flex-height' => true,
		));

		/**
		 * Registers an editor stylesheet for the theme.
		 */
		add_editor_style('assets/css/mt-editor-style.css');
	}
endif;
add_action('after_setup_theme', 'wp_diary_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function wp_diary_content_width()
{
	// This variable is intended to be overruled from themes.
	// Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
	// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	$GLOBALS['content_width'] = apply_filters('wp_diary_content_width', 640);
}
add_action('after_setup_theme', 'wp_diary_content_width', 0);

/**
 * Set the theme version, based on theme stylesheet.
 *
 * @global string $wp_diary_theme_version
 */
function wp_diary_theme_version_info()
{
	$wp_diary_theme_info = wp_get_theme();
	$GLOBALS['wp_diary_theme_version'] = $wp_diary_theme_info->get('Version');
}
add_action('after_setup_theme', 'wp_diary_theme_version_info', 0);

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Load dynamic styles file
 */
//require get_template_directory() . '/inc/mt-dynamic-styles.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer/mt-customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

/**
 * Load custom hook file
 */
require get_template_directory() . '/inc/mt-custom-hooks.php';

/**
 * Load widget functions file
 */
require get_template_directory() . '/inc/widgets/mt-widget-functions.php';

/**
 * Load metaboxes
 */
require get_template_directory() . '/inc/metaboxes/mt-post-sidebar-meta.php';

/**
 * Load breadcrumbs class
 */
if (!function_exists('breadcrumb_trail')) {
	require get_template_directory() . '/inc/mt-class-breadcrumbs.php';
}

/*МОИ ПРАВКИ*/

//scripts
add_action('wp_enqueue_scripts', 'include_tabs');
function include_tabs()
{
	wp_enqueue_script('js_tabs', get_template_directory_uri() . '/assets/js/tabs.js', array('jquery'), null, true);
	wp_enqueue_script('slick-slides', get_template_directory_uri() . '/assets/js/slick.min.js', array(), '', true);
	wp_enqueue_style('slider.css', plugins_url() . '/bp_kitchen_print/assets/slider.css', null, true);
	wp_enqueue_style('lightbox.css', plugins_url() . '/bp_kitchen_print/assets/lightbox.css', null, true);
	wp_enqueue_script('kitchen_slider', plugins_url() . '/bp_kitchen_print/assets/slider.js', array('jquery'), null, true);
	wp_enqueue_script('kitchen_ligntbox', plugins_url() . '/bp_kitchen_print/assets/lightbox-2.6.min.js', array('jquery'), null, true);
}
//samples-tabs
add_action('wp_enqueue_scripts', 'include_stabs');
function include_stabs()
{
	wp_enqueue_script('js_stabs', get_template_directory_uri() . '/assets/js/tabs-samples.js', array('jquery'), null, true);
}
/* добавление поля в профиле*/
function add_status_field_to_profile($user)
{
	?>
	<h3>Статус пользователя</h3>
	<table class="form-table">
		<tr>
			<th><label for="status-activity">Статус активности</label></th>
			<td>
				<select name="status-activity" id="status-activity">
					<option value="">— выберите активность —</option>
					<?php
					$status_activity = [
						'active' => 'Активный',
						'noactive' => 'Не актиынй',
						'blocked' => 'Заблокированный'
					];
					foreach ($status_activity as $value => $label) {
						echo '<option value="' . esc_attr($value) . '" ' . selected(get_user_meta($user->ID, 'status_activity', true), $value, false) . '>' . esc_html($label) . '</option>';
					}
					?>
				</select>
			</td>
		</tr>
	</table>
	<?php
}
function add_contact_fields_to_profile($user)
{
	?>
	<h3>Контактные данные</h3>
	<table class="form-table">
		<tr>
			<th><label for="country">Страна</label></th>
			<td>
				<input type="text" name="country" id="country"
					value="<?php echo esc_attr(get_user_meta($user->ID, 'country', true)); ?>" class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="city">Город</label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr(get_user_meta($user->ID, 'city', true)); ?>"
					class="regular-text" />
			</td>
		</tr>
		<tr>
			<th><label for="address">Адрес</label></th>
			<td>
				<textarea name="address" id="address" rows="5"
					cols="30"><?php echo esc_textarea(get_user_meta($user->ID, 'address', true)); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><label for="phone">Телефон</label></th>
			<td>
				<textarea name="phone" id="phone" rows="5"
					cols="30"><?php echo esc_textarea(get_user_meta($user->ID, 'phone', true)); ?></textarea>
			</td>
		</tr>
		<tr>
			<th><label for="company">Юр. лицо</label></th>
			<td>
				<textarea name="company" id="company" rows="5"
					cols="30"><?php echo esc_textarea(get_user_meta($user->ID, 'company', true)); ?></textarea>
			</td>
		</tr>
	</table>
	<?php
}
function save_contact_fields_to_profile($user_id)
{
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}
	update_user_meta($user_id, 'country', sanitize_text_field($_POST['country']));
	update_user_meta($user_id, 'city', sanitize_text_field($_POST['city']));
	update_user_meta($user_id, 'address', sanitize_textarea_field($_POST['address']));
	update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
	update_user_meta($user_id, 'company', sanitize_text_field($_POST['company']));
	update_user_meta($user_id, 'status_activity', sanitize_text_field($_POST['status-activity']));
}
add_action('personal_options_update', 'save_contact_fields_to_profile');
add_action('edit_user_profile_update', 'save_contact_fields_to_profile');
add_action('show_user_profile', 'add_status_field_to_profile');
add_action('edit_user_profile', 'add_status_field_to_profile');
add_action('show_user_profile', 'add_contact_fields_to_profile');
add_action('edit_user_profile', 'add_contact_fields_to_profile');


// Сохранение значения флажка в метаполях пользователя
function save_user_checkbox_field($user_id)
{
	// Проверка, что пользователь имеет право редактировать профиль
	if (!current_user_can('edit_user', $user_id)) {
		return false;
	}

	// Сохранение значения флажка (1 — если установлен, 0 — если нет)
	if (isset($_POST['email-notification'])) {
		update_user_meta($user_id, 'email_notification', '1');
	} else {
		update_user_meta($user_id, 'email_notification', '0');
	}
}
add_action('personal_options_update', 'save_user_checkbox_field');
add_action('edit_user_profile_update', 'save_user_checkbox_field');
// add_filter('user_contactmethods', 'modify_contact_methods');

//ОТКЛЮЧАЕМ ФИДЫ И RSS
function wpb_disable_feed()
{
	wp_die(__('No feed available,please visit our <a href="' . get_bloginfo('url') . '">homepage</a>!'));
}

add_action('do_feed', 'wpb_disable_feed', 1);
add_action('do_feed_rdf', 'wpb_disable_feed', 1);
add_action('do_feed_rss', 'wpb_disable_feed', 1);
add_action('do_feed_rss2', 'wpb_disable_feed', 1);
add_action('do_feed_atom', 'wpb_disable_feed', 1);
add_action('do_feed_rss2_comments', 'wpb_disable_feed', 1);
add_action('do_feed_atom_comments', 'wpb_disable_feed', 1);

remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'rsd_link');

/** Уборка в мета rel=dns-prefetch href=//s.w.org **/
remove_action('wp_head', 'wp_resource_hints', 2);
/*Убираем wlwmanifest_link и прочее*/
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');

remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);



//УБИРАЕМ ВЕРСИЮ CSS И JS
function vc_remove_wp_ver_css_js($src)
{
	if (strpos($src, 'ver='))
		$src = remove_query_arg('ver', $src);
	return $src;
}
add_filter('style_loader_src', 'vc_remove_wp_ver_css_js', 9999);
add_filter('script_loader_src', 'vc_remove_wp_ver_css_js', 9999);


## Изменение внутреннего логотипа админки. Для версий с dashicons
add_action('add_admin_bar_menus', 'reset_admin_wplogo');
function reset_admin_wplogo()
{
	remove_action('admin_bar_menu', 'wp_admin_bar_wp_menu', 10); // удаляем стандартную панель (логотип)

	add_action('admin_bar_menu', 'my_admin_bar_wp_menu', 10); // добавляем свою
}
function my_admin_bar_wp_menu($wp_admin_bar)
{
	$wp_admin_bar->add_menu(
		array(
			'id' => 'wp-logo',
			'title' => '<img style="max-width:28px;height:auto; margin:3px;" src="' . get_bloginfo('template_directory') . '/images/Favicon.png" alt="" >',
			'href' => home_url('/'),
			'meta' => array(
				'title' => 'На сайт',
			),
		)
	);
}


/*Свой логотип на стр. входа*/
add_action('login_head', 'my_custom_login_logo');
function my_custom_login_logo()
{
	echo '<style type="text/css">
	h1 a { background-image:url(' . get_bloginfo('template_directory') . '/images/logo_new_2.png) !important; width:100% !important; background-size:contain !important;}
	</style>';
	/* Ставим ссылку с логотипа на сайт, а не на wordpress.org */
	add_filter('login_headerurl', function () {
		return get_home_url();
	});

	/* убираем title в логотипе "сайт работает на wordpress" */
	add_filter('login_headertitle', function () {
		return false;
	});
}

//font awesome
add_action('wp_enqueue_scripts', 'enqueue_load_fa');
function enqueue_load_fa()
{
	wp_enqueue_style('load-fa', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css');
}
//Шрифты
add_action('wp_enqueue_scripts', 'myfonts');
function myfonts()
{
	wp_enqueue_style('fonts', get_template_directory_uri() . '/assets/fonts/fonts.css');
}

/*
add_action( 'wp_enqueue_scripts', 'enqueue_load_click' );
function enqueue_load_click() {
	wp_enqueue_style( 'slick-slides-style', get_template_directory_uri()."/assets/css/slick.css" );
}
*/
/*РЕДИРЕКТ НА ГЛАВНУЮ ПОСЛЕ НАЖАТИЯ "ВЫХОД"*/
add_action('wp_logout', 'my_wp_logout');
function my_wp_logout()
{
	wp_safe_redirect('/');
	exit;
}
;
/*РЕДИРЕКТ НА НОВОСТИ ПРИ ВХОДЕ*/
// Добавляет ссылку в админ бар
add_action('admin_bar_menu', 'my_admin_bar_menu', 40);
function my_admin_bar_menu($wp_admin_bar)
{
	$wp_admin_bar->add_menu(
		array(
			'id' => 'to_adminpanel',
			'title' => 'В админку',
			'href' => '/wp-admin/',
		)
	);
	$wp_admin_bar->add_menu(
		array(
			'id' => 'to_site',
			'title' => 'На сайт',
			'href' => '/',
		)
	);
	$wp_admin_bar->add_menu(
		array(
			'id' => 'to_login',
			'title' => 'В договоры',
			'href' => '/contracts/',
		)
	);
	$wp_admin_bar->add_menu(
		array(
			'id' => 'escape',
			'title' => 'Выйти',
			//'href'  => '/manager?action=logout&amp;_wpnonce=b55bec0cda',
			'href' => '/wp-login.php?action=logout&amp;_wpnonce=b55bec0cda',
		)
	);
}
// add_filter('login_redirect', function () {
// 	return site_url('/contracts/'); });
## Удаление базовых элементов (ссылок) из тулбара

add_action('wp_before_admin_bar_render', 'binaryfork_before_admin_bar_render', 999);
function binaryfork_before_admin_bar_render()
{
	global $wp_admin_bar;
	//$wp_admin_bar->remove_menu('wp-logo');				// Remove the WordPress logo
	$wp_admin_bar->remove_menu('about');				// Remove the about WordPress link
	$wp_admin_bar->remove_menu('wporg');				// Remove the WordPress.org link
	$wp_admin_bar->remove_menu('documentation');		// Remove the WordPress documentation
	$wp_admin_bar->remove_menu('support-forums');		// Remove the support forums link
	$wp_admin_bar->remove_menu('feedback');				// Remove the feedback link	
	$wp_admin_bar->remove_menu('site-name');			// Remove the site name menu
	$wp_admin_bar->remove_menu('view-site');			// Remove the view site link
	$wp_admin_bar->remove_menu('updates');				// Remove the updates link
	$wp_admin_bar->remove_menu('comments');				// Remove the comments link
	$wp_admin_bar->remove_menu('new-content');			// Remove the content link
	//$wp_admin_bar->remove_menu('my-account');			// Remove the user details tab
	//$wp_admin_bar->remove_menu('customize');			// Remove customizer link
	$wp_admin_bar->remove_menu('delete-cache');			// Remove WP Supercache Delete Cache link
	$wp_admin_bar->remove_menu('updraft_admin_node');	// Remove Updraft plugin link
	$wp_admin_bar->remove_menu('w3tc');					// Remove W3 total cache plugin link
}



//УДАЛЕНИЕ query strings (для скорости загрузки сайта)
function _remove_script_version($src)
{
	$parts = explode('?ver', $src);
	return $parts[0];
}
add_filter('script_loader_src', '_remove_script_version', 15, 1);
add_filter('style_loader_src', '_remove_script_version', 15, 1);

/*ОТКЛЮЧЕНИЕ EMOJI*/
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_filter('the_content_feed', 'wp_staticize_emoji');
remove_filter('comment_text_rss', 'wp_staticize_emoji');
remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
add_filter('tiny_mce_plugins', 'disable_wp_emojis_in_tinymce');
function disable_wp_emojis_in_tinymce($plugins)
{
	if (is_array($plugins)) {
		return array_diff($plugins, array('wpemoji'));
	} else {
		return array();
	}
}
/*НАСТРАИВАЕМ КОНСОЛЬ*/
remove_action('welcome_panel', 'wp_welcome_panel');
// Удаление виджетов из Консоли WordPress
add_action('wp_dashboard_setup', 'clear_wp_dash');
function clear_wp_dash()
{
	$dash_side = &$GLOBALS['wp_meta_boxes']['dashboard']['side']['core'];
	$dash_normal = &$GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'];

	unset($dash_side['dashboard_quick_press']);   //Быстрая публикация
	unset($dash_side['dashboard_recent_drafts']); //Полседние черновики
	unset($dash_side['dashboard_primary']);       //Блог WordPress
	unset($dash_side['dashboard_secondary']);     //Другие Нновости WordPress

	unset($dash_normal['dashboard_incoming_links']);  //Входящие ссылки
	unset($dash_normal['dashboard_right_now']);       //Прямо сейчас
	unset($dash_normal['dashboard_recent_comments']); //Последние комментарии
	unset($dash_normal['dashboard_plugins']);         //Последние Плагины

	unset($dash_normal['dashboard_activity']);        // Активность
}
// Удаление подвала, информации о версии движка
add_action('admin_print_scripts-profile.php', 'hide_admin_bar_prefs');
function hide_admin_bar_prefs()
{
	if (!current_user_can('administrator')) {
		?>
		<style type="text/css">
			.update-nag,
			#contextual-help-link-wrap,
			#footer,
			#wpfooter {
				display: none;
			}
		</style>
		<?php
	}
}
/*УДАЛЯЕМ НЕНУЖНЫЕ ПУНКТЫ МЕНЮ В АДМИНКЕ*/
function remove_menus()
{
	$a = wp_get_current_user()->user_login;
	if ($a !== 'admin') {
		remove_menu_page('edit.php');
		remove_action('welcome_panel', 'wp_welcome_panel');
		remove_menu_page('index.php');                  //Консоль
		remove_menu_page('edit-comments.php');          //Комментарии
		remove_menu_page('themes.php');                 //Внешний вид
		//remove_menu_page( 'users.php' );                  //Пользователи
		remove_menu_page('tools.php');                  //Инструменты
		remove_menu_page('options-general.php');        //Настройки
		remove_menu_page('profile.php');        		  //Профиль
		remove_menu_page('upload.php');
	}
	//remove_menu_page( 'index.php' );                  //Консоль
	remove_menu_page('edit-comments.php');          //Комментарии
	//remove_menu_page( 'themes.php' );                 //Внешний вид
	//remove_menu_page( 'users.php' );                  //Пользователи
	//remove_menu_page( 'tools.php' );                  //Инструменты
	//remove_menu_page( 'options-general.php' );        //Настройки
}
add_action('admin_menu', 'remove_menus');


//SESSION START
function sess_start()
{
	if (!session_id()) {
		session_start();
	}
}

//LAST LOGIN
function user_last_login($user_login, $user)
{
	update_user_meta($user->ID, 'last_login', time());
}
add_action('wp_login', 'user_last_login', 10, 2);

//RAR
function additional_mime_types($mimes)
{
	$mimes['rar'] = 'application/x-rar-compressed';
	$mimes['swf'] = 'application/x-shockwave-flash';

	return $mimes;
}
add_filter('upload_mimes', 'additional_mime_types');

/*Сообщение про обновление*/
add_filter('pre_site_transient_update_core', function ($a) {
	return null;
});
wp_clear_scheduled_hook('wp_version_check');


//МИНИАТЮРЫ
function true_remove_default_image_sizes($sizes)
{
	unset($sizes['thumbnail']); // отключаем миниатюры
	unset($sizes['medium']); // отключаем средний размер
	unset($sizes['medium_large']); // отключаем средний размер
	unset($sizes['large']); // отключаем крупный размер
	// если вы не хотите отключать всё, можете закомментировать 1-2 строчки
	return $sizes;
}

add_filter('intermediate_image_sizes_advanced', 'true_remove_default_image_sizes');

//remove_action('wp_head', 'rel_canonical');

// отключение jQuery migrate begin

function my_remove_jquery_migrate($scripts)
{
	if (!is_admin() && isset($scripts->registered['jquery'])) {
		$script = $scripts->registered['jquery'];
		if ($script->deps) {
			$script->deps = array_diff(
				$script->deps,
				array(
					'jquery-migrate'
				)
			);
		}
	}
}
add_action('wp_default_scripts', 'my_remove_jquery_migrate');
// отключение jQuery migrate end

/*убрать canonical */
remove_action("wp_head", "rel_canonical");
function mayak_remove_prev_link($data)
{
	return false;
}
add_filter('aioseop_prev_link', 'mayak_remove_prev_link');
add_filter('aioseop_next_link', 'mayak_remove_next_link');
/*--убрать canonical */

$current_user = wp_get_current_user();
$user_role = !empty($current_user->roles) ? $current_user->roles[0] : '';

/*отключаем админбар*/
if ($user_role !== 'sales_manager' && $user_role !== 'administrator') {
	add_filter('show_admin_bar', '__return_false');
}

/*redirect wp-admin*/
if ($user_role !== 'sales_manager' && $user_role !== 'administrator') {
	/*wp-admin*/
	add_action('init', 'blockusers_init');
	function blockusers_init()
	{
		if (
			is_admin() && !current_user_can('administrator') && !(defined('DOING_AJAX') && DOING_AJAX)
		) {
			wp_redirect('/login-page/');
			exit;
		}
	}
	/*редирект с wp-login.php, на главную с окном авторизации*/
	function redirect_login_page()
	{
		$page_viewed = basename($_SERVER['REQUEST_URI']);

		if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
			wp_redirect(home_url('/login-page/'));
			exit;
		}
	}
	add_action('init', 'redirect_login_page');
}


//Добавляем новую роль пользователя - дизайнер
$result = add_role(
	'designer_architect',
	__(
		'Дизайнер-архитектор'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

/*
Роли контрагентов:
Дистрибьютор - distributor
Свободный дилер – free_dealer
Дилер под дистрибьютером – dealer	
Дизайнер Дилера – designer_dealer

Роли фабрики:
Консультант - consultant
Менеджер - manager
Рекламаторщик – complaint_handler
Узкие специалисты - specialist
Бухгалтер - bookkeeper
Менеджер отгрузок – manager_shipment
*/




//Добавляем новую роль пользователя - дистрибьютор
$result = add_role(
	'distributor',
	__(
		'Дистрибьютор'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'free_dealer',
	__(
		'Свободный дилер'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

//Добавляем новую роль пользователя - Менеджер дилера
$result = add_role(
	'designer_dealer',
	__(
		'Дизайнер дилера'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

//
//Добавляем новую роль пользователя - Менеджер дилера
$result = add_role(
	'manager',
	__(
		'Менеджер'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'consultant',
	__(
		'Консультант'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'complaint_handler',
	__(
		'Рекламаторщик'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'specialist',
	__(
		'Узкие специалисты'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'bookkeeper',
	__(
		'Бухгалтер'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'shipment_manager',
	__(
		'Менеджер отгрузок'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

$result = add_role(
	'sales_manager',
	__(
		'Менеджер по продажам'
	),
	array(
		'edit_themes' => false, // редактирование тем
		'install_plugins' => false, // установка плагинов
		'update_plugin' => false, // обновление плагинов
		'update_core' => false, // обновление ядра WordPress
		'read' => false,  // true разрешает эту возможность
		'edit_posts' => false,  // true разрешает редактировать посты
		'upload_files' => false,  // может загружать файлы
	)
);

//Меняем название роли подписчик - дилер
function wps_change_role_name()
{
	global $wp_roles;
	if (!isset($wp_roles))
		$wp_roles = new WP_Roles();
	$wp_roles->roles['subscriber']['name'] = 'Дилер';
	$wp_roles->role_names['subscriber'] = 'Дилер';
}
add_action('init', 'wps_change_role_name');


## Добавляет еще один вариант аватарки по умолчанию в настройки обсуждения
## Файл аватарки 'def-avatar.jpg' нужно залить в папку темы 'img'
add_filter('avatar_defaults', 'add_default_avatar_option');
function add_default_avatar_option($avatars)
{
	$url = get_template_directory_uri() . '/images/bluelogo.jpg';
	$avatars[$url] = 'Аватар сайта';
	return $avatars;
}

function redirectUrl()
{
	$user_role = determineUserRole();
	$contractor_role = ['distributor', 'free_dealer', 'dealer', 'designer_dealer'];
	$designer_role = ['designer_architect'];
	$redirect_url = "/contracts/";
	if (in_array($user_role, $contractor_role)) {
		$redirect_url = "/updates/";
	} elseif (in_array($user_role, $designer_role)) {
		$redirect_url = "/designer-conditions/";
	}
	echo "<script>console.log('$user_role');</script>";
	echo "<script>console.log('$redirect_url');</script>";
	return $redirect_url;
}

function determineUserRole()
{
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	return $user_role;
}
function setWebsiteUrl(){
	$websiteUrl = 'https://geosideal.ru';
	return $websiteUrl;
}

function rusSymbols(){
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
	return $rus;
}
function latSymbols(){
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');
	return $lat;
}

function replaceSymbols($string){
	$rus = rusSymbols();
	$lat = latSymbols();
	$string = str_replace($rus, $lat, $string);
	return $string;
}

function setVSite(){
	$version = '?v=1.1.2';
	return $version;
}
function setVPC()
{
	$version = '?v=1.8.2';
	return $version;
}