<?php
require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
global $wpdb;
//$sql = $_POST['sql'];

$cost = $_POST['cost'] ?? '';
$type = $_POST['type'] ?? '';
$materials = $_POST['materials'] ?? '';
$colors = $_POST['colors'] ?? '';
$styles = $_POST['styles'] ?? '';
$models = $_POST['models'] ?? '';
$configs = $_POST['configs'] ?? '';
$url = $_POST['filterurl'] ?? '';
$page = $_POST['page'] ?? 1;
$perpage = 42;
error_log(print_r($styles, true));

echo $url;
//ТАКИЕ ФИЛЬТРЫ ПО МАТЕРИАЛАМ
if (!empty($materials)) {
	$countmat = count($materials);
} else
	$countmat = 0;

$wheremat = "";
if ($countmat >= 1) {
	$wheremat .= "AND (";
	for ($i = 0; $i < $countmat; $i++) {
		$wheremat_or = $i >= 1 && $i < $countmat ? "OR" : "";
		$wheremat .= " $wheremat_or `material` LIKE '%$materials[$i]%'";
	}
	$wheremat .= ")";
}

$wherestyles = "";
//ТАКИЕ ФИЛЬТРЫ ПО СТИЛЮ
$countstyles = !empty($styles) ? count($styles) : 0;
if (!empty($countstyles) && $countstyles >= 1) {
	$wherestyles .= "AND (";
	for ($i = 0; $i < $countstyles; $i++) {
		//echo $materials[$i];
		$wherestyles_or = "";
		if ($i >= 1 and $i < $countstyles) {
			$wherestyles_or = "OR";
		}
		$wherestyles .= " $wherestyles_or `style` LIKE '%$styles[$i]%'";
	}
	$wherestyles .= ")";
}

//страница
if (!empty($_POST['page'])) {
	$from = ($page - 1) * $perpage;
} else {
	$from = 0;
}

$sql = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wheremat $wherestyles AND public != 'no' ORDER BY name ASC LIMIT $from, 42";

$sql = str_replace("\'", "'", $sql);
//echo $sql;

//УБИРАЕМ ПАГИНАЦИЮ
$sqlpagination = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wheremat $wherestyles ";
$resultpagination = $wpdb->get_results($sqlpagination);
$kitchencount = count($resultpagination);
if ($kitchencount <= $perpage) {
	echo "<style>.pagination_div{display:none !important;}</style>";
} else {
	echo "<style>.pagination_div{display:block !important;}</style>";
}

$result = $wpdb->get_results($sql);
//Смотрим, сколько выдает значений после примененных фильтров. Если 0 - выводим текст типа "не найдено".
$count = count($result); //Плюсуем, т.к. count может быть в модерне 1+, а в классике 0 (в случае фильтра пластика, напр.), но прога считает последнее значение (0)
if ($count == 0) {
	$a = "
	<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>
	<div class='noResultCatalog'>Сожалеем, но по Вашему запросу ничего не найдено. Попробуйте применить другие фильтры.
		<br><br>
		<a href='/kuhni/' style='font-size:16px;'>
			<img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Обнулить фильтры 
		</a>
	</div>
	";
}

$a = "";
foreach ($result as $row) {
	$name = $row->name;
	$name_eng = $row->name_eng;
	// $kitchen_header = $row->kitchen_header;
	// $alias = $row->alias;
	$description = $row->description;
	$avatar = $row->avatar;
	$avatar_massiv = $row->avatar_massiv;
	$avatar_shpon = $row->avatar_shpon;
	$avatar_plastik = $row->avatar_plastik;
	$avatar_akril = $row->avatar_akril;
	$avatar_steklo = $row->avatar_steklo;
	$avatar_dsp = $row->avatar_dsp;
	$avatar = $row->avatar;
	$directory = $row->folder;
	$type = $row->type;
	$cost = $row->basic_cost;
	$cost = round($cost / 2.3);
	$sales = $row->sales;
	// $sales_cost = round($sales_cost);
	if ($name == "Астра" or $name == "Дамиана" or $name == "Миссури" or $name == "Алегри" or $name == "Джаспер") {
		$nowsaleses = "<span style='font-size:12px;'>до </span>-37%";
	} else {
		$nowsaleses = "- " . $sales . "%";
	}
	$permalink = "/kuhni/";
	$style = $row->style;
	$color = $row->color;
	$material = $row->material;
	$wood = $row->wood;
	$plastic = $row->plastic;
	$furnitura = $row->furnitura;
	$nonbacterial = stripos($material, 'МДФ') !== false ? "
			<a href='/antibacterial-mdf/'>
				<div class='catalog_antibacterial_stiker'></div>
			</a>
			" : "";

	$find1 = "Массив";
	$find2 = "Шпон";
	$find3 = "Акрил";
	$find4 = "Пластик";
	$find5 = "ЛДСП";

	if (stripos($material, $find1) !== false or stripos($material, $find2) !== false) {
		$wood_string = "Древесина: $wood <br>";
	} else
		$wood_string = "";
	if (stripos($material, $find3) !== false or stripos($material, $find4) !== false or stripos($material, $find5) !== false) {
		$plastic_string = "Покрытие: $plastic <br>";
	} else
		$plastic_string = "";
	//Подключаем аватар по типу материала
	$echoavatar = $avatar;
	$a .= "
			<div class='kitchen_point' style=''>
				$nonbacterial
				<a href='$permalink$name_eng/' target='_blank'>
					<div class='kitchen_avatar' style='background:url($echoavatar) no-repeat; background-size:cover; background-position:center;'></div>
				
					<div class='kitchen_cat_content'>
						<span class='kitchen_name'>$name</span><br>
						<span><span style='font-weight:400;'><b>Материал:</b></span> $material</span><br>
						<div class='catalogue_cost' style='width:100%; text-align:right; font-size:16px; color:darkred; position:relative;'>
					</div>
					</div>
				</a>
			</div>
	";
}
echo $a;
?>