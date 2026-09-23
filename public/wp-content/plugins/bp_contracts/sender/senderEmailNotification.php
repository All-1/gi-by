<?php
// Получаем данные из POST-запроса
$to = isset($_POST['email']) ? $_POST['email'] : '';
$message = isset($_POST['message']) ? $_POST['message'] : '';
$subject = isset($_POST['subject']) ? $_POST['subject'] : '';

// Параметры для отправки email
$headers = "From: gi@gi.by\r\n";

// Проверка наличия всех необходимых данных
if (!empty($to) && !empty($message) && !empty($subject)) {
  // Отправка email
  $mailSent = mail($to, $subject, $message, $headers);

  // Если письмо отправлено успешно
  if ($mailSent) {
    // error_log("Сообщение отправлено успешно!");
    // echo "Сообщение отправлено успешно!";
  } else {
    error_log("Ошибка при отправке сообщения.");
  }
} else {
  error_log("Ошибка: все поля не были переданы!");
}
?>