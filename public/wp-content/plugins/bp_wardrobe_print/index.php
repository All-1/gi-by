<?php
/*Plugin Name: bp_wardrobe_print
Description: Работа с гардеробами. Вывод информации на страницу.
Version: 1.0
Author: Business Park*/
use PersonalAccount\Workers\DBWorker;
use PersonalAccount\Core\Container;
use PersonalAccount\Utilities\DBUtilities;

add_shortcode('catalogue_wardrobe_print', 'print_wardrobe_catalogue');
add_shortcode('wardrobe_print', 'print_wardrobe');
add_action('wp_enqueue_scripts', 'print_wardrobe_scripts');

function print_wardrobe_scripts()
{
	//wp_enqueue_script('jquery');
}

function print_wardrobe_catalogue($atts)
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
	// $x .= "<div class='filterHeader'>Стиль:</div>";
	// $sqlstyles = "SELECT * FROM gi_wardrobe_filtres WHERE category = 'style'";
	// $resultstyles = $wpdb->get_results($sqlstyles);
	// foreach ($resultstyles as $rowstyles) {
	// 	$styles = $rowstyles->filtervalue;
	// 	$style_urls = $rowstyles->alias;
	// 	$styleseng = str_replace($rus, $lat, $styles);
	// 	$x .= "<label for='$styleseng' class='containerCheckbox'><input class='filter_form' id='$styleseng' type='checkbox' name='styles' data-url='$style_urls' data-value='$styles' value='$styles'/> $styles <span class='checkmarkCheckbox'></span></label>";
	// }

	$x .= "<div style='clear:both;'></div>";

	$b = "<div id='filtres' class='filtres'>
			<img src='/wp-content/themes/wp-diary/images/close-black.png' class='closeFiltres' id='closeFiltres' onclick='closeFiltres();'/>
			<form method='GET' class='formFiltres'>
				$x
				<br>
				<a href='/garderoby/'><img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Сбросить фильтры</a>
			</form>
	</div>";
	$a = "<div id='mycatalog'>";


	$a .= "<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>";

	//$sql = "SELECT * FROM `gi_kitchen` WHERE `type` = '$typeKitchen' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
	$sql = "SELECT * FROM `gi_wardrobe` WHERE `type` !='' ORDER BY name";
	$result = $wpdb->get_results($sql);
	//Смотрим, сколько выдает значений после примененных фильтров. Если 0 - выводим текст типа "не найдено".
	$count = count($result); //Плюсуем, т.к. count может быть в модерне 1+, а в классике 0 (в случае фильтра пластика, напр.), но прога считает последнее значение (0)
	if ($count == 0) {
		$a = "
		<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>
		<div class='noResultCatalog'>Сожалеем, но по Вашему запросу ничего не найдено. Попробуйте применить другие фильтры.
			<br><br>
			<a href='/garderoby/' style='font-size:16px;'>
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
	$sql1 = "SELECT * FROM `gi_wardrobe` WHERE `type` !='' $wherecolor $whereconfig $wherestyle $wherematerial AND public != 'no' ORDER BY name ASC";
	$results = $wpdb->get_results($sql1);

	$wardrobes = '';
	foreach ($results as $row) {
		$name = $row->name;
		$additional_name = $row->additional_name;
		$bind_to = $row->bind_to;
		$name_eng = $row->name_eng;
		$description = $row->description;
		$avatar = $row->avatar;
		$avatar = $row->avatar;
		$directory = $row->folder;
		$type = $row->type;
		$cost = $row->basic_cost;
		$cost = round($cost / 2.3);
		$time = time();
		$sales = $row->sales;
		if ($bind_to == "Астра" or $bind_to == "Дамиана" or $bind_to == "Миссури" or $bind_to == "Алегри" or $bind_to == "Джаспер") {
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

		$echoavatar = "https://geosideal.ru$avatar";

		//var_dump($parent_ids);
		//$permalink = get_permalink();
		$permalink = "/garderoby/";


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
		$wardrobe = "
			<div class='kitchen_point' style=''>
				$emblems
				<a href='$permalink$name_eng/' target='_blank'>
					<div class='new-label'>$newtabletop</div>
					<div class='kitchen_avatar' style='background:url($echoavatar) no-repeat; background-size:cover; background-position:center;'></div>
					
					<div class='kitchen_cat_content'>
						<span class='kitchen_name'>Гардероб $name</span><br>
						<span class='additional_name'>$additional_name</span><br><br>
						<span><span style='font-weight:400;'><b>Материал:</b></span> $material</span><br>
						<div class='catalogue_cost' style='width:100%; text-align:right; font-size:16px; color:darkred; position:relative;'>
					</div>
					</div>
				</a>
			</div>
		";
		$wardrobes .= $wardrobe;
	}

	$a .= "<div id='ajaxcatalogue'>";
	//СЮДА ЛЕТИТ АЯКС
	$a .= "$wardrobes";
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
		$c .= "<a href='/garderoby/$materiallink/'> <button class='myBtn' style='padding:2px 4px; font-size:10px; border-color:#dcdcdc;'>$materialname </button> </a>";
	}


	// $sqlstylelinks = "SELECT filtervalue, alias FROM gi_wardrobe_filtres WHERE category='style'";
	// $resultstylelinks = $wpdb->get_results($sqlstylelinks);
	// $c .= "<br>По стилю: ";
	// foreach ($resultstylelinks as $rowstylelinks) {
	// 	$stylename = $rowstylelinks->filtervalue;
	// 	$stylelink = $rowstylelinks->alias;
	// 	$c .= "<a href='/garderoby/$stylelink/'> <button class='myBtn' style='padding:2px 4px; font-size:10px; border-color:#dcdcdc;'>$stylename </button> </a>";
	// }

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
				url: '../../wp-content/plugins/bp_wardrobe_print/ajax_catalogue.php',
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
				url: '../../wp-content/plugins/bp_wardrobe_print/ajax_catalogue.php',
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
				var baseUrl = 'https://geosideal.ru/garderoby/';
				var newUrl = baseUrl + urlval + '/';
				history.pushState(null, null, newUrl);
			}
			if(allcount == 0 | allcount > 1){
				//var baseUrl = window.location.protocol + '//' + window.location.host + window.location.pathname;
				var newUrl = 'https://geosideal.ru/garderoby/';
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
			console.log(styles_arr);
			jQuery.ajax({
				url: '../../wp-content/plugins/bp_wardrobe_print/ajax_catalogue.php',
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

//Вывод страницы товара


function print_wardrobe()
{
	global $post;
	$post_slug = $post->post_name;
	/** @var Container */
	global $servicesContainer;

	/** @var DBWorker */
	$dbWorker = $servicesContainer->get('DBWorker');
	/** @var DBUtilities */
	$dbUtilities = $servicesContainer->get('DBUtilities');

	$version = setVSite();
	$time = time();

	$result = $dbWorker->selectSimple('gi_wardrobe', 'name_eng', $post_slug);
	$name = $result['name'];
	$id_wardrobe = $result['newid'];
	$additional_name = isset($result['additionalName']) ? $result['additionalName'] : '';
	$bind_to = isset($result['bindTo']) ? $result['bindTo'] : '';

	// Меняем ; на , в строке, переводя ее в массив и обратно
	$material_arr = explode(';', $result['material']);
	$material_arr = array_diff($material_arr, array('')); // удаляем пустые значения
	$material = implode(', ', $material_arr);

	if (stripos($material, 'МДФ') !== false) {
		$nonbacterial = "
			<a href='/antibacterial-mdf/'>
				<div class='kitchenpage_antibacterial_stiker'></div>
			</a>
			";
	} else {
		$nonbacterial = "";
	}

	$newTableTop = "";
	$tabletop_timestamp = $result['timestamp'];
	if ($time - $tabletop_timestamp <= 7776000 * 2) {
		$newTableTop = "<span class='new-span'>NEW</span> ";
	}

	ob_start();
	?>

	<script src='/wp-content/plugins/bp_kitchen_print/js/lozad.min.js'></script>

	<script>
		let kitchenName = "<?php echo $name; ?>";
		let kitchenType = "wardrobe";
		console.log(kitchenName);
	</script>
	<div class='kitchen_card_slider' style='position:relative;'>
		<?php echo $nonbacterial; ?>
		<div class='new-label'><?php echo $newTableTop; ?></div>
		<div class='fotorama' data-allowfullscreen='native' data-keyboard='true' data-nav='thumbs' data-width='100%'
			data-max-width='100%' data-thumbwidth='170px' data-thumbheight='110px' data-thumbmargin='16'>
			<?php
			$condition = $dbUtilities->preparenSingleOperSepar('id_wardrobe', $id_wardrobe, ' = ', '');
			$condition['OrderBy'] = '`rank_visual` ASC';
			$resultVisual = $dbWorker->selectUni_2('gi_wardrobe_visualisation', $condition);
			if (!empty($resultVisual)) {
				// Handle both single result and array of results
				$resultVisual = isset($resultVisual[0]) ? $resultVisual : [$resultVisual];
				foreach ($resultVisual as $rowVisual) {
					$visual_name = isset($rowVisual['nameVisual']) ? $rowVisual['nameVisual'] : '';
					$visual_url_image = isset($rowVisual['urlImage']) ? $rowVisual['urlImage'] : '';
					$visual_url_thumb = isset($rowVisual['urlThumb']) ? $rowVisual['urlThumb'] : '';
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
			Гардероб <?php echo $name; ?>
		</div>
		<div class='someheader' style='padding:8px 0 0 32px; font-size:23px;'>
			<?php echo $additional_name; ?>
		</div>
		<div class='kitchen_card_info_div_content'>
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
					<?php echo $result['wood']; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					Пластик
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $result['plastic']; ?>
				</div>
			</div>
			<div class='kitchen_card_info_item'>
				<div class='kitchen_card_info_item_property'>
					<a href='/kitchen-accessories/' title='Смотреть фурнитуру'>Фурнитура</a>
				</div>
				<div class='kitchen_card_info_item_content'>
					<?php echo $result['furnitura']; ?>
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
			<h2>Особенности гардеробной системы</h2>
			<?php echo $result['description']; ?>
		</div>
	</div>

	<div class='youtube-video'>
		<?php echo $result['video']; ?>
	</div>

	<div class="kitchen_card_ajax_block">
		<button class="kitchen_card_button facades_button" id="selected-button" value="facades">Фасады</button>
		<button class="kitchen_card_button tabletops_button" value="fittings">Наполнение</button>
	</div>
	<?php
	// Use name for facade lookup - KitchenWorker will resolve bind_to for wardrobes via AJAX
	$resultMaterialType = $dbWorker->selectCrossTable(
		'gi_facades_list',
		'gi_facade_material',
		'type',
		'id',
		'rank_material',
		'kitchens',
		!empty($bind_to) ? $bind_to : $name,
		'type'
	);
	if (!empty($resultMaterialType)) {
		$resultMaterialType = is_array($resultMaterialType) ? $resultMaterialType : [$resultMaterialType];
	?>
	<div class="kitchen_card_ajax_block_inner" data-where="facades-list">
		<?php
		foreach ($resultMaterialType as $key => $rowOneType) {
			$resultMaterialNew = $dbWorker->selectSimple('gi_facade_material', 'id', $rowOneType);
			$classSelected = $key === 0 ? 'selected-inner-button' : '';
			?>
			<div class="kitchen_card_inner_block <?php echo $classSelected; ?>"
				data-material-id="<?php echo $resultMaterialNew['id']; ?>">
				<?php echo $resultMaterialNew['name']; ?>
			</div>
			<?php
		}
		?>
	</div>
	<div class="kitchen_card_facade_div">
		<?php
		$facadeTarget = !empty($bind_to) ? $bind_to : $name;
		$condition = $dbUtilities->prepareEqualAndLike($resultMaterialType[0], $facadeTarget, 'type', 'kitchens');
		$condition['OrderBy'] = '`facade_rank` ASC';
		$facades = $dbWorker->selectUni_2('FacadesList', $condition);
		if (!empty($facades)) {
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
				?>
				<div class="facades_list_item">
					<a data-lightbox='image-3' <?php echo $datanew; ?> <?php echo $datasilver; ?> href='<?php echo $facadeImage; ?>'>
						<?php echo $newfacade; ?>
						<?php echo $silver; ?>
						<img src='<?php echo $facadeThumb; ?>' class='lozad'>
					</a>
					<span class="list_item_name"><?php echo $facadeName; ?></span>
					<span class="list_item_description"><?php echo $facadeDescription; ?></span>
				</div>
				<?php
			}
		}
		?>
	</div>
	<?php } ?>
	<div class='more_kitchens'>
		<p class='h3'>Похожие модели кухонь</p><br>
		<?php
		$similarValue = isset($result['similar']) ? $result['similar'] : '';
		$arrSimilar = array_filter(explode(',', $similarValue));
		if (!empty($arrSimilar)) {
			$condition = $dbUtilities->createConditionQueryIN('name', $arrSimilar);
			$resultLike = $dbWorker->selectUni_2('Kitchen', $condition);
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
		}
		?>
	</div>
	<p>* Изображение на вашем устройстве из-за особенностей цветопередачи может незначительно отличаться от реального цвета
		фасада</p>
	<p>** Краска, нанесенная на древесину, может незначительно отличаться по тону от образца цвета в бумажном веере-каталоге
	</p>
	<script>
		var opencitieswindow = document.getElementById('opencitieswindow');
		opencitieswindow.onclick = function () {
			gde_zakazat_bg.style.display = 'block';
		}	
	</script>
	<script src='/wp-content/plugins/bp_contracts/js/utility.js <?php echo $version; ?>'></script>
	<script src="/wp-content/plugins/bp_kitchen_print/js/script.js <?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}
?>