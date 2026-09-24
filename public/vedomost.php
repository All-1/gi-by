<?php

//input test order list
//$orders_string = '89435,89389,89377,893805';
//$orders_number_string = 'БМ177д3, БМ483д1, УК388, БМ471';

$order_ids_array = explode(',', $orders_string);
$orders_number_array = explode(',', $orders_number_string);

$orders_array = array();


//DB connect
try {
    $host = '192.168.3.12';
	$port = '1433';
    $db   = 'gi02';
    $user = 'gi_Dealer';
    $pass = 'Kuwe4724';

    $dsn = "dblib:host=$host;charset=cp1251;port=$port;dbname=$db";
    $db = new PDO($dsn, $user, $pass);	
} 
catch (PDOException $e) {
	echo $e->getMessage();
}

//get order list
if($stmt = $db->prepare("exec GetProductsExt @OrderIds=:orders_string")){
    $stmt->bindValue(':orders_string', $orders_string);
    $stmt->execute();
    while($row = $stmt->fetch()) { 
        $orders_array[] = $row;
    }
}


require_once 'wp-includes/phpexcel/PHPExcel.php';

$objPHPExcel = new PHPExcel();


//styles
$style_header_first_row = array(			
	'font'=>array(
		'bold' => true,	'name' => 'Arial',	'size' => 10
	),				
	'borders'=>array(		
		'outline' => array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN
		),
		'allborders'=>array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb'=>'696969')
		),
		'alignment' => array(
			'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER, 'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
		),
		'fill' => array(
			'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
			'color'=>array(	'rgb' => 'CFCFCF' )
		)
	)
);
$border =  array(
	'borders'=>array(
		'allborders'=>array(
			'style'=>PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb'=>'696969')
		)
	)
);
$style_number_place = array(
	'font'=>array(
		'name' => 'Arial', 'size' => 14, 'bold' => true,
	),
);
$style_center = array(
	'alignment' => array(
		'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_CENTER, 'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
	),
);
$style_bold = array(
	'font'=>array(
		'bold' => true
	),	
);
$style_header = array(
	'font'=>array(
		'name' => 'Arial', 'size' => 10
	),
	'alignment' => array(
		'horizontal' => PHPExcel_STYLE_ALIGNMENT::HORIZONTAL_LEFT, 'vertical' => PHPExcel_STYLE_ALIGNMENT::VERTICAL_CENTER,
	),
);






$c = 0;

foreach ($order_ids_array as $order_id) {
	
	//createSheet
	$objPHPExcel->createSheet();
	$objPHPExcel->setActiveSheetIndex($c);
	$active_sheet = $objPHPExcel->getActiveSheet();
	$active_sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_PORTRAIT);
	$active_sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
	$active_sheet->getPageMargins()->setTop(0.59);
	$active_sheet->getPageMargins()->setRight(0.39);
	$active_sheet->getPageMargins()->setLeft(0.39);
	$active_sheet->getPageMargins()->setBottom(0.39);

	$active_sheet->setTitle($orders_number_array[$c]);	
	//$active_sheet->getHeaderFooter()->setOddHeader('&C'.$orders_number_array[$c]);	

	$active_sheet->getHeaderFooter()->setOddHeader('&C&B'.$active_sheet->getTitle().'&RСтраница &P из &N');
	$objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(8);
	$active_sheet->getColumnDimension('A')->setWidth(5.2);
	$active_sheet->getColumnDimension('B')->setWidth(7.3);
	$active_sheet->getColumnDimension('C')->setWidth(86);
	$active_sheet->getColumnDimension('D')->setWidth(5.57);
	$active_sheet->getColumnDimension('E')->setWidth(8);
	$active_sheet->getColumnDimension('F')->setWidth(8);

	$active_sheet->setCellValue('A1','№ п/п');
	$active_sheet->setCellValue('B1','№ места');
	$active_sheet->setCellValue('C1','Изделие');
	$active_sheet->setCellValue('D1','Кол-во');
	$active_sheet->setCellValue('E1','Склад 1');
	$active_sheet->setCellValue('F1','Склад 2');
	
	foreach ($orders_array as $order) {

			$row_start = 2;
			$row_next = '';
			$i = 0;
			$last_cell = '';
			$packed_count = 0;
			$last_OrderId = 0;

			$active_sheet->getStyle('A'.$row_start.':F'.$row_start)->applyFromArray($border);

			foreach($orders_array as $item) {

				if($order_id == $item['OrderId']){
					
					//if the order is packed and has SeatNumber
					if($item["SeatNumber"] !== null){

						$row_next = $row_start + $i;
						
						$OrderId = $i+1;
						if($item["SeatNumber"] === null){
							$item["SeatNumber"] = 'изделие не упаковано';
						}
						$SeatNumber = $item['SeatNumber'];
						$ProductName = iconv("CP1251", "UTF-8", $item['ProductName']);
						$ProductQuantity = iconv("CP1251", "UTF-8", $item['ProductQuantity']);	

						$active_sheet->setCellValue('A'.$row_next,$OrderId);
						$active_sheet->setCellValue('B'.$row_next,$SeatNumber);
						$active_sheet->setCellValue('C'.$row_next,$ProductName);
						$active_sheet->setCellValue('D'.$row_next,$ProductQuantity);

						$active_sheet->getStyle('A'.$row_next.':F'.$row_next)->applyFromArray($border);
					
						$i++;
						$last_row = $row_next - 1;
						if($SeatNumber == $last_cell){
							$objPHPExcel->getActiveSheet()->mergeCells('B'.$last_row.':B'.$row_next);
						}
						$last_cell = $SeatNumber;
						$row_next++;
						$packed_count++;
						$last_OrderId = $OrderId;

						
					}
					
				}
				
			}//foreach($orders_array as $item)

			

			//if there are unpacked orders - do empty area
			if($packed_count == 0) {
				$row_next += 3;
			}
			$flag = 0;
			foreach($orders_array as $item) {
				if($order_id == $item['OrderId']){
					if($item["SeatNumber"] === null){
						$flag = 1;
					}
				}
			}

			if(1 == $flag){

				$row_next++;
				$row_unpack_string = $row_next;
				$unpack = 'Неупакованные изделия';
				$active_sheet->setCellValue('C'.$row_next, $unpack);

				$row_next++;
				$row_start = 2;
				$i = $last_OrderId;
				$j = 0;
				$row_start = $row_next; 

				foreach($orders_array as $item) {

					if($order_id == $item['OrderId']){
						
						if($item["SeatNumber"] === null){

							$row_next = $row_start + $j;
							
							$OrderId = $i+1;
							if($item["SeatNumber"] === null){
								$item["SeatNumber"] = 'изделие не упаковано';
							}
							$SeatNumber = $item['SeatNumber'];
							$ProductName = iconv("CP1251", "UTF-8", $item['ProductName']);
							$ProductQuantity = iconv("CP1251", "UTF-8", $item['ProductQuantity']);	

							$active_sheet->setCellValue('A'.$row_next,$OrderId);						
							$active_sheet->setCellValue('C'.$row_next,$ProductName);
							$active_sheet->setCellValue('D'.$row_next,$ProductQuantity);

							$active_sheet->getStyle('A'.$row_next.':D'.$row_next)->applyFromArray($border);

							$j++;
							$i++;
							$last_row = $row_next - 1;
							if($SeatNumber == $last_cell){
								$objPHPExcel->getActiveSheet()->mergeCells('B'.$last_row.':B'.$row_next);
							}
							$last_cell = $SeatNumber;
						}

					}

				}//foreach($orders_array as $item)

			}
			

			
			//setting style kit for specific elements of sheet
			$active_sheet->getStyle('A1:F1')->applyFromArray($style_header_first_row);

			$active_sheet->getStyle('A1:F1')->getAlignment()->setWrapText(true);

			$active_sheet->getStyle('A1:F1')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));

			$active_sheet->getStyle('A2:F'.($row_next))->applyFromArray($style_header);

			$active_sheet->getStyle('B2:B'.($row_next))->applyFromArray($style_number_place);

			$active_sheet->getStyle('A2:B'.($row_next))->applyFromArray($style_center);

			$active_sheet->getStyle('D2:D'.($row_next))->applyFromArray($style_center);

			if(1 == $flag){	
				$active_sheet->getStyle('C'.$row_unpack_string)->applyFromArray($style_center); 
				$active_sheet->getStyle('C'.$row_unpack_string)->applyFromArray($style_bold); 
			} 
			
		 
		
		
	}
	
	$c++;
	
}

//remove the last sheet (empty);
$last = $objPHPExcel->getSheetCount();
$objPHPExcel->removeSheetByIndex($last-1);

header("Content-Type:application/vnd.ms-excel");
header("Content-Disposition:attachment;filename=vedomost.xls");

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');

exit();

?>
