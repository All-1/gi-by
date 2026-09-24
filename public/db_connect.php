<?php
/*echo "<h4 style='color:red; font-weight: bold'> !!! Проводились технические работы !!! Если у Вас некорректное отображение таблицы, зажмите на клавиатуре ctrl+f5 windows, cmd+f5 macOS. <br> Для пользователей Microsoft Edge ниже:</h4>
    <p>Настройки браузера > Конфиденциальность и службы > В разделе Удалить данные о просмотре веб-страниц нажать \"Выбрать элементы\" > 
Установите флажок параметра \"Кэшированные изображения и файлы\" и \"Файлы cookie и другие данные веб-сайтов, выберите\" \"Удалить сейчас\". > Перезагружаем страницу gi.by </p>
";
*/
//header("Content-Type: text/html; charset=WINDOWS-1251");
/*
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
*/
global $wpdb;
try {
    //$host = '86.57.128.78';
    $host = '192.168.3.12';
    //$host = '192.168.3.13';
    $port = '1433';
    $db   = 'gi02';
    $user = 'gi_Dealer';
    $pass = 'Kuwe4724';


    $dsn = "dblib:host=$host;port=$port;dbname=$db;charset=cp1251";
    //$dsn = "sqlsrv:Server=$host;port=$port;dbname=$db;charset=cp1251";
    $db1 = new PDO($dsn, $user, $pass);	
} 
catch (PDOException $e) {
	echo $e->getMessage();
}

$dbpass = 'o7Rk*2E"';

try {
    $db2 = new PDO('mysql:host=localhost;charset=cp1251;dbname=gi.by', 'webbaseadm', $dbpass);
} 
catch (PDOException $e) {
    echo $e->getMessage();
}

//$Id = 108;
// $stmt1 = $db1->prepare("exec GetOrdersExt @UserId=:Id");
// $stmt1->bindValue(':Id', $Id);
// $stmt1->execute();
// while($row = $stmt1->fetch()) { 
//     echo $row['ReceptionDate'].'<br>';
// }


include "lang-admin.php";

$it_is_time = 1;
if($it_is_time == 1){
    
    $temp_table = "temporary_".$Id;
    try {
        // $db2->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
         $drop_table ="DROP TABLE IF EXISTS `".$temp_table."`;";
         $db2->exec($drop_table);
         $create_table ="CREATE TABLE IF NOT EXISTS `".$temp_table."` (
          `id` int(12) NOT NULL AUTO_INCREMENT,
          `order_number` varchar(255) NOT NULL,
          `client_numer` varchar(255) NOT NULL,
          `date_receipt` varchar(255) NOT NULL,
          `confirm_date` varchar(255) NOT NULL,
          `invoice_date` varchar(255) NOT NULL,
          `payment_date` varchar(255) NOT NULL,
          `status` varchar(255) NOT NULL,
          `required_date` varchar(255) NOT NULL,
          `release_date` varchar(255) NOT NULL,
          `shipping_date` varchar(255) NOT NULL,
          `shipping_number` varchar(255) NOT NULL,
          `point` text NOT NULL,
          `brutto` varchar(255) NOT NULL,
          `netto` varchar(255) NOT NULL,
          `volume` varchar(255) NOT NULL,
          `serial_number` varchar(255) NOT NULL,
          `shipment_password` varchar(3) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=cp1251 AUTO_INCREMENT=28";
         $db2->exec($create_table);
    } 
    catch(PDOException $e) {
        echo $e->getMessage();
    }


    if($stmt1 = $db1->prepare("exec GetOrdersExt2 @UserId=:Id")){
        $stmt1->bindValue(':Id', $Id);
        $stmt1->execute();
//echo $Id;
        while($row = $stmt1->fetch()) { 
//echo "OK";            
            if($row['PKId'] == NULL) $row['PKId'] = '-';
            if($row['OrderName'] == NULL) $row['OrderName'] = '-';
                       
            if($row['ClientName'] == NULL) $row['ClientName'] = '-';
            if($row['ReceptionDate'] == NULL) $row['ReceptionDate'] = '-';
            if($row['ReceptionDate'] == ''){ 
                $ReceptionDate = ''; 
            } 
            else{
                $row['ReceptionDate'] = iconv("UTF-8", "WINDOWS-1251", $row['ReceptionDate']);
                $ReceptionDate = strtotime($row['ReceptionDate']); 
            }

            if($row['ConfirmationDate'] == NULL) $row['ConfirmationDate'] = '-';
            if($row['ConfirmationDate'] == ''){ 
                $ConfirmationDate = ''; 
            } 
            else{
                $row['ConfirmationDate'] = iconv("UTF-8", "WINDOWS-1251", $row['ConfirmationDate']);
                $ConfirmationDate = strtotime($row['ConfirmationDate']); 
            }

            if($row['InvoiceDate'] == NULL) $row['InvoiceDate'] = '-';
            if($row['InvoiceDate'] == ''){ 
                $InvoiceDate = ''; 
            } 
            else{
                $row['InvoiceDate'] = iconv("UTF-8", "WINDOWS-1251", $row['InvoiceDate']);
                $InvoiceDate = strtotime($row['InvoiceDate']); 
            }

            if($row['PaymentDate'] == NULL) $row['PaymentDate'] = '-';
            if($row['PaymentDate'] == ''){ 
                $PaymentDate = ''; 
            } 
            else{
                $row['PaymentDate'] = iconv("UTF-8", "WINDOWS-1251", $row['PaymentDate']);
                $PaymentDate = strtotime($row['PaymentDate']); 
            }
			
			if($row['RequiredDate'] == NULL) $row['RequiredDate'] = '-';
			if($row['RequiredDate'] == ''){ 
                $RequiredDate = ''; 
            } 
            else{
                $row['RequiredDate'] = iconv("UTF-8", "WINDOWS-1251", $row['RequiredDate']);
                $RequiredDate = strtotime($row['RequiredDate']); 
            }
			
			
            if($row['OutputDate'] == ''){ 
                $OutputDate = ''; 
            } 
            else{
                $row['OutputDate'] = iconv("UTF-8", "WINDOWS-1251", $row['OutputDate']);
                $OutputDate = strtotime($row['OutputDate']); 
            }

            if($row['ShipmentDate'] == ''){ 
                $ShipmentDate = ''; 
            } 
            else{
                $row['ShipmentDate'] = iconv("UTF-8", "WINDOWS-1251", $row['ShipmentDate']);
                $ShipmentDate = strtotime($row['ShipmentDate']); 
            }
            if($row['SN'] == NULL) {
                $row['SN'] = '';
            }
            else {
                $serial_number = $row['SN'];
                $order_confirm = 'yes';
                $sql1 = "UPDATE gi_booking SET order_confirm = '$order_confirm' WHERE order_number = '$serial_number'";
                $result1 = $wpdb->query($sql1);
            }

            if($row['OrderStatus'] == NULL) $row['OrderStatus'] = '-';
            if($row['OrderName'] == NULL) $row['OrderName'] = '-';
            if($row['PointName'] == NULL) $row['PointName'] = '-';
            if($row['Brutto'] == NULL) $row['Brutto'] = '-';
            if($row['Netto'] == NULL) $row['Netto'] = '-';
            if($row['Volume'] == NULL) $row['Volume'] = '-';
            if($row['ShipmentPassword'] == NULL) $row['ShipmentPassword'] = '-';
            
            if($row['ShipmentId'] == NULL) $row['ShipmentId'] = ' ';
          
            $x = $row['OrderStatus'];
            switch ($x) {
            case 1:
                $status = mb_convert_encoding($lang_adm->mz_prinyat, "windows-1251"); 
                break;
            case 2:
                $status = mb_convert_encoding($lang_adm->mz_wait, "windows-1251");
                break;
            case 3:
                $status = mb_convert_encoding($lang_adm->mz_confirmed, "windows-1251");
                break;
            case 4:
                $status = mb_convert_encoding($lang_adm->mz_in_production, "windows-1251");
                break;
            case 5:
                $status = mb_convert_encoding($lang_adm->mz_packed, "windows-1251");
                break;
            case 6:
                $status = mb_convert_encoding($lang_adm->mz_part_shipped, "windows-1251");
                break;
            case 7:
                $status = mb_convert_encoding($lang_adm->mz_shipped, "windows-1251");
                break;
            }

            $stmt = $db2->prepare("INSERT INTO ".$temp_table." (
                id, order_number, client_numer, date_receipt, confirm_date, invoice_date, payment_date, status, required_date, release_date, shipping_date, shipping_number, point, brutto, netto, volume, serial_number, shipment_password
                ) VALUES (
                :id, :order_number, :client_numer, :date_receipt, :confirm_date, :invoice_date, :payment_date, :status, :required_date, :release_date, :shipping_date, :shipping_number, :point, :brutto, :netto, :volume, :serial_number, :shipment_password
                )");
            $stmt -> execute(array(
                'id'=>$row['PKId'], 
                'order_number'=>$row['OrderName'], 
                'client_numer'=>$row['ClientName'], 
                'date_receipt'=>$ReceptionDate, 
                'confirm_date'=>$ConfirmationDate,
                'invoice_date'=>$InvoiceDate,
                'payment_date'=>$PaymentDate,
                'status'=>$status, 
                'required_date'=>$RequiredDate, 
                'release_date'=>$OutputDate, 
                'shipping_date'=>$ShipmentDate, 
                'shipping_number'=>$row['ShipmentId'], 
                'point'=>$row['PointName'], 
                'brutto'=>$row['Brutto'], 
                'netto'=>$row['Netto'], 
                'volume'=>$row['Volume'],
                'serial_number'=> $row['SN'],
                'shipment_password'=>$row['ShipmentPassword']
            ));

        }//while

    }//if



}//if it_is_time == 1




?>
