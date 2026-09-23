<?php
/*Plugin Name: bp_kitchen_print
Description: Работа с кухнями. Вывод информации на страницу.
Version: 1.0
Author: Business Park*/
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;

add_shortcode('catalogue_print', 'print_catalogue');
add_shortcode('catalogue_print2', 'print_catalogue2');
add_shortcode('catalogue_print3', 'print_catalogue3');
add_shortcode('kitchen_print', 'print_kitchen');
add_action('wp_enqueue_scripts', 'print_kitchens_scripts');

function print_kitchens_scripts()
{
	//wp_enqueue_script('jquery');
}

function print_catalogue($atts)
{

	$shortArray = shortcode_atts(
		array( 		//Добавляет ID в шорткод, со стартовым значением ALL
			'color' => "",
			'config' => "",
			'style' => "",
			'material' => "",
		),
		$atts
	);
	$shortColor = $shortArray['color'];
	$shortConfig = $shortArray['config'];
	$shortStyle = $shortArray['style'];
	$shortMaterial = $shortArray['material'];

	//ПОД ФИЛЬТРЫ ПРОПИСЫВАЕМ MySQL
	$wherecolor = (!empty($shortColor)) ? "AND color LIKE '%$shortColor%'" : "";
	$whereconfig = (!empty($shortConfig)) ? "AND config LIKE '%$shortConfig%'" : "";
	$wherestyle = (!empty($shortStyle)) ? "AND style LIKE '%$shortStyle%'" : "";
	$wherematerial = (!empty($shortMaterial)) ? "AND material LIKE '%$shortMaterial%'" : "";

	$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');

	$uri = $_SERVER['REQUEST_URI'];
	$qPos = strpos($uri, '?');

	global $wpdb;

	//ФИЛЬТРЫ ПО МАТЕРИАЛАМ
	$x = "<div class='filterHeader'>Материалы:</div>";
	$sqlmater = "SELECT * FROM gi_kitchen_material ORDER BY `rank`";
	$resultmater = $wpdb->get_results($sqlmater);
	foreach ($resultmater as $rowmater) {
		$material = $rowmater->material;
		$material_urls = $rowmater->alias;
		$matereng = $rowmater->material_eng;
		$x .= "<label for='$matereng' class='containerCheckbox'><input class='filter_form' id='$matereng' type='checkbox' name='material' data-url='$material_urls' data-value='$material' value='$material'/> $material <span class='checkmarkCheckbox'></span></label>";
	}

	//ФИЛЬТРЫ ПО СТИЛЮ
	$x .= "<div class='filterHeader'>Стиль:</div>";
	$sqlstyles = "SELECT * FROM gi_kitchen_filtres WHERE category = 'style'";
	$resultstyles = $wpdb->get_results($sqlstyles);
	foreach ($resultstyles as $rowstyles) {
		$styles = $rowstyles->filtervalue;
		$style_urls = $rowstyles->alias;
		$styleseng = str_replace($rus, $lat, $styles);
		$x .= "<label for='$styleseng' class='containerCheckbox'><input class='filter_form' id='$styleseng' type='checkbox' name='styles' data-url='$style_urls' data-value='$styles' value='$styles'/> $styles <span class='checkmarkCheckbox'></span></label>";
	}

	$x .= "<div style='clear:both;'></div>";

	$b = "<div id='filtres' class='filtres'>
			<img src='/wp-content/themes/wp-diary/images/close-black.png' class='closeFiltres' id='closeFiltres' onclick='closeFiltres();'/>
			<form method='GET' class='formFiltres'>
				$x
				<br>
				<a href='/kuhni/'><img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Сбросить фильтры</a>
			</form>
	</div>";
	$a = "<div id='mycatalog'>";


	$a .= "<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>";

	//$sql = "SELECT * FROM `gi_kitchen` WHERE `type` = '$typeKitchen' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
	$sql = "SELECT * FROM `gi_kitchen` WHERE `type` !='' ORDER BY name";
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
	/*
						 //СМОТРИМ СКОЛЬКО ВСЕГО МОДЕЛЕЙ НА ЭТОЙ СТРАНИЦЕ 
						 $sqlpagination = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wherecolor $whereconfig $wherestyle $wherematerial";
						 $resultpagination = $wpdb->get_results($sqlpagination);
							 if(count($resultpagination)<=42){

						 }
						 */
	$display_pagination = "display:none;";
	//ВЫГРУЖАЕМ БАЗОВЫЙ КАТАЛОГ, БЕЗ АЯКСА
	$sql1 = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wherecolor $whereconfig $wherestyle $wherematerial ORDER BY name ASC";
	$results = $wpdb->get_results($sql1);

	$kitchens = '';
	foreach ($results as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
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
		$public = $row->public;
		if (!$public) {
			continue;
		}
		$cost = round($cost / 2.3);
		$time = time();
		$sales = $row->sales;
		if ($name == "Астра" or $name == "Дамиана" or $name == "Миссури" or $name == "Алегри" or $name == "Джаспер") {
			$nowsaleses = "<span style='font-size:12px;'>до </span>-37%";
		} else {
			$nowsaleses = "- " . $sales . "%";
		}
		if ($sales !== 'no') {
			$sales_cost = $cost - ($cost / 100 * $sales);
			$sales_cost = round($sales_cost);
			$cost = "<span style='text-decoration:line-through; font-size:12px; color:red;'>$cost</span> $sales_cost";
			$salesDiv = "<div class='salesDiv'> $nowsaleses </div>";
		} else
			$salesDiv = "";

		$style = $row->style;
		$color = $row->color;
		$material = $row->material;
		$wood = $row->wood;
		$plastic = $row->plastic;
		$furnitura = $row->furnitura;

		if (stripos($material, 'МДФ') !== false) {
			$nonbacterial = "
			<a class='nonbacterialEmblem' href='/antibacterial-mdf/'>
				<div class='catalog_antibacterial_stiker'></div>
			</a>
			";
		} else
			$nonbacterial = "";


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

		$echoavatar = $avatar;

		//var_dump($parent_ids);
		//$permalink = get_permalink();
		$permalink = "/kuhni/";


		$emblems = "
		<div class='kitchenEmblems'>
			$nonbacterial
		</div>
		";

		$tabletop_timestamp = $row->timestamp;
		if ($time - $tabletop_timestamp <= 7776000 * 2) {
			$newtabletop = " <span class='new-span'>NEW</span> ";
			$datanew = "data-new='yes'";
		} else {
			$newtabletop = "";
			$datanew = "data-new='no'";
		}

		//Вывод каталога 
		$kitchen = "
			<div class='kitchen_point' style=''>
				$emblems
				<a href='$permalink$name_eng/' target='_blank'>
					<div class='new-label'>$newtabletop</div>
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
		$kitchens .= $kitchen;
	}

	$a .= "<div id='ajaxcatalogue'>";
	//СЮДА ЛЕТИТ АЯКС
	$a .= "$kitchens";
	$a .= "</div>";

	//ПАГИНАЦИЯ
	$a .= "<div class='pagination_div' style='$display_pagination'>";
	$pages = ceil($count / 42);
	for ($i = 1; $i <= $pages; $i++) {
		$a .= "<button id='pagebtn_$i' value='$i' class='pagination' onclick='get_page(this);' style='padding:0; border:none; width:40px; height:40px; line-height:40px; text-align:center; background:#114977; color:white; margin:3px; border-radius:50%; cursor:pointer;'>$i</button>";
	}
	$a .= "</div>";

	$c = "<div>";
	//ПЕРЕЛИНКОВКА

	$sqlmateriallinks = "SELECT material, alias FROM gi_kitchen_material";
	$resultmateriallinks = $wpdb->get_results($sqlmateriallinks);
	$c .= "По материалу: ";
	foreach ($resultmateriallinks as $rowmaterialslinks) {
		$materialname = $rowmaterialslinks->material;
		$materiallink = $rowmaterialslinks->alias;
		$c .= "<a href='/kuhni/$materiallink/'> <button class='myBtn' style='padding:2px 4px; font-size:10px; border-color:#dcdcdc;'>$materialname </button> </a>";
	}


	$sqlstylelinks = "SELECT filtervalue, alias FROM gi_kitchen_filtres WHERE category='style'";
	$resultstylelinks = $wpdb->get_results($sqlstylelinks);
	$c .= "<br>По стилю: ";
	foreach ($resultstylelinks as $rowstylelinks) {
		$stylename = $rowstylelinks->filtervalue;
		$stylelink = $rowstylelinks->alias;
		$c .= "<a href='/kuhni/$stylelink/'> <button class='myBtn' style='padding:2px 4px; font-size:10px; border-color:#dcdcdc;'>$stylename </button> </a>";
	}

	$c .= "</div>";

	$header = "<div class='header-catalog'>
					<div class='download-button'>
						<img src='/wp-content/plugins/bp_dealer_files/images/load.png'>
							<a href='https://gi.by/wp-content/uploads/2022/11/MODERN-CATALOGUE.pdf' target='_blank' rel='noopener'>Каталог Модерн</a> 
							<br>
							<b>PDF</b>(19 Mb)
					</div>
					<div class='download-button-classic'>
						<img src='/wp-content/plugins/bp_dealer_files/images/load.png'> 
							<a href='https://gi.by/wp-content/uploads/2022/11/CLASSIC-CATALOGUE.pdf' target='_blank' rel='noopener'>Каталог Классика</a>
							<br>
							<b>PDF</b> (8 Mb)
					</div>
				</div>
			<p></p>
		";
	//$a.="$header";	
	$a .= "$c";
	$a .= "</div>";
	$a .= "<div style='clear:both;'></div>";


	$myjs = "
	<style>
	.selected {background:#c6c6c6 !important;}
	</style>
		<script>
		//Устанавливаем чекед на фильтры, примененные шорткодом
		jQuery('[data-value=\"$shortStyle\"]').prop('checked', true);
		jQuery('[data-value=\"$shortMaterial\"]').prop('checked', true);
		
		
		
		
		/*каталог надо выгружать не аяксом, а для сео....		
		//АЯКС КАТАЛОГ
		function show(){  
			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {page:1},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});
		} 
		jQuery(document).ready(function(){ 
			show();  
		});*/
		
		
		jQuery('#pagebtn_1').addClass('selected');
		function get_page(obj){
			var pagenumber = obj.value;
			var pageid = document.getElementById(obj.id);
			jQuery('.pagination').removeClass('selected');
			jQuery(obj).addClass('selected');
			var myscrollto = document.getElementById('breadcrumb');
			//получаем чекбоксы
			var cost = jQuery('input[name=cost]:checked').val();
			var materials_arr = new Array();
			var colors_arr = new Array();
			var configs_arr = new Array();
			var styles_arr = new Array();
			var models_arr = new Array();

			jQuery('input:checkbox[name=material]:checked').each(function(){
				materials_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=color]:checked').each(function(){
				colors_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=config]:checked').each(function(){
				configs_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=styles]:checked').each(function(){
				styles_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=models]:checked').each(function(){
				models_arr.push(jQuery(this).val());
			});
			jQuery('html, body').animate({scrollTop: jQuery(myscrollto).offset().top}, 8000);
			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {page:pagenumber, cost:cost, materials: materials_arr, styles: styles_arr, configs: configs_arr, colors:colors_arr, models:models_arr},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});				
		}
		
		//При применении фильтров
		jQuery( '.filter_form' ).change(function() {
			var filterurl = jQuery(this).attr('data-url');
			var countstyles = jQuery('input:checkbox[name=styles]:checked').length;
			var countcolors = jQuery('input:checkbox[name=color]:checked').length;
			var countconfigs = jQuery('input:checkbox[name=config]:checked').length;
			var countmaterials = jQuery('input:checkbox[name=material]:checked').length;
			var allcount = countstyles + countcolors + countconfigs + countmaterials;
			if(filterurl && allcount == 1){
				var urlval = jQuery('input:checkbox[type=checkbox]:checked').attr('data-url');
				var baseUrl = 'https://geosideal.ru/kuhni/';
				var newUrl = baseUrl + urlval + '/';
				history.pushState(null, null, newUrl);
			}
			if(allcount == 0 | allcount > 1){
				//var baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
				var newUrl = 'https://geosideal.ru/kuhni/';
				history.pushState(null, null, newUrl);
			}
			var cost = jQuery('input[name=cost]:checked').val();
			var materials_arr = new Array();
			var colors_arr = new Array();
			var configs_arr = new Array();
			var styles_arr = new Array();
			var models_arr = new Array();
			
			jQuery('input:checkbox[name=material]:checked').each(function(){
				materials_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=color]:checked').each(function(){
				colors_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=config]:checked').each(function(){
				configs_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=styles]:checked').each(function(){
				styles_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=models]:checked').each(function(){
				models_arr.push(jQuery(this).val());
			});
			
			//Переключение на 1 страницу
			jQuery('.pagination').removeClass('selected');
			jQuery('#pagebtn_1').addClass('selected');
			//Листаем вверх
			var myscrollto = document.getElementById('breadcrumb');
			jQuery('html, body').animate({scrollTop: jQuery(myscrollto).offset().top}, 1000);

			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {cost:cost, materials: materials_arr, styles: styles_arr, configs: configs_arr, colors:colors_arr, models:models_arr, url:filterurl},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});
			
		});
		</script>
	";

	return $b . $a . $myjs;
}

function print_catalogue2()
{
	$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');

	$uri = $_SERVER['REQUEST_URI'];
	$qPos = strpos($uri, '?');

	global $wpdb;

	//Сохраним заполненность форм пока что таким вот образом...и, наверное, навсегда....:D
	if (isset($_GET['cost']) and $_GET['cost'] == 'cheap')
		$cheapchecked = "checked";
	else
		$cheapchecked = "";
	if (isset($_GET['cost']) and $_GET['cost'] == 'expensive')
		$expensivechecked = "checked";
	else
		$expensivechecked = "";
	if ($_GET['massiv'] == "yes")
		$massivchecked = "checked";
	else
		$massivchecked = "";
	if ($_GET['shpon'] == "yes")
		$shponchecked = "checked";
	else
		$shponchecked = "";
	if ($_GET['plastic'] == "yes")
		$plasticchecked = "checked";
	else
		$plasticchecked = "";
	if ($_GET['akril'] == "yes")
		$akrilchecked = "checked";
	else
		$akrilchecked = "";
	if ($_GET['dsp'] == "yes")
		$dspchecked = "checked";
	else
		$dspchecked = "";
	if ($_GET['steklo'] == "yes")
		$steklochecked = "checked";
	else
		$steklochecked = "";
	if (isset($_GET['type']) and $_GET['type'] == 'classic')
		$classicchecked = "checked";
	else
		$eclassicchecked = "";
	if (isset($_GET['type']) and $_GET['type'] == 'modern')
		$modernchecked = "checked";
	else
		$modernchecked = "";

	$b = "<div id='filtres' class='filtres'>
			<img src='/wp-content/themes/wp-diary/images/close-black.png' class='closeFiltres' id='closeFiltres' onclick='closeFiltres();'/>
			<form method='GET' class='formFiltres'>
				<div class='filterHeader'>Сортировать по цене:</div>
				<label for='cheap' class='containerInput'><input id='cheap' type='radio' name='cost' value='cheap' $cheapchecked/> Сначала дешевые<span class='checkmark'></span></label>
				<label for='expensive' class='containerInput'> <input id='expensive' type='radio' name='cost' value='expensive' $expensivechecked/> Сначала дорогие <span class='checkmark'></span></label>
				
				<div class='filterHeader'>Материал:</div>
				<label for='massiv' class='containerCheckbox'><input id='massiv' type='checkbox' name='massiv' value='yes' $massivchecked/>  Массив дерева <span class='checkmarkCheckbox'></span></label>
				<label for='shpon' class='containerCheckbox'><input id='shpon' type='checkbox' name='shpon' value='yes' $shponchecked/> Шпон <span class='checkmarkCheckbox'></span></label>
				<label for='plastic' class='containerCheckbox'><input id='plastic' type='checkbox' name='plastic' value='yes' $plasticchecked/>  Пластик <span class='checkmarkCheckbox'></span></label>
				<label for='akril' class='containerCheckbox'><input id='akril' type='checkbox' name='akril' value='yes' $akrilchecked/>  Акрил <span class='checkmarkCheckbox'></span></label>
				<!------<label for='dsp' class='containerCheckbox'><input id='dsp' type='checkbox' name='dsp' value='yes' $dspchecked/>  ЛДСП <span class='checkmarkCheckbox'></span></label>----->
				<!------<label for='steklo' class='containerCheckbox'><input id='steklo' type='checkbox' name='steklo' value='yes' $steklochecked/>  Стекло <span class='checkmarkCheckbox'></span></label>----->
			
				<div class='filterHeader'>Стиль:</div>
				<label for='classic' class='containerInput'><input id='classic' type='radio' name='type' value='classic' $classicchecked/>  Классическая <span class='checkmark'></span></label>
				<label for='modern' class='containerInput'><input id='modern' type='radio' name='type' value='modern' $modernchecked/>  Современная <span class='checkmark'></span></label>

				<br>
				<input type='submit' value='Применить фильтры' class='myBtn'/><br><br>
				<a href='/kuhni/'><img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Сбросить фильтры</a>
			</form>
	</div>";
	$a = "<div id='mycatalog'>";

	//ФИЛЬТР ПО ЦЕНЕ
	if (!empty($_GET['cost'])) {
		if ($_GET['cost'] == 'cheap') {
			$orderby = "basic_cost ASC";
		} else {
			$orderby = "basic_cost DESC";
		}
	} else
		$orderby = "name";
	//ФИЛЬТР ПО ТИПУ (КЛАССИКА И МОДЕРН)
	if (!empty($_GET['type'])) {
		if ($_GET['type'] == 'classic') {
			$wheretype = "WHERE type = 'Классика'";
		} elseif ($_GET['type'] == 'modern') {
			$wheretype = "WHERE type = 'Модерн'";
		}
	} else {
		$wheretype = "";
	}
	//ФИЛЬТР ПО МАТЕРИАЛАМ
	$and = "AND (";
	$or = "OR";
	if ($_GET['massiv'] == 'yes') {
		$wheremassiv = "$and material LIKE '%ассив%'";
		$and = "OR";
	} else {
		$wheremassiv = "";
	}
	if ($_GET['shpon'] == 'yes') {
		$whereshpon = "$and material LIKE '%пон%'";
		$and = "OR";
	} else {
		$whereshpon = "";
	}
	if ($_GET['plastic'] == 'yes') {
		$whereplastic = "$and material LIKE '%ластик%'";
		$and = "OR";
	} else {
		$whereplastic = "";
	}
	if ($_GET['akril'] == 'yes') {
		$whereakril = "$and material LIKE '%крил%'";
		$and = "OR";
	} else {
		$whereakril = "";
	}
	if ($_GET['dsp'] == 'yes') {
		$wheredsp = "$and material LIKE '%ДСП%'";
		$and = "OR";
	} else {
		$wheredsp = "";
	}
	if ($_GET['steklo'] == 'yes') {
		$wheresteklo = "$and material LIKE '%текло%'";
		$and = "OR";
	} else {
		$wheresteklo = "";
	}
	//Чтобы применить несколько условий, надр занести все OR в скобки. Открывается в переменной $and. 
	//Если хотя бы 1 фильтр задан - вносим в переменную $skobka закрывающую скобку. Если нет, то пофиг, т.к. переменная $and и не используется. Хитро?
	if (isset($_GET['massiv']) or isset($_GET['shpon']) or isset($_GET['plastic']) or isset($_GET['akril']) or isset($_GET['dsp'])) {
		$skobka = ")";
	} else
		$skobka = "";


	$a .= "<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>";
	$sqlType = "SELECT DISTINCT type FROM gi_kitchen $wheretype ORDER BY type DESC";
	$resultType = $wpdb->get_results($sqlType);



	//$sql = "SELECT * FROM `gi_kitchen` WHERE `type` = '$typeKitchen' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
	$sql = "SELECT * FROM `gi_kitchen` WHERE `type` != '' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
	$result = $wpdb->get_results($sql);
	//Смотрим, сколько выдает значений после примененных фильтров. Если 0 - выводим текст типа "не найдено".
	$count = count($result); //Плюсуем, т.к. count может быть в модерне 1+, а в классике 0 (в случае фильтра пластика, напр.), но прога считает последнее значение (0)
	if ($count == 0) {
		$a = "
			<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>
			<div class='noResultCatalog'>Сожалеем, но по Вашему запросу ничего не найдено. Попробуйте применить другие фильтры.
				<br><br>
				<a href='/catalog/' style='font-size:16px;'>
					<img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Обнулить фильтры 
				</a>
			</div>
			";
	}
	if ($result) {
		//$a.="<div id='$typeEng' style='width:100%; font-size:21px; font-weight:600; margin: 30px 0 20px 0;'>Кухни $typeKitchen</div><hr>";
		$a .= "<div id='$resultType' style='width:100%; font-size:21px; font-weight:600; margin: 30px 0 20px 0;'>Каталог кухонь</div><hr>";
	}
	foreach ($result as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
		$kitchen_header = $row->kitchen_header;
		$alias = $row->alias;
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
		$sales_cost = $cost - ($cost / 100 * $sales);
		$sales_cost = round($sales_cost);
		if ($sales !== 'no') {
			$cost = "<span style='text-decoration:line-through; font-size:12px; color:red;'>$cost</span> $sales_cost";
			$salesDiv = "<div style='font-size:15px; position:absolute; top:-5px; right:-5px; background:darkred; padding:5px 10px; color:white;'> Скидка: $sales% </div>";
		} else
			$salesDiv = "";
		$permalink = get_permalink();


		//Подключаем аватар по типу материала
		$echoavatar = $avatar;
		//если цена "сначала дорогие" и выбран материал, то выводить аватар массива,если он есть, как самого дорогого. Иначе шпона, иначе акрила и т.д. 
		if ($_GET['cost'] == 'expensive' or empty($_GET['cost'])) {
			if ($_GET['dsp'] == 'yes' and !empty($avatar_dsp))
				$echoavatar = $avatar_dsp;
			if ($_GET['steklo'] == 'yes' and !empty($avatar_steklo))
				$echoavatar = $avatar_steklo;
			if ($_GET['plastic'] == 'yes' and !empty($avatar_plastik))
				$echoavatar = $avatar_plastik;
			if ($_GET['akril'] == 'yes' and !empty($avatar_akril))
				$echoavatar = $avatar_akril;
			if ($_GET['shpon'] == 'yes' and !empty($avatar_shpon))
				$echoavatar = $avatar_shpon;
			if ($_GET['massiv'] == 'yes' and !empty($avatar_massiv))
				$echoavatar = $avatar_massiv;
		}
		//если цена "сначала дешевые", то подгружаем аватар самого дешевого материала.
		elseif ($_GET['cost'] == 'cheap' and empty($_GET['massiv']) and empty($_GET['akril']) and empty($_GET['steklo']) and empty($_GET['plastic']) and empty($_GET['shpon']) and empty($_GET['dsp'])) {
			if (!empty($avatar_massiv))
				$echoavatar = $avatar_massiv;
			if (!empty($avatar_akril))
				$echoavatar = $avatar_akril;
			if (!empty($avatar_steklo))
				$echoavatar = $avatar_steklo;
			if (!empty($avatar_plastik))
				$echoavatar = $avatar_plastik;
			if (!empty($avatar_shpon))
				$echoavatar = $avatar_shpon;
			if (!empty($avatar_dsp))
				$echoavatar = $avatar_dsp;
		}
		//Если выбран конкретный материал или несколько, то подгружать аватар от дешевого к дорогому
		else {
			if ($_GET['massiv'] == 'yes' and !empty($avatar_massiv))
				$echoavatar = $avatar_massiv;
			if ($_GET['akril'] == 'yes' and !empty($avatar_akril))
				$echoavatar = $avatar_akril;
			if ($_GET['steklo'] == 'yes' and !empty($avatar_steklo))
				$echoavatar = $avatar_steklo;
			if ($_GET['plastic'] == 'yes' and !empty($avatar_plastik))
				$echoavatar = $avatar_plastik;
			if ($_GET['shpon'] == 'yes' and !empty($avatar_shpon))
				$echoavatar = $avatar_shpon;
			if ($_GET['dsp'] == 'yes' and !empty($avatar_dsp))
				$echoavatar = $avatar_dsp;
		}

		$a .= "
			<div class='kitchen_point' style=''>
				
				<a href='$permalink$alias/' target='_blank'>
					<div style='width:100%; height:220px; background:url($echoavatar) no-repeat; background-size:cover; background-position:center; margin-bottom:15px;'></div>
				</a>
				<span style='font-size:18px; font-weight:200; margin-top:35px; color:black;'>$kitchen_header</span>
				<hr style='margin-bottom:15px;'>
				<div class='catalogue_cost' style='width:100%; text-align:right; font-size:16px; color:darkred; font-weight:600; position:relative;'>
				<a href='/basic_equipment/' target='_blank' style='text-decoration:none; font-weight:200;' title='Цена на базовую модель'>от $cost <span style='font-size:14px;'>руб./п.м</span> </a>
				<i class='fa fa-info-circle basic_equipment_info' aria-hidden='true'></i>	
				<div class='basic_equipment_div'>
					<br><span style='font-size:14px; font-weight:600;'>Цена указана на базовую модель</span><br>
					<span style='font-size:12px; font-weight:400;'>Что из себя представляет базовая модель Вы можете 
					<a href='/basic_equipment/' target='_blank' style='color:blue; text-decoration:underline;'>прочитать тут</a>.</span> <br><br>
					
				</div>
				<a href='$permalink$alias/' target='_blank'><button class='myBtn moreinfocataloguebtn' style='float:left; margin-top:-5px; padding:5px 10px; font-size:14px;'>Подробнее...</button></a>

				</div>
			</div>
			";
	}

	$a .= "</div>";
	$a .= "<div style='clear:both;'></div>";
	$zalip = "
		<script>
		/*ПРИЛИПАНИЕ ФИЛЬТРОВ В КАТАЛОГЕ*/

		(function(){
		var a = document.querySelector('#filtres'), b = null, P = 0;
		window.addEventListener('scroll', Ascroll, false);
		document.body.addEventListener('scroll', Ascroll, false);
		function Ascroll() {
		  if (b == null) {
			var Sa = getComputedStyle(a, ''), s = '';
			for (var i = 0; i < Sa.length; i++) {
			  if (Sa[i].indexOf('overflow') == 0 || Sa[i].indexOf('padding') == 0 || Sa[i].indexOf('border') == 0 || Sa[i].indexOf('outline') == 0 || Sa[i].indexOf('box-shadow') == 0 || Sa[i].indexOf('background') == 0) {
				s += Sa[i] + ': ' +Sa.getPropertyValue(Sa[i]) + '; '
			  }
			}
			filtres.style.background='white';
			b = document.createElement('div');
			b.style.cssText = s + ' box-sizing: border-box; overflow-y:auto; max-height:580px; background:#f5f5f5; /*width: ' + a.offsetWidth + 'px;*/ min-width:245px;';
			a.insertBefore(b, a.firstChild);
			var l = a.childNodes.length;
			for (var i = 1; i < l; i++) {
			  b.appendChild(a.childNodes[1]);
			}
			a.style.height = b.getBoundingClientRect().height + 'px';
			a.style.padding = '0';
			a.style.border = '0';
		  }
		  var Ra = a.getBoundingClientRect(),
			  R = Math.round(Ra.top + b.getBoundingClientRect().height - document.querySelector('#mycatalog').getBoundingClientRect().bottom);  // селектор блока, при достижении нижнего края которого нужно открепить прилипающий элемент
		  if ((Ra.top - P) <= 0) {
			if ((Ra.top - P) <= R) {
			  b.className = 'stop';
			  b.style.top = - R +'px';
			} else {
			  b.className = 'sticky';
			  b.style.top = P + 'px';
			}
		  } else {
			b.className = '';
			b.style.top = '';
		  }
		  window.addEventListener('resize', function() {
			a.children[0].style.width = getComputedStyle(a, '').width
		  }, false);
		}
		})();
		</script>
	";
	return $b . $a . $zalip;
}

function print_catalogue3($atts)
{


	$shortArray = shortcode_atts(
		array( 		//Добавляет ID в шорткод, со стартовым значением ALL
			'color' => "",
			'config' => "",
			'style' => "",
		),
		$atts
	);
	$shortColor = $shortArray['color'];
	$shortConfig = $shortArray['config'];
	$shortStyle = $shortArray['style'];

	//ПОД ФИЛЬТРЫ ПРОПИСЫВАЕМ MySQL
	$wherecolor = (!empty($shortColor)) ? "AND color LIKE '%$shortColor%'" : "";
	$whereconfig = (!empty($shortConfig)) ? "AND config LIKE '%$shortConfig%'" : "";
	$wherestyle = (!empty($shortStyle)) ? "AND style LIKE '%$shortStyle%'" : "";

	$rus = array(' ', 'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я', ' ');
	$lat = array('_', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya', ' ');

	$uri = $_SERVER['REQUEST_URI'];
	$qPos = strpos($uri, '?');

	global $wpdb;


	//ФИЛЬТРЫ ПО СТИЛЮ
	$x = "<div class='filterHeader'>Стиль:</div>";
	$sqlstyles = "SELECT * FROM gi_kitchen_filtres WHERE category = 'style'";
	$resultstyles = $wpdb->get_results($sqlstyles);
	foreach ($resultstyles as $rowstyles) {
		$styles = $rowstyles->filtervalue;
		$style_urls = $rowstyles->alias;
		$styleseng = str_replace($rus, $lat, $styles);
		$x .= "<label for='$styleseng' class='containerCheckbox'><input class='filter_form' id='$styleseng' type='checkbox' name='styles' data-url='$style_urls' data-value='$styles' value='$styles'/> $styles <span class='checkmarkCheckbox'></span></label>";
	}
	//ФИЛЬТРЫ ПО МОДЕЛЯМ
	$x .= "<div class='filterHeader'>Модель:</div>";
	$sqlmodel = "SELECT DISTINCT name FROM gi_kitchen ORDER BY name";
	$resultmodel = $wpdb->get_results($sqlmodel);
	foreach ($resultmodel as $rowmodel) {
		$models = $rowmodel->name;
		$modelseng = str_replace($rus, $lat, $models);
		$x .= "<label for='$modelseng' class='containerCheckbox'><input class='filter_form' id='$modelseng' type='checkbox' name='models' data-value='$models' value='$models'/> $models <span class='checkmarkCheckbox'></span></label>";
	}

	//ФИЛЬТРЫ ПО ЦВЕТУ
	$x .= "<div class='filterHeader'>Цвет:</div>";
	$sqlcolors = "SELECT * FROM gi_kitchen_filtres WHERE category = 'color' ORDER BY filtervalue";
	$resultcolors = $wpdb->get_results($sqlcolors);
	foreach ($resultcolors as $rowcolors) {
		$filtervalue = $rowcolors->filtervalue;
		$color = $rowcolors->color;
		$color_urls = $rowcolors->alias;
		$coloreng = str_replace($rus, $lat, $filtervalue);
		$x .= "<label for='$coloreng' class='containerCheckbox'><input class='filter_form' id='$coloreng' type='checkbox' name='color' data-url='$color_urls' data-value='$filtervalue' value='$filtervalue'/> $filtervalue <span class='checkmarkCheckbox'></span><div style='width:15px; height:15px; background:$color; float:left; margin:5px 5px 0 0;'></div> </label>";
	}
	$x .= "<div style='clear:both;'></div>";

	//ФИЛЬТРЫ ПО КОНФИГУРАЦИИ
	$x .= "<div class='filterHeader'>Конфигурация:</div>";
	$sqlconfig = "SELECT * FROM gi_kitchen_filtres WHERE category = 'config'";
	$resultconfig = $wpdb->get_results($sqlconfig);
	foreach ($resultconfig as $rowconfig) {
		$configs = $rowconfig->filtervalue;
		$configs_urls = $rowconfig->alias;
		$configeng = str_replace($rus, $lat, $configs);
		$x .= "<label for='$configeng' class='containerCheckbox'><input class='filter_form' id='$configeng' type='checkbox' name='config' data-url='$configs_urls' data-value='$configs' value='$configs'/> $configs <span class='checkmarkCheckbox'></span></label>";
	}



	$b = "<div id='filtres' class='filtres'>
			<img src='/wp-content/themes/cleanpress/images/close-black.png' class='closeFiltres' id='closeFiltres' onclick='closeFiltres();'/>
			<form method='GET' class='formFiltres'>
				<div class='filterHeader'>Сортировать по цене:</div>
				<label for='cheap' class='containerInput'><input class='filter_form' id='cheap' type='radio' name='cost' value='cheap' /> Сначала дешевые<span class='checkmark'></span></label>
				<label for='expensive' class='containerInput'> <input class='filter_form' id='expensive' type='radio' name='cost' value='expensive'/> Сначала дорогие <span class='checkmark'></span></label>
				
				<div class='filterHeader'>Материал:</div>
				<label for='massiv' class='containerCheckbox'><input class='filter_form' id='massiv' type='checkbox' name='material' value='Массив дерева' />  Массив дерева <span class='checkmarkCheckbox'></span></label>
				<label for='shpon' class='containerCheckbox'><input class='filter_form' id='shpon' type='checkbox' name='material' value='Шпон' /> Шпон <span class='checkmarkCheckbox'></span></label>
				<label for='plastic' class='containerCheckbox'><input class='filter_form' id='plastic' type='checkbox' name='material' value='Пластик' />  Пластик <span class='checkmarkCheckbox'></span></label>
				<label for='akril' class='containerCheckbox'><input class='filter_form' id='akril' type='checkbox' name='material' value='Акрил' />  Акрил <span class='checkmarkCheckbox'></span></label>
				<label for='dsp' class='containerCheckbox'><input class='filter_form' id='dsp' type='checkbox' name='material' value='ЛДСП' />  ЛДСП <span class='checkmarkCheckbox'></span></label>
				<label for='steklo' class='containerCheckbox'><input class='filter_form' id='steklo' type='checkbox' name='material' value='Стекло'/>  Стекло <span class='checkmarkCheckbox'></span></label>
			
				$x
				<br>
				<a href='/catalog/'><img src='/wp-content/themes/cleanpress/images/close-black.png' width='14'/> Сбросить фильтры</a>
			</form>
	</div>";
	$a = "<div id='mycatalog'>";


	$a .= "<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>";

	//$sql = "SELECT * FROM `gi_kitchen` WHERE `type` = '$typeKitchen' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
	$sql = "SELECT * FROM `gi_kitchen` WHERE `type` != '' ORDER BY name";
	$result = $wpdb->get_results($sql);
	//Смотрим, сколько выдает значений после примененных фильтров. Если 0 - выводим текст типа "не найдено".
	$count = count($result); //Плюсуем, т.к. count может быть в модерне 1+, а в классике 0 (в случае фильтра пластика, напр.), но прога считает последнее значение (0)
	if ($count == 0) {
		$a = "
		<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>
		<div class='noResultCatalog'>Сожалеем, но по Вашему запросу ничего не найдено. Попробуйте применить другие фильтры.
			<br><br>
			<a href='/catalog/' style='font-size:16px;'>
				<img src='/wp-content/themes/cleanpress/images/close-black.png' width='14'/> Обнулить фильтры 
			</a>
		</div>
		";
	}
	//СМОТРИМ СКОЛЬКО ВСЕГО МОДЕЛЕЙ НА ЭТОЙ СТРАНИЦЕ 
	$sqlpagination = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wherecolor $whereconfig $wherestyle";
	$resultpagination = $wpdb->get_results($sqlpagination);
	if (count($resultpagination) <= 42) {
		$display_pagination = "display:none;";
	}


	//ВЫГРУЖАЕМ БАЗОВЫЙ КАТАЛОГ, БЕЗ АЯКСА
	$sql = "SELECT * FROM `gi_kitchen` WHERE `type` !='' $wherecolor $whereconfig $wherestyle ORDER BY name LIMIT 0, 42";
	$result = $wpdb->get_results($sql);


	foreach ($result as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
		$kitchen_header = $row->kitchen_header;
		$alias = $row->alias;
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
		$sales_cost = $cost - ($cost / 100 * $sales);
		$sales_cost = round($sales_cost);
		if ($sales !== 'no') {
			$cost = "<span style='text-decoration:line-through; font-size:12px; color:red;'>$cost</span> $sales_cost";
			$salesDiv = "<div style='font-size:15px; position:absolute; top:-5px; right:-5px; background:darkred; padding:5px 10px; color:white;'> Скидка: $sales% </div>";
		} else
			$salesDiv = "";
		//$permalink = get_permalink();
		$permalink = "/catalog/";

		$kitchen = "
		<div class='kitchen_point' style=''>
			
			<a href='$permalink$alias/' target='_blank'>
				<div style='width:100%; height:220px; background:url($avatar) no-repeat; background-size:cover; background-position:center; margin-bottom:15px;'></div>
			</a>
			<span style='font-size:18px; font-weight:200; margin-top:35px; color:black;'>$kitchen_header</span>
			<hr style='margin-bottom:15px;'>
			<div class='catalogue_cost' style='width:100%; text-align:right; font-size:16px; color:darkred; font-weight:600; position:relative;'>
				<a href='/basic_equipment/' target='_blank' style='text-decoration:none; font-weight:200;' title='Цена на базовую модель'>от $cost <span style='font-size:14px;'>руб./п.м</span> </a>
				<i class='fa fa-info-circle basic_equipment_info' aria-hidden='true'></i>	
				<div class='basic_equipment_div'>
					<br><span style='font-size:14px; font-weight:600;'>Цена указана на базовую модель</span><br>
					<span style='font-size:12px; font-weight:400;'>Что из себя представляет базовая модель Вы можете 
					<a href='/basic_equipment/' target='_blank' style='color:blue; text-decoration:underline;'>прочитать тут</a>.</span> <br><br>
			</div>
			<a href='$permalink$alias/' target='_blank'><button class='myBtn moreinfocataloguebtn' style='float:left; margin-top:-5px; padding:5px 10px; font-size:14px;'>Подробнее...</button></a>

		</div>
		</div>
		";
	}

	$a .= "<div id='ajaxcatalogue'>";
	//СЮДА ЛЕТИТ АЯКС
	$a .= "$kitchen";
	$a .= "</div>";

	//ПАГИНАЦИЯ
	$a .= "<div class='pagination_div' style='$display_pagination'>";
	$pages = ceil($count / 42);
	for ($i = 1; $i <= $pages; $i++) {
		$a .= "<button id='pagebtn_$i' value='$i' class='pagination' onclick='get_page(this);' style='padding:0; border:none; width:40px; height:40px; line-height:40px; text-align:center; background:#114977; color:white; margin:3px; border-radius:50%; cursor:pointer;'>$i</button>";
	}
	$a .= "</div>";


	// $a .= "$c";
	$a .= "</div>";
	$a .= "<div style='clear:both;'></div>";


	$myjs = "
	<style>
	.selected {background:#c6c6c6 !important;}
	</style>
		<script>
		//Устанавливаем чекед на фильтры, примененные шорткодом
		jQuery('[data-value=\"$shortColor\"]').prop('checked', true);
		jQuery('[data-value=\"$shortStyle\"]').prop('checked', true);
		jQuery('[data-value=\"$shortConfig\"]').prop('checked', true);
		
		
		
		/*каталог надо выгружать не аяксом, а для сео....		
		//АЯКС КАТАЛОГ
		function show(){  
			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {page:1},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});
		} 
		jQuery(document).ready(function(){ 
			show();  
		});*/
		
		
		jQuery('#pagebtn_1').addClass('selected');
		function get_page(obj){
			var pagenumber = obj.value;
			var pageid = document.getElementById(obj.id);
			jQuery('.pagination').removeClass('selected');
			jQuery(obj).addClass('selected');
			var myscrollto = document.getElementById('content');
			//получаем чекбоксы
			var cost = jQuery('input[name=cost]:checked').val();
			var materials_arr = new Array();
			var colors_arr = new Array();
			var configs_arr = new Array();
			var styles_arr = new Array();
			var models_arr = new Array();

			jQuery('input:checkbox[name=material]:checked').each(function(){
				materials_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=color]:checked').each(function(){
				colors_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=config]:checked').each(function(){
				configs_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=styles]:checked').each(function(){
				styles_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=models]:checked').each(function(){
				models_arr.push(jQuery(this).val());
			});
			jQuery('html, body').animate({scrollTop: jQuery(myscrollto).offset().top}, 1000);
			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {page:pagenumber, cost:cost, materials: materials_arr, styles: styles_arr, configs: configs_arr, colors:colors_arr, models:models_arr},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});				
		}
		
		//При применении фильтров
		jQuery( '.filter_form' ).change(function() {
			var filterurl = jQuery(this).attr('data-url');
			var countstyles = jQuery('input:checkbox[name=styles]:checked').length;
			var countcolors = jQuery('input:checkbox[name=color]:checked').length;
			var countconfigs = jQuery('input:checkbox[name=config]:checked').length;
			var allcount = countstyles + countcolors + countconfigs;
			if(filterurl && allcount == 1){
				var baseUrl = 'https://ideal-kuhni.ru/catalog/';
				var newUrl = baseUrl + filterurl + '/';
				history.pushState(null, null, newUrl);
			}
			if(allcount == 0 | allcount > 1){
				//var baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
				var newUrl = 'https://ideal-kuhni.ru/catalog/';
				history.pushState(null, null, newUrl);
			}
			var cost = jQuery('input[name=cost]:checked').val();
			var materials_arr = new Array();
			var colors_arr = new Array();
			var configs_arr = new Array();
			var styles_arr = new Array();
			var models_arr = new Array();
			
			jQuery('input:checkbox[name=material]:checked').each(function(){
				materials_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=color]:checked').each(function(){
				colors_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=config]:checked').each(function(){
				configs_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=styles]:checked').each(function(){
				styles_arr.push(jQuery(this).val());
			});
			jQuery('input:checkbox[name=models]:checked').each(function(){
				models_arr.push(jQuery(this).val());
			});
			
			//Переключение на 1 страницу
			jQuery('.pagination').removeClass('selected');
			jQuery('#pagebtn_1').addClass('selected');
			//Листаем вверх
			//var myscrollto = document.getElementById('content');
			//jQuery('html, body').animate({scrollTop: jQuery(myscrollto).offset().top}, 1000);

			jQuery.ajax({
				url: '../../wp-content/plugins/bp_kitchen_print/ajax_catalogue.php',
				type: 'POST',
				data: {cost:cost, materials: materials_arr, styles: styles_arr, configs: configs_arr, colors:colors_arr, models:models_arr, url: filterurl},
				cache: false,
				success: function(html){ 
					jQuery('#ajaxcatalogue').html(html);
				} 
			});
			
		});
		</script>
	";
	return $b . $a . $myjs;
}

//Вывод страницы товара


function print_kitchen()
{
	//узнаем ярлык страницы - ВРЕМЕННО, ПОТОМ СДЕЛАТЬ ШОРТКОД С id=''
	global $post;
	$post_slug = $post->post_name;
	// global $wpdb;
	/** @var Container */
	global $servicesContainer;

	/** @var DBWorker */
	$dbWorker = $servicesContainer->get('DBWorker');
	/** @var DBUtilities */
	$dbUtilities = $servicesContainer->get('DBUtilities');

	$version = setVSite();

	$time = time();

	// $sql = "SELECT * FROM `gi_kitchen` WHERE name_eng = '$post_slug'";
	// $result = $wpdb->get_results($sql);
	$result_2 = $dbWorker->selectSimple('Kitchen', 'name_eng', $post_slug);
	// print_r($result_2);
	$name = $result_2['name'];
	$id_kitchen = $result_2['newid'];
	// Меняем ; на , в строке, переводя ее в массив и и обратно
	$material_arr = explode(';', $result_2['material']);
	$material_arr = array_diff($material_arr, array('')); //удаляем пустые значения
	$material = implode(', ', $material_arr);
	$style = str_replace(',', '<br>', $result_2['style']);
	//Цена со скидкой

	if (stripos($material, 'МДФ') !== false) {
		$nonbacterial = "
			<a href='/antibacterial-mdf/'>
				<div class='kitchenpage_antibacterial_stiker'></div>
			</a>
			";
	} else
		$nonbacterial = "";
	$newTableTop = "";
	$tabletop_timestamp = $result_2['timestamp'];
	if ($time - $tabletop_timestamp <= 7776000 * 2) {
		$newTableTop = "<span class='new-span'>NEW</span> ";
		$datanew = "data-new='yes'";
	} else {
		$newtabletop = "";
		$datanew = "data-new='no'";
	}
	ob_start();
	?>

	<script src='/wp-content/plugins/bp_kitchen_print/js/lozad.min.js'></script>

	<script>
		let kitchenName = "<?php echo $name; ?>";
		console.log(kitchenName);
	</script>
	<div class='kitchen_card_slider' style='position:relative;'>
		<?php echo $nonbacterial; ?>
		<div class='new-label'><?php echo $newTableTop ?></div>
		<div class='fotorama' data-allowfullscreen='native' data-keyboard='true' data-nav='thumbs' data-width='100%'
			data-max-width='100%' data-thumbwidth='170px' data-thumbheight='110px' data-thumbmargin='16'>
			<?php
			$condition = $dbUtilities->preparenSingleOperSepar('id_kitchen', $id_kitchen, ' = ', '');
			$condition['OrderBy'] = '`rank_visual` ASC';
			$resultVisiual = $dbWorker->selectUni_2('KitchenVisualisation', $condition);
			if (!empty($resultVisiual)) {
				foreach ($resultVisiual as $rowVisiual) {
					$visual_name = $rowVisiual['nameVisual'];
					$visual_url_image = $rowVisiual['urlImage'];
					$visual_url_thumb = $rowVisiual['urlThumb'];
					$visual_url_image = "https://geosideal.ru" . str_replace('../', '/', $visual_url_image);
					$visual_url_thumb = "https://geosideal.ru" . str_replace('../', '/', $visual_url_thumb);
					?>
					<a href='<?php echo $visual_url_image; ?>' data-caption='<?php echo $visual_name; ?>'><img
							src='<?php echo $visual_url_thumb; ?>' width='180' height='120' style='margin:0 16px; background: #f0f0f0;'
							class='lozad'>
					</a>
					<?php
				}
			}
			?>
		</div>
	</div>
	<div class='kitchen_card_info_div'>
		<div class='someheader'>
			<?php echo $name; ?>
		</div>
		<div class='kitchen_card_info_div_content'>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Стиль кухни
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $style; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Материал фасада
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $material; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Древесина
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $result_2['wood']; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Пластик
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $result_2['plastic']; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					<a href='/kitchen-accessories/' title='Смотреть фурнитуру'>Фурнитура</a>
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $result_2['furnitura']; ?>
				</div>
			</div>
		</div>
		<div class='kitchen_info_button'>
			<button class='myBtn' id='opencitieswindow'>Где купить</button>
		</div>
	</div>
	<div style='clear:both;'></div>
	<br>
	<div class='about_kitchen_div'>
		<div class='about_kitchen'>
			<h2>Особенности кухни</h2>
			<?php echo $result_2['description']; ?>
		</div>

	</div>

	<div class='youtube-video'>
		<?php echo $result_2['video']; ?>
	</div>
	<?php

	$gola_button = "";
	if (mb_strpos($result_2['handles'], 'профиль')) {
		$gola_button = '<button class="kitchen_card_button gola_button" value="gola">Gola-профиль</button>'; ?>
		<script>

		// let handles = "<?php echo $result_2['handles']; ?>";
		// console.log(handles);
	</script>
<?php 
	} else { 
		$gola_button = '';?>
		<script>

		// let handles = "<?php echo $result_2['handles']; ?>";
		// console.log(handles);
	</script>
		<?php
	}
	?>
	<div class="kitchen_card_ajax_block">
		<button class="kitchen_card_button facades_button" id="selected-button" value="facades">Фасады</button>
		<button class="kitchen_card_button tabletops_button" value="tabletops">Столешницы</button>
		<button class="kitchen_card_button handles_button" value="handles">Ручки</button>
		<?php echo $gola_button; ?>
	</div>
	<?php
	$resultMaterialType = $dbWorker->selectCrossTable(
		'gi_facades_list',
		'gi_facade_material',
		'type',
		'id',
		'rank_material',
		'kitchens',
		$name,
		'type'
	);
	$resultMaterialType = is_array($resultMaterialType) ? $resultMaterialType : [$resultMaterialType];
	?>
	<div class="kitchen_card_ajax_block_inner" data-where="facades-list">
		<?php
		foreach ($resultMaterialType as $key => $rowOneType) {
			$resultMaterialNew = $dbWorker->selectSimple('gi_facade_material', 'id', $rowOneType);
			$classSelected = $key === 0 ? 'selected-inner-button' : '';
			?>
			<div class="kitchen_card_inner_block <?php echo $classSelected ?>"
				data-material-id="<?php echo $resultMaterialNew['id']; ?>">
				<?php echo $resultMaterialNew['name']; ?>
			</div>
			<?php
		}
		?>
	</div>
	
	<div class="kitchen_card_facade_div">
		<?php
		$condition = $dbUtilities->prepareEqualAndLike($resultMaterialType[0], $name, 'type', 'kitchens');
		$condition['OrderBy'] = '`facade_rank` ASC';
		$facades = $dbWorker->selectUni_2('FacadesList', $condition);
		$facades = is_array($facades) ? $facades : [$facades];
		$facades = isset($facades[0]) ? $facades : [$facades];
		foreach ($facades as $rowFacade) {
			$facadeName = !empty($rowFacade['name']) ? $rowFacade['name'] : '';
			$facadeImage = !empty($rowFacade['image']) ? 'https://geosideal.ru/wp-content/uploads/facades_list/textures/' . $rowFacade['image'] : '';
			$facadeThumb = !empty($rowFacade['image']) ? 'https://geosideal.ru/wp-content/uploads/facades_list/thumbnails/' . $rowFacade['image'] : '';
			$facadeDescription = !empty($rowFacade['description']) ? $rowFacade['description'] : '';

			$newfacade = !empty($rowFacade['newfacade']) ? " <img src='/wp-content/uploads/2020/10/new.png' class='new-span'> " : "";
			$silver = !empty($rowFacade['silver']) ? " <img src='/wp-content/uploads/2020/10/silver.png' class='silver-span'> " : "";
			$datanew = !empty($rowFacade['newfacade']) ? "data-new='yes'" : "data-new='no'";
			$datasilver = !empty($rowFacade['silver']) ? "data-silver='yes'" : "data-silver='no'";
			$dataantibac = !empty($rowFacade['antibac']) ? "data-antibac='yes'" : "data-antibac='no'"; ?>
			<div class="facades_list_item">
				<a data-lightbox='image-3' <?php echo $datanew; ?>	<?php echo $datasilver; ?> href='<?php echo $facadeImage; ?>'>
					<?php echo $newfacade; ?>
					<?php echo $silver; ?>
					<img src='<?php echo $facadeThumb; ?>' class='lozad'>
				</a>
				<span class="list_item_name"><?php echo $facadeName; ?></span>
				<span class="list_item_description"><?php echo $facadeDescription; ?></span>
			</div>
			<?php
		}
		?>
	</div>
	<div class='more_kitchens'>
		<p class='h3'>Похожие модели кухонь</p><br>
		<?php
		$arrSimilar = array_filter(explode(',', $result_2['similar']));
		$condition = $dbUtilities->createConditionQueryIN('name', $arrSimilar);
		$resultLike = $dbWorker->selectUni_2('Kitchen', $condition);
		// print_r($resultLike);
		if (!empty($resultLike)) {
			$resultLike = isset($resultLike[0]) ? $resultLike : [$resultLike];
			foreach ($resultLike as $rowLike) {
					$names = $rowLike['name'];
					$aliases = $rowLike['nameEng'];
					$thisava = $rowLike['avatar'];
					$materials = $rowLike['material'];
					?>
					<a href='/catalog/<?php echo $aliases; ?>/'>
						<div class='byer_grid_div' style='position:relative; margin-top:32px;'>
							<div class='byer_grid_image'
								style='background:url(<?php echo $thisava; ?>) no-repeat; background-size:cover; background-position:center;'>
							</div>
							<div class='byer_grid_header'><?php echo $names; ?><br>
								<span style='font-size:12px;'><b>Материал:</b>
									<span style='font-weight:200;'><?php echo $materials; ?></span>
								</span>
							</div>
						</div>
					</a>
					<?php
			}
		}
		?>
	</div>
	<p>* Изображение на вашем устройстве из-за особенностей цветопередачи может незначительно отличаться от реального цвета
		кухни</p>
	<p>** Краска, нанесенная на древесину, может незначительно отличаться по тону от образца цвета в бумажном веере-каталоге
	</p>
	<script>
		var opencitieswindow = document.getElementById('opencitieswindow');
		opencitieswindow.onclick = function () {
			gde_zakazat_bg.style.display = 'block';
		}	
	</script>
	<script src='/wp-content/plugins/bp_contracts/js/utility.js<?php echo $version; ?>'></script>
	<script src="/wp-content/plugins/bp_kitchen_print/js/script.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;

}

?>