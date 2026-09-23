<?php

if (!empty($Mailer) && !empty($CatcherBugs)) {
    error_log("Mailer or CatcherBugs not found");
    die("Error: Mailer or CatcherBugs not found");
}
// Load WordPress
$wp_load_path = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    error_log("WordPress load file not found at: " . $wp_load_path);
    die("Error: WordPress not found. Please check the installation.");
}
require_once $wp_load_path;

// Define the correct path to autoload.php
$autoloadPath = $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

// Check if the file exists before requiring it
if (!file_exists($autoloadPath)) {
    error_log("Autoload file not found at: " . $autoloadPath);
    die("Error: Required files not found. Please check the installation.");
}

require_once $autoloadPath;

use PersonalAccount\Workers\Mailer;
use PersonalAccount\Workers\CatcherBugs;
$Mailer = new Mailer();
$CatcherBugs = new CatcherBugs();

// Get raw input data
$rawData = file_get_contents('php://input');
// error_log("Raw input data in newSender: " . $rawData);
// $CatcherBugs->convPrintLog($rawData, 'Raw input data in newSender', '$rawData');
// $CatcherBugs->analizeChain('newSender', 'rawData', $rawData);
// Проверяем, что данные переданы

if (empty($rawData)) {
    error_log("No data received in request");
    die("Error: No data received");
}

// Декодирование данных
$dataArray = json_decode($rawData, true);

// Логирование результата
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log("JSON decode error in newSender: " . json_last_error_msg());
    error_log("Failed to decode data: " . $rawData);
    die("Error: Invalid data format");
}

// Проверяем, что данные декодировались корректно
if (empty($dataArray)) {
    error_log("Decoded data is empty in newSender");
    die("Error: No valid data after decoding");
}

error_log("Successfully decoded data in newSender: " . print_r($dataArray, true));

try {
    // Отправка данных по email
    $Mailer->sendToEmail($dataArray);
    echo "Email sent successfully";
} catch (Exception $e) {
    error_log("Error sending email in newSender: " . $e->getMessage());
    die("Error: Failed to send email");
}


