<?php
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
global $wp_hasher;
global $wpdb;
//$wp_hasher = new PasswordHash(16, FALSE);
$source = "vk";
$id = "7286223";
$key = "BJBPYK3jn51qKGnKv1cr";
$uri = "https://fishermap.org/wp-content/plugins/registration/api_login.php?from=vk";
$code = $_GET['code'];
$url_access = "https://oauth.vk.com/access_token?client_id=$id&client_secret=$key&redirect_uri=$uri&code=$code";

$obj = file_get_contents($url_access);
$json = json_decode($obj, true);

$acsess_token = $json["access_token"];
$user_id = $json["user_id"];
$user_email = $json["email"];

$more_info_url = "https://api.vk.com/method/users.get?user_ids=$user_id&fields=first_name,last_name,city,country,photo_max,photo_max_orig,contacts&access_token=$acsess_token&v=5.103";
$json_more = json_decode(file_get_contents($more_info_url), true);

$photo_max = $json_more['response'][0]['photo_max'];
$photo_max_orig = $json_more['response'][0]['photo_max_orig'];
$first_name = $json_more['response'][0]['first_name'];
$last_name = $json_more['response'][0]['last_name'];
$country = $json_more['response'][0]['country']['title'];
$city = $json_more['response'][0]['city']['title'];
$mobile_phone = $json_more['response'][0]['contacts']['mobile_phone'];

//Получили все данные, теперь херачим юзера
$user_name_eng = str_replace($rus, $lat, $first_name);
$user_lastname_eng = str_replace($rus, $lat, $last_name);
$user_login = $user_name_eng.$user_id;
$display_name = $user_name_eng . "_" . $user_lastname_eng;
$vklink = "https://vk.com/id" . $user_id;

$nowdate = date('Y-m-d H:i:s');

//Пустые поля заменяем
if(empty($city)){
	$city = "Город не указан";
}
if(empty($country)){
	$country = "Страна не указана";
}

//Проверяем польователя на наличие. Если не существует - регаем, если уже есть - логиним
$isuser = get_user_by('login', $user_login);
//$isuser = get_user_by('login', 'user');
if(!$isuser){
	//Придумываем пароль
	$rand = rand(10000, 99999);
	$user_password = "pass".$rand;
	$hash_password = wp_hash_password($user_password); // для дальнейшего логина
	
	$userdata = array(
		'user_pass'       => $user_password, // обязательно
		'user_login'      => $user_login, // обязательно
		'user_nicename'   => $display_name,
		'user_email'      => $user_email,
		'first_name'      => $first_name,
		'last_name'      => $last_name,
		'display_name'    => $display_name,
		'rich_editing'    => 'true', // false - выключить визуальный редактор
		'user_registered' => $nowdate, // дата регистрации (Y-m-d H:i:s) в GMT
	);
		
	$newuser = wp_insert_user( $userdata ) ;
	//	var_dump($newuser);
	// возврат
	if( ! is_wp_error( $newuser ) ) {
		add_user_meta( $newuser, 'first_name', $first_name, false );
		add_user_meta( $newuser, 'last_name', $last_name, false );
		add_user_meta( $newuser, 'vkavathumb', $photo_max, false );
		add_user_meta( $newuser, 'vkavaimage', $photo_max_orig, false );
		add_user_meta( $newuser, 'country', $country, false );
		add_user_meta( $newuser, 'vk', $vklink, false );
		add_user_meta( $newuser, 'mobile', $mobile_phone, false );
		add_user_meta( $newuser, 'city', $city, false );
		
		$credentials = array();
		$credentials['user_login'] = $user_login;
		$credentials['user_password'] = $user_password;
		wp_signon( $credentials, true );
		if(empty($myredirect)){
			$redirect_url = "/my-profile/";
		}
		else $redirect_url = $myredirect;
		wp_redirect($redirect_url);
		
	} else {
		echo $newuser->get_error_message();
	}
	
	
	
	
}
//ИНАЧЕ ЗАМЕНЯЕМ НЕКОТОРЫЕ ДАННЫЕ
else{

	$this_user_id = $isuser -> ID;
	$hash_password = $isuser -> user_pass;
	
	update_user_meta( $this_user_id, 'first_name', $first_name );
	update_user_meta( $this_user_id, 'last_name', $last_name );
	update_user_meta( $this_user_id, 'vkavathumb', $photo_max );
	update_user_meta( $this_user_id, 'vkavaimage', $photo_max_orig );
	update_user_meta( $this_user_id, 'city', $city );
	update_user_meta( $this_user_id, 'vk', $vklink );
	
	wp_set_auth_cookie($isuser -> ID);
	if(empty($myredirect)){
		$redirect_url = "/my-profile/";
	}
	else $redirect_url = $myredirect;
	wp_redirect($redirect_url);
}


/*
//Временно меняем пароль, чтоб залогиниться
$new_hashpassword = wp_hash_password("123456");
$newpassword = "123456";
$userdata = array(
		'ID'			  => $this_user_id,
 		'user_pass'       => $new_hashpassword, // обязательно
 		'user_login'       => $user_login
);
wp_insert_user( $userdata );

$credentials = array();
$credentials['user_login'] = 'user'; // ПОМЕНЯТЬ
$credentials['user_password'] = $newpassword;
$credentials['remember'] = true;
*/

//Теперь просто логинемся в админку
/*
wp_set_auth_cookie($isuser -> ID);
$redirect_url = "/wp-admin/admin.php?page=profile-options";
wp_redirect($redirect_url);
*/
/*$do_autorize = wp_signon( $credentials, true );
if ( is_wp_error($do_autorize) ) {
   echo $do_autorize->get_error_message();
}
else {
	$redirect_url = "/wp-admin/admin.php?page=profile-options";
	//Меняем пароль назад
	$sql = "UPDATE fm_users SET `user_pass` = '$hash_password' WHERE `ID` = '$this_user_id'";
	//$result=$wpdb->get_results($sql);
	wp_redirect($redirect_url);
}
*/

?>