<?php
// Ваш PHP-скрипт для обработки данных
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);

    // Ваш код для обработки данных $postData

    // Пример отправки ответа (в данном случае возвращаем пустой JSON-объект)
    echo json_encode(['status' => 'success']);
} else {
    // Возвращаем ошибку, если метод запроса не POST
    echo json_encode(['error' => 'Invalid request method']);
}
?>