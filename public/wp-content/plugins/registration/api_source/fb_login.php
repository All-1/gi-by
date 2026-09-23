<?php
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я');
$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya');
global $wp_hasher;
global $wpdb;


$source = "fb";
$id = "208345833669126";
$uri = "https://fishermap.org/wp-content/plugins/registration/api_login.php?from=fb";
$client_secret = "85ce6dc7abc392db46f3f6d80b78fc37";
$code = $_GET['code'];
$url_access = "https://graph.facebook.com/v5.0/oauth/access_token?client_id=$id&redirect_uri=$uri&client_secret=$client_secret&code=$code";
//$url_access = "https://graph.facebook.com/v5.0/oauth/access_token?client_id=$id&redirect_uri=$uri&client_secret=$client_secret&grant_type=client_credentials"; //apptoken!

$obj = file_get_contents($url_access);
$json = json_decode($obj, true);

$access_token = $json['access_token'];


/*робим*/

require_once __DIR__ . '/Facebook/autoload.php'; // change path as needed

$fb = new Facebook\Facebook([
  'app_id' => $id,
  'app_secret' => $client_secret,
  'default_graph_version' => 'v5.0',
  ]);

try {
  // Returns a `Facebook\FacebookResponse` object
  $response = $fb->get('/me?fields=id,first_name,last_name,address,email', $access_token);
  //$response = $fb->get('/me?fields=id,picture,first_name,last_name,email,short_name', $access_token);
} catch(Facebook\Exceptions\FacebookResponseException $e) {
  echo 'Graph returned an error: ' . $e->getMessage();
  exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
  echo 'Facebook SDK returned an error: ' . $e->getMessage();
  exit;
}

$user = $response->getGraphUser();
//var_dump($user);
$user_id = $user['id'];
$first_name = $user['first_name'];
$last_name = $user['last_name'];
$address = $user['address'];
$user_email = $user['email'];
//$profile_link = "https://www.facebook.com/profile.php?id=$user_id";

//тут можно подумать, как в верхней части скрипта можно вытянуть картинку, ибо там 50х50 малявка, а пока отдельным запросом
$fb_avatars_dir = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/plugins/registration/images/facebook_avatars/' . $user_id . '.jpg';
$fb_avatars_link = '/wp-content/plugins/registration/images/facebook_avatars/' . $user_id . '.jpg';
$image_url = "https://graph.facebook.com/v5.0/$user_id/picture?width=600&height=800";
file_put_contents($fb_avatars_dir, file_get_contents($image_url));

$num_for_login = substr($user_id, 0 , 5);
$user_name_eng = str_replace($rus, $lat, $first_name);
$user_lastname_eng = str_replace($rus, $lat, $last_name);
$user_login = $user_name_eng.$num_for_login;
$display_name = $user_name_eng . "_" . $user_lastname_eng;

$nowdate = date('Y-m-d H:i:s');

$city = "Город не указан";
$country = "Страна не указана";



//Проверяем польователя на наличие. Если не существует - регаем, если уже есть - логиним
$isuser = get_user_by('login', $user_login);

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
		add_user_meta( $newuser, 'vkavathumb', $fb_avatars_link, false );
		add_user_meta( $newuser, 'vkavaimage', $fb_avatars_link, false );
		add_user_meta( $newuser, 'country', $country, false );
		//add_user_meta( $newuser, 'vk', $vklink, false );
		//add_user_meta( $newuser, 'mobile', $mobile_phone, false );
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
	update_user_meta( $this_user_id, 'vkavathumb', $fb_avatars_link );
	update_user_meta( $this_user_id, 'vkavaimage', $fb_avatars_link );
	update_user_meta( $this_user_id, 'city', $city );
	//update_user_meta( $this_user_id, 'vk', $vklink );
	
	wp_set_auth_cookie($isuser -> ID);
	if(empty($myredirect)){
		$redirect_url = "/my-profile/";
	}
	else $redirect_url = $myredirect;
	wp_redirect($redirect_url);
}



?>