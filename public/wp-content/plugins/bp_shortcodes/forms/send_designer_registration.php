<?php
if (isset($_POST['username']) AND isset($_POST['phone']) AND isset($_POST['city']) AND isset($_POST['mail'])){
		$username = $_POST['username'];
		$phone = $_POST['phone'];
		$city = $_POST['city'];
		$mail = $_POST['mail'];
		$message = "<h1>Поступил запрос на регистрацию дизайнера-интерьерщика</h1> 
		<b>Имя: </b> $username <br>
		<b>Телефон: </b> $phone <br>
		<b>Город: </b> $city <br>
		<b>Почта: </b> $mail";
		$subject = "Запрос на регистрацию дизайнера-интерьерщика";
		$headers = "Content-type: text/html; charset=utf-8\r\n";
		mail('info@gi.by', $subject, $message, $headers);
	}
	$response = [
        'success' => true,
        'message' => 'Запрос успешно отправлен.'
    ];

    // Преобразуем массив в JSON и отправляем на клиент
    header('Content-Type: application/json');
    echo json_encode($response);
?>