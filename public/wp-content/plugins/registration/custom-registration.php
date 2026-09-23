<?php

/*
	Plugin Name: Custom Registration
	Description: Updates user rating based on number of posts.
	Version: 1.0
	Author: Agbonghama Collins
 */
// Register a new shortcode: [cr_custom_registration]
add_shortcode('cr_custom_registration', 'custom_registration_shortcode');

add_shortcode('social_registration_buttons', 'social_registration_buttons');
add_shortcode('login_form', 'login_form');


function custom_registration_function()
{
	if (isset($_POST['submit'])) {
		registration_validation(
			$_POST['username'],
			$_POST['password'],
			$_POST['email']
		);

		// sanitize user form input
		global $username, $password, $email;
		$username = sanitize_user($_POST['username']);
		$password = esc_attr($_POST['password']);
		$email = sanitize_email($_POST['email']);

		// call @function complete_registration to create the user
		// only when no WP_error is found
		complete_registration(
			$username,
			$password,
			$email
		);
	}

	registration_form(
		$username,
		$password,
		$email
	);
}

function registration_form($username, $password, $email)
{
	$a = "<script src='https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit' async defer></script>";
	$a .= "
	<div class='reg_div'>
	<a href='https://fishermap.org'>	
		<img src='../wp-content/uploads/2019/02/small_logo_grey.png' style='width:400px; max-width:90%; margin:32px 0 0 -8px;'/>
	</a>
	<p><b>Уже есть аккаунт? <a href='/login-page/'>Перейти на страницу входа</a></b></p>
	<p style='font-size:13px;'>
	Регистрируя аккаунт, вы автоматически соглашаетесь с <a href='/politika/' target='_blank'>политикой конфиденциальности</a>. Ниже вы можете выбрать наиболее удобный вам способ регистрации.</a>.
	</p>
	<p style='font-size:22px; color:black;'>Регистрация через:</p>
	";
	$a .= do_shortcode('[social_registration_buttons]');
	$a .= "
	<p style='font-size:22px; color:black; margin-top:48px;'>Зарегистрировать новый аккаунт</p>
	";

	$a .= "
	
	 <script type='text/javascript'>
      var onloadCallback = function() {
        grecaptcha.render('html_element', {
          'sitekey' : '6Ld4LbcZAAAAAJgulxfHqsJRQY1u4wX9edtUK675',
		  'callback' : verifyCallback,
		  'expired-callback' : AgainCallback
        });
      };
	  var verifyCallback = function(response) {
		var regbtn = \"<input type='submit' name='submit' id='submitbtn' class='myBtn regBtn' value='Регистрация'/>\";
		var submitbtn = document.getElementById('submitbtn');
		if(submitbtn){
			submitbtn.remove();
		}
		jQuery('#reg_form').append(regbtn);
		jQuery('#fakebtn').hide();
      };
	  function zapolni(){
		  alert('Заполните форму регистрации!');
	  }
	  
	  var AgainCallback = function(){
		 var submitbtn = document.getElementById('submitbtn');
		 if(submitbtn){
			submitbtn.remove();
		 }
		 jQuery('#fakebtn').show();
	  }
    </script>
	
	";
	$a .= '
	<div id="registr_result"></div>
    <form action="' . $_SERVER['REQUEST_URI'] . '" method="post" id="reg_form">
	<div>
	<label class="reg_label" for="username">Логин<strong>*</strong></label>
	<input required class="reg_input" type="text" name="username" value="' . (isset($_POST['username']) ? $username : null) . '">
	</div>
	
	<div>
	<label class="reg_label" for="password">Пароль<strong>*</strong></label>
	<input required class="reg_input" type="password" name="password" value="' . (isset($_POST['password']) ? $password : null) . '">
	</div>

	
	<div>
	<label class="reg_label" for="email">Email <strong>*</strong></label>
	<input required class="reg_input" type="text" name="email" value="' . (isset($_POST['email']) ? $email : null) . '">
	</div>

	<div>
	<label class="reg_label" for="country">Страна <strong>*</strong></label>
	<input class="reg_input" type="text" name="country" required>
	</div>
	<br>
	<div id="html_element"></div>
	
	</form>
	<button class="myBtn regBtn" id="fakebtn" style="background:#a8a8a8; border:1px solid #a8a8a8; cursor:auto;" onclick="zapolni();">Регистрация</button>

	</div>
	';

	echo $a;
}

function registration_validation($username, $password, $email)
{
	global $reg_errors;
	$reg_errors = new WP_Error;

	if (empty($username) || empty($password) || empty($email)) {
		$reg_errors->add('field', 'Required form field is missing');
	}

	if (strlen($username) < 4) {
		$reg_errors->add('username_length', 'Логин слишком короткий. Требуется минимум 4 символа');
	}

	if (username_exists($username))
		$reg_errors->add('user_name', 'Извините, этот логин уже существует!');

	if (!validate_username($username)) {
		$reg_errors->add('username_invalid', 'Введенный вами логин недействителен. Проверьте правильность написания');
	}

	if (strlen($password) < 5) {
		$reg_errors->add('password', 'Длина пароля должна быть больше 5');
	}

	if (!is_email($email)) {
		$reg_errors->add('email_invalid', 'Email не является допустимым');
	}

	if (email_exists($email)) {
		$reg_errors->add('email', 'Этот электронный адрес уже занят');
	}

	if (is_wp_error($reg_errors)) {

		foreach ($reg_errors->get_error_messages() as $error) {
			$b = '<div>' . '<strong>ERROR</strong>:' . $error . '<br/>' . '</div>';
		}

	}
	echo "
	<script>
		jQuery.ajax({
			url: '/wp-content/plugins/registration/registr_result.php',
			type: 'POST',
			data: {result:'$b'},
			dataType: 'html',
			success: function(data){ 
				jQuery('#registr_result').html(data);
			}
		});				
	</script>";
}

function complete_registration()
{
	global $reg_errors, $username, $password, $email;
	if (count($reg_errors->get_error_messages()) < 1) {
		$userdata = array(
			'user_login' => $username,
			'user_email' => $email,
			'user_pass' => $password
		);
		$user = wp_insert_user($userdata);
		if (!empty($_POST['country'])) {
			global $wpdb;
			$sql = "SELECT ID FROM fm_users WHERE user_login = '$username'";
			$result = $wpdb->get_results($sql);
			foreach ($result as $row) {
				$user_id = $row->ID;
			}
			$meta_key = 'country';
			$meta_value = $_POST['country'];
			add_user_meta($user_id, $meta_key, $meta_value, false);
		}
		$c = 'Регистрация выполнена!. Перейдите на <a href="' . get_site_url() . '/login-page/">страницу входа</a>.<br><br>';
		echo "<script>
				jQuery.ajax({
					url: '/wp-content/plugins/registration/registr_result.php',
					type: 'POST',
					data: {result:'$c'},
					dataType: 'html',
					success: function(data){ 
						jQuery('#registr_result').html(data);
					}
				});				
			</script>";
	}
}



// The callback function that will replace [book]
function custom_registration_shortcode()
{
	ob_start();
	custom_registration_function();
	return ob_get_clean();
}

function social_registration_buttons()
{
	$template = get_template_directory_uri();
	$a = "
	<div style='width:100%;'>
		<a href='https://oauth.vk.com/authorize?client_id=7286223&display=page&redirect_uri=https://fishermap.org/wp-content/plugins/registration/api_login.php?from=vk&scope=notify,photos,email&response_type=code&v=5.103'>
			<img class='social_image' style='max-width:45px;' src='$template/images/vk.png'/>
		</a>
		<a href='https://www.facebook.com/v5.0/dialog/oauth?client_id=208345833669126&redirect_uri=https://fishermap.org/wp-content/plugins/registration/api_login.php?from=fb&state=fish123er45map67&response_type=granted_scopes,code'>
			<img class='social_image' style='max-width:45px;' src='$template/images/facebook.png'/>
		</a>
	</div>
	";
	return $a;
}

function login_form()
{

	$bloginfo = 'https://gi.by';
	$a = "<script src='https://smartcaptcha.yandexcloud.net/captcha.js' defer></script>";
	$a .= "
	
	 <script type='text/javascript'>
      

      // var onloadCallback = function() {
      //   grecaptcha.render('html_element', {
      //     'sitekey' : '6LcuvLkZAAAAAH-ODI9SDnmSeDvgOdOHAQOfMEND',
		  // 'callback' : verifyCallback,
		  // 'expired-callback' : AgainCallback
      //   });
      // };
	  var verifyCallback = function(response) {
		
	  }

		function callback(token) {
				var regbtn = \"<input type='submit' name='submit' id='submitbtn' class='myBtn regBtn' style='background:#e78c68; color:#fff; cursor:pointer; padding:16px 32px; border:none; margin-bottom:32px;' value='Войти'/>\";
				var submitbtn = document.getElementById('submitbtn');
				document.getElementById('smart-token').value = token;
		
				if(submitbtn){
					submitbtn.remove();
				}
				jQuery('#inputs').append(regbtn);
				jQuery('#fakebtn').hide();
    };
	  function zapolni(){
		  alert('Заполните все поля!');
	  }
	  
	  var AgainCallback = function(){
		 var submitbtn = document.getElementById('submitbtn');
		 if(submitbtn){
			submitbtn.remove();
		 }
		 jQuery('#fakebtn').show();
		}
    </script>
	
	";
	$a .= "
	<div class='reg_div'>
		<a href='https://gi.by'>	
			<img src='../wp-content/uploads/2019/11/logo12.png' style='width:85px; max-width:90%; margin:32px 0 32px 0;'/>
		</a>
	";
	$action = "/wp-login.php";
	$a .= "
		<p style='font-size:24px; color:black; margin-bottom:16px;'>Вход в личный кабинет </p>
		<div id='response_div'></div>
		<div id='login_tp'>
		<form id='inputs' name='loginform'  method='post' action='/wp-content/plugins/registration/login_checking.php'>
			<input required class='logininput' type='text' name='log' id='log' placeholder='Логин или mail'  >
			<input required class='logininput' type='password' name='pwd' id='pwd'  placeholder='Пароль' ><br><br>
			<div id='captcha-container'></div>
			<div
			id='captcha-container'
				class='smart-captcha'
				data-sitekey='ysc1_XwqV4Bp2fkNJRCoOwkx9ikweDRHTO5TQYxCnmxzZ2c4cf852'
				data-hl='ru'
				data-callback='callback'
			></div>
			<input type='hidden' name='smart-token' id='smart-token' value=''>
			<br><br>
		</form>
		<button class='myBtn regBtn' id='fakebtn' style='margin-bottom:32px; background:transparent; border:1px solid #083c65; color:#083c65; cursor:auto;' onclick='zapolni();'>Войти</button>
		
		</div>  
	</div>
	";

	$a .= "
	<script>
	jQuery('form').submit(function(){
		event.preventDefault();
		console.log('here');
		var form = jQuery(this);
		var url = form.attr('action');
		jQuery.ajax({
			type: 'POST',
			url: url,
			data: form.serialize(), // serializes the form's elements.
			success: function(data)
			{
				jQuery('#response_div').html(data);
			}
		});
	});
	</script>
	";
	return $a;
}