<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
global $lang_adm;
global $wpdb;

if (isset($_POST['action']) && $_POST['action'] === 'reset_points') {
    $user_id = get_current_user_id();

    $sql = "UPDATE gi_points SET ManagerIdTemp_gi 
        = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', ManagerIdTemp_gi, ','), ',$user_id,', ',')) 
        WHERE FIND_IN_SET('$user_id', ManagerIdTemp_gi) > 0";

    $wpdb->query($sql);

    wp_send_json(['success' => true, 'message' => 'Точки успешно сброшены.']);
} else {
    wp_send_json(['success' => false, 'message' => 'Некорректный запрос.']);
}
function newFunction()
{
}
;
