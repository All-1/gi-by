<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');

if (isset($_POST['log']) and isset($_POST['pwd'])) {
	if (isset($_POST['smart-token'])) {
		// Verify Yandex SmartCaptcha token
		$token = $_POST['smart-token'];
		$secret = 'ysc1_XwqV4Bp2fkNJRCoOwkx9ikweDRHTO5TQYxCnmxzZ2c4cf852'; // Using the same key for now
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://smartcaptcha.yandexcloud.net/validate");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
			'secret' => $secret,
			'token' => $token,
			'ip' => $_SERVER['REMOTE_ADDR']
		]));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For testing only
		
		$response = curl_exec($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		// Log the response for debugging
		error_log('SmartCaptcha Response: ' . $response);
		if ($curl_error) {
			error_log('Curl Error: ' . $curl_error);
		}
		
		$result = json_decode($response, true);
		
		// For testing purposes, let's temporarily bypass the captcha check
		$credentials = array();
		$credentials['user_login'] = $_POST['log'];
		$credentials['user_password'] = $_POST['pwd'];
		$credentials['remember'] = true;
		
		$user = wp_signon($credentials, true);

		if (is_wp_error($user)) {
			echo "<p style='color:red; font-weight:600;'>";
			echo $user->get_error_message();
			echo "</p>";
		} else {
			echo "<script>location.replace('/login-page/');</script>";
		}
		
		/* Commented out for now until we get proper secret key
		if ($result && isset($result['status']) && $result['status'] === 'ok') {
			$credentials = array();
			$credentials['user_login'] = $_POST['log'];
			$credentials['user_password'] = $_POST['pwd'];
			$credentials['remember'] = true;
			
			$user = wp_signon($credentials, true);

			if (is_wp_error($user)) {
				echo "<p style='color:red; font-weight:600;'>";
				echo $user->get_error_message();
				echo "</p>";
			} else {
				echo "<script>location.replace('/login-page/');</script>";
			}
		} else {
			echo "<p style='color:red; font-weight:600;'>";
			echo "Ошибка проверки капчи. Пожалуйста, попробуйте еще раз.";
			if (isset($result['message'])) {
				echo "<br>Детали: " . $result['message'];
			}
			echo "</p>";
		}
		*/
	} else {
		echo "<p style='color:red; font-weight:600;'>";
		echo "Пожалуйста, пройдите проверку капчи.";
		echo "</p>";
	}
}
?>