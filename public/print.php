<?php
//создание списка заказов в xls файле

header('Content-Type: text/html; charset=windows-1251', true);
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');



$user_id = get_current_user_id();
//echo $user_id;
//Подключение к БД
try {
    $host = 'localhost';
    $db   = 'gi.by';
    $user = 'webbaseadm';
    $pass = 'o7Rk*2E"';
    $charset = 'CP1251';
    $dsn = "mysql:host=$host;charset=cp1251;dbname=$db;";
    $db = new PDO($dsn, $user, $pass);	
} 
catch (PDOException $e) {
	echo $e->getMessage();
}

$orders_array = array(); //массив заказов

//получение всех товароы из бд и запонение массива

$stmt = $db->query('SELECT * FROM temporary_'.$user_id.' WHERE id IN ('.$orders_string.')');
$stmt->setFetchMode(PDO::FETCH_ASSOC);
while($row = $stmt->fetch())
{
    $orders_array[] = $row;
}

// var_dump($orders_array);
// die;


require_once 'wp-includes/phpexcel/PHPExcel.php';
$objPHPExcel = new PHPExcel();

$objPHPExcel->setActiveSheetIndex(0);
$active_sheet = $objPHPExcel->getActiveSheet();
$objPHPExcel->createSheet();



//Ориентация страницы и  размер листа
$active_sheet->getPageSetup()
		->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
$active_sheet->getPageSetup()
			->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
//Поля документа		
$active_sheet->getPageMargins()->setTop(1);
$active_sheet->getPageMargins()->setRight(0.75);
$active_sheet->getPageMargins()->setLeft(0.75);
$active_sheet->getPageMargins()->setBottom(1);
//Название листа
$active_sheet->setTitle("Заказы");	
//Шапа и футер 
$active_sheet->getHeaderFooter()->setOddHeader("&Заказы");	
$active_sheet->getHeaderFooter()->setOddFooter('&L&B'.$active_sheet->getTitle().'&RСтраница &P из &N');
//Настройки шрифта
$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
$objPHPExcel->getDefaultStyle()->getFont()->setSize(8);


$active_sheet->getColumnDimension('A')->setWidth(13);
$active_sheet->getColumnDimension('B')->setWidth(13.5);
$active_sheet->getColumnDimension('C')->setWidth(12.5);
$active_sheet->getColumnDimension('D')->setWidth(17.5);
$active_sheet->getColumnDimension('E')->setWidth(11.5);
$active_sheet->getColumnDimension('F')->setWidth(11.5);
$active_sheet->getColumnDimension('G')->setWidth(10.5);
$active_sheet->getColumnDimension('H')->setWidth(41.6);
$active_sheet->getColumnDimension('I')->setWidth(8.54);
$active_sheet->getColumnDimension('J')->setWidth(8.54);
$active_sheet->getColumnDimension('K')->setWidth(8.72);
$active_sheet->getColumnDimension('L')->setWidth(8.72);




$active_sheet->setCellValue('A1','Номер заказа');
$active_sheet->setCellValue('B1','Номер клиента');
$active_sheet->setCellValue('C1','Дата приема');
$active_sheet->setCellValue('D1','Состояние');
$active_sheet->setCellValue('E1','Дата выпуска');
$active_sheet->setCellValue('F1','Дата отгрузки');
$active_sheet->setCellValue('G1','Номер отгрузки');
$active_sheet->setCellValue('H1','Точка');
$active_sheet->setCellValue('I1','Брутто');
$active_sheet->setCellValue('J1','Нетто');
$active_sheet->setCellValue('K1','Объем');
$active_sheet->setCellValue('L1','Пароль');

									

//В цикле проходимся по элементам массива и выводим все в соответствующие ячейки
$row_start = 2;
$i = 0;
foreach($orders_array as $item) {
	$row_next = $row_start + $i;
	
	$order_number = iconv("CP1251", "UTF-8", $item['order_number']);
	$client_numer = iconv("CP1251", "UTF-8", $item['client_numer']);
	$date_receipt = iconv("CP1251", "UTF-8", $item['date_receipt']);
	$status = iconv("CP1251", "UTF-8", $item['status']);
	$release_date = iconv("CP1251", "UTF-8", $item['release_date']);
	$shipping_date = iconv("CP1251", "UTF-8", $item['shipping_date']);
	$shipping_number = iconv("CP1251", "UTF-8", $item['shipping_number']);



	$point = iconv("CP1251", "UTF-8", $item['point']);
	$brutto = iconv("CP1251", "UTF-8", $item['brutto']);
	$netto = iconv("CP1251", "UTF-8", $item['netto']);
	$volume = iconv("CP1251", "UTF-8", $item['volume']);
	$shipment_password = iconv("CP1251", "UTF-8", $item['shipment_password']);

	if($date_receipt != '') $date_receipt = date('d.m.y', $date_receipt);
	if($release_date != '') $release_date = date('d.m.y', $release_date);
	if($shipping_date != '') $shipping_date = date('d.m.y', $shipping_date);

	$active_sheet->setCellValue('A'.$row_next,$order_number);
	$active_sheet->setCellValue('B'.$row_next,$client_numer);
	$active_sheet->setCellValue('C'.$row_next,$date_receipt);
	$active_sheet->setCellValue('D'.$row_next,$status);
	$active_sheet->setCellValue('E'.$row_next,$release_date);
	$active_sheet->setCellValue('F'.$row_next,$shipping_date);
	$active_sheet->setCellValue('G'.$row_next,$shipping_number);
	$active_sheet->setCellValue('H'.$row_next,$point);
	$active_sheet->setCellValue('I'.$row_next,$brutto);
	$active_sheet->setCellValue('J'.$row_next,$netto);
	$active_sheet->setCellValue('K'.$row_next,$volume);
	$active_sheet->setCellValue('L'.$row_next,$shipment_password);
	

	
	$i++;
}




//массив стилей
$style_wrap = array(
	'font'=>array(
		'bold' => true,		'name' => 'Arial',		'size' => 10
	),
	'borders'=>array(
		'outline' => array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN
		),
		'allborders'=>array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN,			'color' => array('rgb'=>'696969')
		),
		'alignment' => array(
			'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER
		),
		'fill' => array(
		'type' => PHPExcel_STYLE_FILL::FILL_SOLID,		'color'=>array('rgb' => 'CFCFCF')
		)
	)
);
$style_left = array(
	'alignment' => array(
		'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT
	),
);
$style_center = array(
	'alignment' => array(
		'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER
	),
);
$style_header = array(
	'font'=>array(
		'name' => 'Arial',		'size' => 10
	),
	'alignment' => array(
		'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT
	),
	'borders'=>array(
		'outline' => array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN
		),
		'allborders'=>array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN,			'color' => array('rgb'=>'696969')
		),
		'alignment' => array(
			'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER
		),
		'fill' => array(
		'type' => PHPExcel_STYLE_FILL::FILL_SOLID,		'color'=>array('rgb' => 'CFCFCF')
		)
	)

);


$active_sheet->getRowDimension(1)->setRowHeight(37);

$active_sheet->getStyle('A2:L'.($row_next))->applyFromArray($style_header);
$active_sheet->getStyle('A2:L'.($row_next))->applyFromArray($style_left);

$active_sheet->getStyle('A1:L1')->applyFromArray($style_wrap);
$active_sheet->getStyle('A1:L1')->applyFromArray($style_center);
$active_sheet->getStyle('A1:L1')->getAlignment()->setWrapText(true);





//remove the last sheet (empty);
$last = $objPHPExcel->getSheetCount();
$objPHPExcel->removeSheetByIndex($last-1);


header("Content-Type:application/vnd.ms-excel");
header("Content-Disposition:attachment;filename=order_list.xls");

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit();
?>
