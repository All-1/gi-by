<?php
if (isset($_POST['message']) AND isset($_POST['subject'])){
		$message = $_POST['message'];
		$subject = $_POST['subject'];
		$headers = "Content-type: text/html; charset=utf-8\r\n";
		mail('alorian.all1@gmail.com', $subject, $message, $headers);
	}
	$response = [
        'success' => true,
        'message' => 'Запрос успешно отправлен.'
    ];

    // Преобразуем массив в JSON и отправляем на клиент
    header('Content-Type: application/json');
    echo json_encode($response);
?>