<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
global $lang_adm;
global $wpdb;

// Проверяем, что данные пришли и это массив
if (isset($_POST['selectedPoints'])) {
    // Принимаем данные из AJAX-запроса
    $selectedPoints = json_decode(stripslashes($_POST['selectedPoints']));

    // Получаем текущего пользователя (менеджера)
    $user_id = get_current_user_id();

    // Создаем строку с ID точек
    $points_string = implode(',', $selectedPoints);

    foreach ($selectedPoints as $point_id) {
        $sql = "UPDATE gi_points SET ManagerIdTemp_gi = 
            CASE 
                WHEN ManagerIdTemp_gi IS NULL OR ManagerIdTemp_gi = '' THEN '$user_id' 
                WHEN FIND_IN_SET('$user_id', ManagerIdTemp_gi) = 0 THEN CONCAT_WS(',', ManagerIdTemp_gi, '$user_id')
                ELSE ManagerIdTemp_gi
            END
            WHERE PKId_gi = $point_id";

        $result = $wpdb->query($sql);
    }

    // Удаляем user_id из ManagerIdTemp_gi, включая запятые, если point_id не включено в $selectedPoints
    $sql_delete =
        "UPDATE gi_points SET ManagerIdTemp_gi = 
		    TRIM(BOTH ',' FROM REPLACE(CONCAT(',', ManagerIdTemp_gi, ','), ',$user_id,', ',')) 
		WHERE PKId_gi NOT IN (" . implode(',', $selectedPoints) . ") 
		               AND FIND_IN_SET('$user_id', ManagerIdTemp_gi) > 0";

    $result_delete = $wpdb->query($sql_delete);



    // Отправляем ответ обратно на клиентскую сторону
    wp_send_json(['success' => true, 'message' => 'Точки успешно сохранены.']);
} else {
    wp_send_json(['success' => false, 'message' => 'Некорректные данные.']);
}
