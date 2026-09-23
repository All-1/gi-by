<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

use WebSocket\Client;
// Создаем клиент WebSocket

echo "Подключение успешно установлено<br>";
try {
    if (isset($_GET['logId'])) {
        // Получаем значение параметра `id`
        $id = $_GET['logId'];
        $request = 'Orders';
    } elseif (isset($_GET['logIdOrder'])) {
        $id = $_GET['logIdOrder'];
        $request = 'Orders';
    } elseif (isset($_GET['logIdDealer'])) {
        // Получаем значение параметра `id`
        $id = $_GET['logIdDealer'];
        $request = 'Dealer';
    } elseif (isset($_GET['logIdPoint'])) {
        // Получаем значение параметра `id`
        $id = $_GET['logIdPoint'];
        $request = 'Point';
    } elseif (isset($_GET['logIdFirm'])) {
        // Получаем значение параметра `id`
        $id = $_GET['logIdFirm'];
        $request = 'Firm';
    } elseif (isset($_GET['logIdInvoice'])) {
        // Получаем значение параметра `id`
        $id = $_GET['logIdInvoice'];
        $request = 'Invoice';
    } 
    if (!empty($id) && !empty($request)) {
        // $client = new Client("wss://test.gi.by/wss/");
        // $client = new Client("wss://dev.gi.by/wss/");
        // $client = new Client("ws://127.0.0.1:8080");
        $client = new Client("wss://geosideal.ru/wss/");
    }
    if ($client) {
        echo "Получен ID: $id<br>";
        $request = "requestUpdate" . $request . "UP::: ";
        echo "Данные отправлены:  $id<br>";
        $client->send($request . $id);
        // Получаем ответ от сервера
        // $response = $client->receive();
        // echo "Ответ от сервера: $response<br>";
    }

} catch (Exception $e) {
    echo "Ошибка при работе с WebSocket: " . $e->getMessage() . "<br>";
}

?>