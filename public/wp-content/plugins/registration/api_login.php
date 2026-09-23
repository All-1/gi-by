<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
$from = $_GET['from'];
$myredirect = $_SESSION['logined_from_page'];

switch($from){
	case "vk": require_once plugin_dir_path(__FILE__) . "/api_source/vk_login.php"; break;
	case "fb": require_once plugin_dir_path(__FILE__) . "/api_source/fb_login.php"; break;
}

?>