<?php
require_once ($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';

global $lang_adm;
global $wpdb;

$Id = get_current_user_id();
date_default_timezone_set('Europe/Minsk');
$date_send = date('Y-m-d H:i:s');

//Личные данные того кто отправил запрос на сервер
$first_name = get_user_meta($Id, 'first_name', true);
$last_name = get_user_meta($Id, 'last_name', true);
$company = get_user_meta($Id, 'company', true);
$avatar = get_avatar($Id, 64);

$user_data = get_userdata($Id);
$user_role = $user_data->roles[0];
if ($user_role === 'subscriber') {
    $user_role = 'dealer';
}

$id_user = $wpdb->get_var("SELECT DealerId_gi FROM gi_users WHERE PKId_gi = $Id");
$id_point;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //$data = json_decode(file_get_contents('php://input'), true); // Чтение данных из тела запроса в формате JSON, чуть позже всё будет реализовано через JSON формат.
    $type_dialog = isset ($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
    $serial_number = isset ($_POST['serialNumber']) ? sanitize_text_field($_POST['serialNumber']) : '';
    $message = isset ($_POST['message']) ? sanitize_text_field($_POST['message']) : '';
    $id_dialog = isset ($_POST['idDialog']) ? sanitize_text_field($_POST['idDialog']) : null;
    $id_message_redact = isset ($_POST['idMessage']) ? sanitize_text_field($_POST['idMessage']) : null;
    $old_files_save = isset ($_POST['oldFilesSave']) ? sanitize_text_field($_POST['oldFilesSave']) : null;
    $status = isset ($_POST['status']) ? sanitize_text_field($_POST['status']) : null;
    $date_contract = $wpdb->get_var("SELECT date_creation FROM gi_contracts WHERE sn = $serial_number");
    $date = new DateTime($date_contract);
    $year_contract = $date->format('Y');
    $id_point = $id_user ? $wpdb->get_var("SELECT PKId_gi FROM gi_points WHERE DealerId_gi = $id_user") : $wpdb->get_var("SELECT id_point FROM gi_contracts WHERE sn = $serial_number");
    $table_name = 'x_gi_contracts_in_point_' . $id_point . '_' . $year_contract;
    //Проверка на существование в DB сообщения в рамках данного диалога и пришло ли от клиента редактирование сообщения.
    $sql_last_id_message = $wpdb->get_var("SELECT MAX(id_message) FROM `$table_name` WHERE sn = '$serial_number' AND id_dialog = '$id_dialog'");
    $sql_last_id_message = intval($sql_last_id_message);
    $sql_last_id_author = $wpdb->get_var("SELECT id_author FROM `$table_name` WHERE sn = '$serial_number' AND id_dialog = '$id_dialog' AND id_message = '$sql_last_id_message'");
    $sql_last_id_author = intval($sql_last_id_author);
    $user_data_last_message = get_userdata($sql_last_id_author);
    $user_role_last_message = $user_data_last_message->roles[0];
    if ($user_role_last_message === 'subscriber') {
        $user_role_last_message = 'dealer';
    }

    $type_for_db = '';
    switch ($type_dialog) {
        case 'Консультация':
            $type_for_db = 'consultation';
            break;
        case 'Заказ':
            $type_for_db = 'order';
            break;
        case 'Рекламация':
            $type_for_db = 'complaint';
            break;
    }


    $response = [];
    //Если прилетело что сообщение прочитали.
    if ($status === 'readed') {
        $id_message = intval($id_message_redact);
        $serial_number = intval($serial_number);
        $id_dialog = intval($id_dialog);

        if (
            (($user_role === 'dealer' || $user_role === 'distributor') && $user_role_last_message === 'manager')
            || (($user_role_last_message === 'dealer' || $user_role_last_message === 'distributor') && $user_role === 'manager')
        ) {
            $sql_update_readed_message = "UPDATE `$table_name` SET message_status = '$status', date_readed = '$date_send ' WHERE id_message <= '$id_message' AND id_dialog = '$id_dialog' AND sn='$serial_number' AND message_status = 'unreaded'";
            $wpdb->query($sql_update_readed_message);
            $sql_select_after_update = "SELECT * FROM `$table_name` WHERE id_message = '$id_message' AND id_dialog='$id_dialog' AND sn='$serial_number'";
            $result_select_after_update = $wpdb->get_results($sql_select_after_update);
        }

        foreach ($result_select_after_update as $row) {
            $serial_number = $row->sn;
            $id_dialog = $row->id_dialog;
            $type_dialog = $row->type_dialog;
            $id_message = $row->id_message;
            $id_author = $row->id_author;
            $date_send_message = $row->date_send;
            $message = $row->message_body;
            $json_uploaded_files = $row->files;
            $status = $row->message_status;
            //Личные данные автора сообщения, чьё сообщение было прочитано
            if ($id_author) {
                $first_name = get_user_meta($id_author, 'first_name', true);
                $last_name = get_user_meta($id_author, 'last_name', true);
                $company = get_user_meta($id_author, 'company', true);
                $avatar = get_avatar($id_author, 64);
            }
        }

        $user_data = get_userdata($id_author);
        $user_role_author = $user_data->roles[0];
        if ($user_role_author === 'subscriber') {
            $user_role_author = 'dealer';
        }

        $date_send_time_stamp = convert_timestamp_index($date_send_message);
        //Ответ клиенту.
        $response = [
            'serialNumber' => $serial_number,
            'idDialog' => $id_dialog,
            'typeDialog' => $type_dialog,
            'idMessage' => $id_message,
            'idAuthor' => $id_author,
            'firstNameAuthor' => $first_name,
            'lastNameAuthor' => $last_name,
            'companyAuthor' => $company,
            'avatarAuthor' => $avatar,
            'IdUser' => $Id,
            'userRole' => $user_role,
            'userRoleAuthor' => $user_role_author,
            'dateSend' => $date_send_message,
            'dateSendTimeStamp' => $date_send_time_stamp,
            'message' => $message,
            'filesJSON' => $json_uploaded_files,
            'status' => $status,
            'updateContract' => $update_contracts_data,
        ];

    } else {
        // Выполняем запрос есть ли у нас вообще нужная таблица в БД, если нет создаём
        $query = $wpdb->prepare("SHOW TABLES LIKE %s", $wpdb->esc_like($table_name));
        $table_exists = $wpdb->get_var($query);
        if (!$table_exists) {
            $sql_create = "CREATE TABLE {$table_name} (
            id INT NOT NULL AUTO_INCREMENT,
            sn INT NOT NULL DEFAULT 0,
            id_dialog INT NOT NULL DEFAULT 0,
            type_dialog VARCHAR(999) NOT NULL DEFAULT '',
            id_message INT NOT NULL DEFAULT 0,
            id_author INT NOT NULL DEFAULT 0,
            date_send DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            message_body TEXT NOT NULL DEFAULT '',
            files TEXT NOT NULL DEFAULT '',
            message_status VARCHAR(999) NOT NULL DEFAULT '',
            date_readed DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci";
            require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql_create);
        }

        $uploaded_files = [];
        $send_files = [];
        //Проверяем есть ли старые файлы или только новые. Надо для того чтобы реализовать удаление файлов которые были удалены пользователем из файловой системы на сервере.
        if ($old_files_save) {
            $old_files_save = explode(',', $old_files_save);

            foreach ($old_files_save as $file) {
                $uploaded_files[] = $file;
            }
        }
        //Работа с файлами, сохранение их на сервер и создание ссылок для хранения Массива в текстовом формате в DB
        if (!empty ($_FILES['fileInput']['name'][0])) {

            //Подгрузка всех файлов на сервер.
            foreach ($_FILES['fileInput']['tmp_name'] as $index => $tmp_name) {
                $file_name = basename($_FILES['fileInput']['name'][$index]);
                $uploadDirectory1 = ABSPATH . 'wp-content/uploads/contracts/' . $year_contract;
                $uploadDirectory = ABSPATH . 'wp-content/uploads/contracts/' . $year_contract . '/serial_number_' . $serial_number;

                $file_url = $uploadDirectory . '/' . $file_name;
                $path_for_db = '/wp-content/uploads/contracts/' . $year_contract . '/serial_number_' . $serial_number;
                $file_url_for_db = $path_for_db . '/' . $file_name;
                $prefix = 1;
                if (!is_dir($uploadDirectory1)) {
                    mkdir($uploadDirectory1, 0774, true);
                }
                if (!is_dir($uploadDirectory)) {
                    mkdir($uploadDirectory, 0774, true);
                }
                //Проверка существования файла. Если файл уже есть сохраняем его с prefix
                while (file_exists($file_url)) {
                    $filename_without_extension = pathinfo($file_name, PATHINFO_FILENAME);
                    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                    $file_name_new = $filename_without_extension . "($prefix)" . '.' . $file_extension;
                    $file_url = $uploadDirectory . '/' . $file_name_new;
                    $file_url_for_db = $path_for_db . '/' . $file_name_new;
                    ++$prefix;

                }
                if (move_uploaded_file($tmp_name, $file_url)) {
                    $uploaded_files[] = $file_url_for_db;
                }
                $send_files[] = $file_name;
            }
        }
        //Подготовка для отправки на клиент.
        $json_uploaded_files = json_encode($uploaded_files);

        //Проверка сообщение отправлено в рамках диалога или надо создавать новый.
        if (!$id_dialog) {
            $sql_select_id_dialog = $wpdb->get_var("SELECT MAX(id_dialog) FROM `$table_name` WHERE sn = '$serial_number'");
            $id_dialog = ($sql_select_id_dialog !== null) ? ($sql_select_id_dialog + 1) : 1;
        }


        // if (!$id_message_redact) {
        //     $id_message = ($sql_last_id_message !== null) ? ($sql_last_id_message + 1) : 1;
        // } else {
        //     $id_message_redact = intval($id_message_redact);
        //     $id_message = $id_message_redact;
        // }
        $id_message = ($sql_last_id_message !== null) ? ($sql_last_id_message + 1) : 1;
        $status = 'unreaded';

        //Проверка перед редактированием сообщения. Можно по другому но уже лень переписывать.
        // if ($id_message_redact === $id_message) {
        //     $sql_update_redact_message = "UPDATE `$table_name` SET message_body='$message', files='$json_uploaded_files', date_send='$date_send', message_status = '$status' WHERE id_message = '$id_message' AND id_dialog='$id_dialog' AND sn='$serial_number'";
        //     $wpdb->query($sql_update_redact_message);
        // } else {
        //     $sql_insert_this_message = $wpdb->prepare(
        //         "INSERT INTO `$table_name` 
        //     (sn, id_dialog, type_dialog, id_message, id_author, date_send, message_body, files, message_status)
        //     VALUES 
        //     (%s, %d, %s, %d, %d, %s, %s, %s, %s)",
        //         $serial_number,
        //         $id_dialog,
        //         $type_dialog,
        //         $id_message,
        //         $Id,
        //         $date_send,
        //         $message,
        //         $json_uploaded_files,
        //         $status
        //     );
        //     $wpdb->query($sql_insert_this_message);
        // }

        $sql_insert_this_message = $wpdb->prepare(
            "INSERT INTO `$table_name` 
        (sn, id_dialog, type_dialog, id_message, id_author, date_send, message_body, files, message_status)
        VALUES 
        (%s, %d, %s, %d, %d, %s, %s, %s, %s)",
            $serial_number,
            $id_dialog,
            $type_dialog,
            $id_message,
            $Id,
            $date_send,
            $message,
            $json_uploaded_files,
            $status
        );
        $wpdb->query($sql_insert_this_message);
        //Проставляем предыдущим сообщениям статус отвеченное
        if ($sql_last_id_message && ($id_message > $sql_last_id_message)) {
            if (
                (($user_role === 'dealer' || $user_role === 'distributor') && $user_role_last_message === 'manager')
                || (($user_role_last_message === 'dealer' || $user_role_last_message === 'distributor') && $user_role === 'manager')
            ) {
                $status_before = 'answered';
                $update_status_before_message = "UPDATE `$table_name` SET message_status = '$status_before', date_readed = '$date_send' WHERE id_message <= '$sql_last_id_message' AND id_dialog='$id_dialog' AND sn='$serial_number' AND (message_status = 'unreaded' OR message_status = 'readed')";
                $wpdb->query($update_status_before_message);
            }
        }

        $date_send_time_stamp = convert_timestamp_index($date_send_message);
        $response = [
            'serialNumber' => $serial_number,
            'idDialog' => $id_dialog,
            'typeDialog' => $type_dialog,
            'idMessage' => $id_message,
            'idAuthor' => $Id,
            'firstNameAuthor' => $first_name,
            'lastNameAuthor' => $last_name,
            'companyAuthor' => $company,
            'avatarAuthor' => $avatar,
            'IdUser' => $Id,
            'userRole' => $user_role,
            'dateSend' => $date_send,
            'dateSendTimeStamp' => $date_send_time_stamp,
            'message' => $message,
            'filesJSON' => $json_uploaded_files,
            'status' => $status,
            'table' => $table_name,
            'beforeMessageId' => $sql_last_id_message,
            'statusBeforeMessage' => $status_before,
            'inputFiles' => $send_files,
            'updateContract' => $update_contracts_data,
            'userRoleLastMessage' => $user_role_last_message,
            'idAuthorLastMessage' => $sql_last_id_author,
        ];
    }

    /*Новая система хранения информации о последних сообщениях в контрактах.*/
    //Вытаскиваем последние сообщения в контракте и групируем их по разным темам одинакого типа. По другому не получалось, пожалуйста не материтесь, самому не нравится.
    $check_readed_type_dialog_new = $wpdb->get_results("SELECT t1.* FROM `$table_name` 
        t1 JOIN (
            SELECT id_dialog, MAX(id_message) AS max_id_message
            FROM `$table_name`
            WHERE (type_dialog='$type_dialog' AND sn='$serial_number')
            GROUP BY id_dialog
        ) t2 ON t1.id_dialog = t2.id_dialog AND t1.id_message = t2.max_id_message
        WHERE (type_dialog='$type_dialog' AND sn='$serial_number');
    ");
    header('Content-Type: application/json');
    echo json_encode($response);

    //Дальше всё просто, образовываем массив конвертим в json и запихиваем в таблицу контрактов в зависимости от того какой тип сообщения пришёл от клиента. Если просто прочитано то без одновления даты.
    $update_contracts_data = [];
    $update_contracts_manager = '';
    $update_contracts_other = '';
    foreach ($check_readed_type_dialog_new as $row) {
        $id_dialog_for_contracts = $row->id_dialog;
        $user_data_for_contract = get_userdata($row->id_author);
        $user_role_for_contract = $user_data_for_contract->roles[0];
        if ($user_role_for_contract === 'subscriber') {
            $user_role_for_contract = 'dealer';
        }

        if (
            ($user_role_for_contract === 'dealer' || $user_role_for_contract === 'distributor' || $user_role_for_contract === 'designer_manager')
            && $row->message_status === 'unreaded') {
            $update_contracts_other = $row->message_status;
        }
        if ($user_role_for_contract === 'manager' && $row->message_status === 'unreaded') {
            $update_contracts_manager = $row->message_status;
        }
        // $update_contracts_data[] = [$row->message_status, $row->id_author, $row->date_send];
    }

    $update_contracts_data = json_encode($update_contracts_data);
    if ($status === 'readed') {
        if ($update_contracts_manager === 'unreaded') {
            $wpdb->query("UPDATE gi_contracts SET last_status_manager_$type_for_db = '$update_contracts_manager' WHERE sn = '$serial_number'");
        } 
        if ($update_contracts_other === 'unreaded') {
            $wpdb->query("UPDATE gi_contracts SET last_status_other_$type_for_db = '$update_contracts_other' WHERE sn = '$serial_number'");
        }
        if ($update_contracts_manager === '') {
            $wpdb->query("UPDATE gi_contracts SET last_status_manager_$type_for_db = 'readed' WHERE sn = '$serial_number'");
        } 
        if ($update_contracts_other === '') {
            $wpdb->query("UPDATE gi_contracts SET last_status_other_$type_for_db = 'readed' WHERE sn = '$serial_number'");
        }
    } else {
        if ($update_contracts_manager === 'unreaded') {
            $wpdb->query("UPDATE gi_contracts SET last_status_manager_$type_for_db = '$update_contracts_manager', date_last_activity = '$date_send', date_last_$type_for_db = '$date_send' WHERE sn = '$serial_number'");
        } 
        if ($update_contracts_other === 'unreaded') {
            $wpdb->query("UPDATE gi_contracts SET last_status_other_$type_for_db = '$update_contracts_other', date_last_activity = '$date_send', date_last_$type_for_db = '$date_send' WHERE sn = '$serial_number'");
        }
        if ($update_contracts_manager === '') {
            $wpdb->query("UPDATE gi_contracts SET last_status_manager_$type_for_db = 'readed' WHERE sn = '$serial_number'");
        } 
        if ($update_contracts_other === '') {
            $wpdb->query("UPDATE gi_contracts SET last_status_other_$type_for_db = 'readed' WHERE sn = '$serial_number'");
        }
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не разрешен']);
}
//Намучался с сохранением файлов на кирилице, слишком много времени потратил и решил таким образом.

?>