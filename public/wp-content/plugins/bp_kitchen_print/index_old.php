<?php
/*Plugin Name: bp_kitchen_print
Description: Работа с кухнями. Вывод информации на страницу.
Version: 1.0
Author: Business Park*/
add_shortcode('catalogue_print', 'print_catalogue');
add_shortcode('kitchen_print', 'print_kitchen');
add_action( 'wp_enqueue_scripts', 'print_kitchens_scripts' );

function print_kitchens_scripts() {
	wp_enqueue_script('jquery');
	wp_enqueue_style( 'slider.css', plugins_url().'/bp_kitchen_print/assets/slider.css', null, true);
	wp_enqueue_style( 'lightbox.css', plugins_url().'/bp_kitchen_print/assets/lightbox.css', null, true);
	wp_enqueue_script( 'kitchen_slider', plugins_url().'/bp_kitchen_print/assets/slider.js', array('jquery'), null, true);
	wp_enqueue_script( 'kitchen_ligntbox', plugins_url().'/bp_kitchen_print/assets/lightbox-2.6.min.js', array('jquery'), null, true);
}

function print_catalogue($atts) {
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');

$uri  = $_SERVER['REQUEST_URI'];
$qPos = strpos($uri, '?');
	
	global $wpdb;


	//Сохраним заполненность форм пока что таким вот образом...и, наверное, навсегда....:D
	if(isset($_GET['cost']) and $_GET['cost'] == 'cheap') $cheapchecked = "checked"; else $cheapchecked = "";
	if(isset($_GET['cost']) and $_GET['cost'] == 'expensive') $expensivechecked = "checked"; else $expensivechecked = "";
	if ($_GET['massiv'] == "yes")$massivchecked = "checked"; else $massivchecked="";
	if ($_GET['shpon'] == "yes")$shponchecked = "checked"; else $shponchecked="";
	if ($_GET['plastic'] == "yes")$plasticchecked = "checked"; else $plasticchecked="";
	if ($_GET['akril'] == "yes")$akrilchecked = "checked"; else $akrilchecked="";
	if ($_GET['dsp'] == "yes")$dspchecked = "checked"; else $dspchecked="";
	if ($_GET['steklo'] == "yes")$steklochecked = "checked"; else $steklochecked="";
	if(isset($_GET['type']) and $_GET['type'] == 'classic') $classicchecked = "checked"; else $eclassicchecked = "";
	if(isset($_GET['type']) and $_GET['type'] == 'modern') $modernchecked = "checked"; else $modernchecked = "";
	
	$b.="<div id='filtres' class='filtres'>
			<img src='/wp-content/themes/wp-diary/images/close-black.png' class='closeFiltres' id='closeFiltres' onclick='closeFiltres();'/>
			<form method='GET' class='formFiltres'>
				<!-----<div class='filterHeader'>Сортировать по цене:</div>
				<label for='cheap' class='containerInput'><input id='cheap' type='radio' name='cost' value='cheap' $cheapchecked/> Сначала дешевые<span class='checkmark'></span></label>
				<label for='expensive' class='containerInput'> <input id='expensive' type='radio' name='cost' value='expensive' $expensivechecked/> Сначала дорогие <span class='checkmark'></span></label>
				---->
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
				<a href='/catalog/'><img src='/wp-content/themes/wp-diary/images/close-black.png' width='14'/> Сбросить фильтры</a>
			</form>
	</div>";
	$a.="<div id='mycatalog'>";

	//ФИЛЬТР ПО ЦЕНЕ
	if (!empty($_GET['cost'])){
		if($_GET['cost']=='cheap'){
			$orderby = "basic_cost ASC";
		}
		else {
			$orderby = "basic_cost DESC";
		}
	}
	else $orderby = "name";
	//ФИЛЬТР ПО ТИПУ (КЛАССИКА И МОДЕРН)
	if(!empty($_GET['type'])){
		if($_GET['type'] == 'classic'){
			$wheretype = "WHERE type = 'Классика'";
		}
		elseif($_GET['type']== 'modern'){
			$wheretype = "WHERE type = 'Модерн'";
		}
	}
	else {
		$wheretype = "";
	}
	//ФИЛЬТР ПО МАТЕРИАЛАМ
	$and = "AND (";
	$or = "OR";
	if($_GET['massiv'] == 'yes'){$wheremassiv = "$and material LIKE '%ассив%'"; $and = "OR";} else {$wheremassiv = "";}
	if($_GET['shpon'] == 'yes'){$whereshpon = "$and material LIKE '%пон%'"; $and = "OR";} else {$whereshpon = "";}
	if($_GET['plastic'] == 'yes'){$whereplastic = "$and material LIKE '%ластик%'"; $and = "OR";} else {$whereplastic = "";}
	if($_GET['akril'] == 'yes'){$whereakril = "$and material LIKE '%крил%'"; $and = "OR";} else {$whereakril = "";}
	if($_GET['dsp'] == 'yes'){$wheredsp = "$and material LIKE '%ДСП%'"; $and = "OR";} else {$wheredsp = "";}
	if($_GET['steklo'] == 'yes'){$wheresteklo = "$and material LIKE '%текло%'"; $and = "OR";} else {$wheresteklo = "";}
	//Чтобы применить несколько условий, надр занести все OR в скобки. Открывается в переменной $and. 
	//Если хотя бы 1 фильтр задан - вносим в переменную $skobka закрывающую скобку. Если нет, то пофиг, т.к. переменная $and и не используется. Хитро?
	if(isset($_GET['massiv']) or isset($_GET['shpon']) or isset($_GET['plastic']) or isset($_GET['akril']) or isset($_GET['dsp'])){
		$skobka = ")";
	}
	else $skobka = "";
	
	
	$a.="<button class='myBtn openFiltres' id='openFiltres' onclick='showFiltres();'>Показать фильтры поиска</button>";
	$sqlType = "SELECT DISTINCT type FROM gi_kitchen $wheretype ORDER BY type DESC";
	$resultType = $wpdb->get_results($sqlType);
	foreach ($resultType as $rowType) {
		
		$typeKitchen = $rowType->type;
		$typeEng = str_replace($rus, $lat, $typeKitchen);
		
		$sql = "SELECT * FROM `gi_kitchen` WHERE `type` = '$typeKitchen' $wheremassiv $whereshpon $whereplastic $whereakril $wheredsp $wheresteklo $skobka ORDER BY $orderby";
		$result = $wpdb->get_results($sql);
		//Смотрим, сколько выдает значений после примененных фильтров. Если 0 - выводим текст типа "не найдено".
		$count += count ($result); //Плюсуем, т.к. count может быть в модерне 1+, а в классике 0 (в случае фильтра пластика, напр.), но прога считает последнее значение (0)
		if($count == 0){
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
		if($result){
			$a.="
			<div id='$typeEng' style='width:100%; font-size:28px; font-weight:200; margin: 0;'>Кухни $typeKitchen</div><hr style='border:1px;'>";
		}
		foreach ($result as $row) {
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
			$directory = $row-> folder;
			$type = $row->type;
			$cost = $row->basic_cost;
			$cost = round($cost / 2.3);
			$sales = $row->sales;
			$sales_cost = $cost - ($cost / 100 * $sales);
			$sales_cost = round($sales_cost);
			$material = $row->material;
			$furnitura = $row->furnitura;
			if ($sales !=='no'){
				$cost = "<span style='text-decoration:line-through; font-size:12px; color:red;'>$cost</span> $sales_cost";
				$salesDiv = "<div class='salesDiv'> - $sales% </div>";
			}
			else $salesDiv="";
			$permalink = get_permalink();
			
			
			//Подключаем аватар по типу материала
			$echoavatar = $avatar;
			//если цена "сначала дорогие" и выбран материал, то выводить аватар массива,если он есть, как самого дорогого. Иначе шпона, иначе акрила и т.д. 
			if ($_GET['cost']=='expensive' or empty($_GET['cost'])){
				if ($_GET['dsp']=='yes' and !empty($avatar_dsp)) $echoavatar = $avatar_dsp;
				if ($_GET['steklo']=='yes' and !empty($avatar_steklo)) $echoavatar = $avatar_steklo;
				if ($_GET['plastic']=='yes' and !empty($avatar_plastik)) $echoavatar = $avatar_plastik;
				if ($_GET['akril']=='yes' and !empty($avatar_akril)) $echoavatar = $avatar_akril;
				if ($_GET['shpon']=='yes' and !empty($avatar_shpon)) $echoavatar = $avatar_shpon;
				if ($_GET['massiv']=='yes' and !empty($avatar_massiv)) $echoavatar = $avatar_massiv;
			}
			//если цена "сначала дешевые", то подгружаем аватар самого дешевого материала.
			elseif ($_GET['cost']=='cheap' and empty($_GET['massiv']) and empty($_GET['akril']) and empty($_GET['steklo']) and empty($_GET['plastic']) and empty($_GET['shpon']) and empty($_GET['dsp'])) {
				if (!empty($avatar_massiv)) $echoavatar = $avatar_massiv;
				if (!empty($avatar_akril)) $echoavatar = $avatar_akril;
				if (!empty($avatar_steklo)) $echoavatar = $avatar_steklo;
				if (!empty($avatar_plastik)) $echoavatar = $avatar_plastik;
				if (!empty($avatar_shpon)) $echoavatar = $avatar_shpon;
				if (!empty($avatar_dsp)) $echoavatar = $avatar_dsp;	
			}
			//Если выбран конкретный материал или несколько, то подгружать аватар от дешевого к дорогому
			else {
				if ($_GET['massiv']=='yes' and !empty($avatar_massiv)) $echoavatar = $avatar_massiv;
				if ($_GET['akril']=='yes' and !empty($avatar_akril)) $echoavatar = $avatar_akril;
				if ($_GET['steklo']=='yes' and !empty($avatar_steklo)) $echoavatar = $avatar_steklo;
				if ($_GET['plastic']=='yes' and !empty($avatar_plastik)) $echoavatar = $avatar_plastik;
				if ($_GET['shpon']=='yes' and !empty($avatar_shpon)) $echoavatar = $avatar_shpon;
				if ($_GET['dsp']=='yes' and !empty($avatar_dsp)) $echoavatar = $avatar_dsp;					
			}
			
			$a.= "
			<div class='kitchen_point' style=''>
				$salesDiv
				<a href='$permalink$name_eng/' target='_blank'>
					<div class='kitchen_avatar' style='background:url($echoavatar) no-repeat; background-size:cover; background-position:center;'></div>
				</a>
				<span class='kitchen_name'>$name</span><br>
				<span><span style='font-weight:400;'>Материал:</span> $material</span><br>
				<span><span style='font-weight:400;'>Фурнитура:</span> $furnitura</span><br>
				<span><span style='font-weight:400;'>Доступные цвета:</span> Широкий выбор цветов</span><br>
				<hr style='margin:20px 0;'>
				<div class='catalogue_cost' style='width:100%; text-align:right; font-size:16px; color:darkred; position:relative;'>
				<a class='cost_href' href='/basic_equipment/' target='_blank' title='Цена на базовую модель'>от $cost <span class='cost_href'>руб./п.м</span> </a>
				<i class='fa fa-info-circle basic_equipment_info' aria-hidden='true'></i>	
				<div class='basic_equipment_div'>
					<br><span style='font-size:14px; font-weight:600;'>Цена указана на базовую модель</span><br>
					<span style='font-size:12px; font-weight:400;'>Что из себя представляет базовая модель Вы можете 
					<a href='/basic_equipment/' target='_blank' style='color:blue; text-decoration:underline;'>прочитать тут</a>.</span> <br><br>
					
				</div>
				<a href='$permalink$name_eng/' target='_blank'><button class='myCatalogueBtn moreinfocataloguebtn'>Подробнее...</button></a>

				</div>
			</div>
			";
		}	
	}
	$a.="$c";
	$a.="</div>";
	$a.="<div style='clear:both;'></div>";
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
			  if (Sa[i].indexOf('overflow') == 0 || Sa[i].indexOf('padding') == 0 || Sa[i].indexOf('outline') == 0 || Sa[i].indexOf('box-shadow') == 0 || Sa[i].indexOf('background') == 0) {
				s += Sa[i] + ': ' +Sa.getPropertyValue(Sa[i]) + '; '
			  }
			}
			filtres.style.background='white';
			b = document.createElement('div');
			b.style.cssText = s + ' box-sizing: border-box; overflow-y:auto; max-height:580px; background:transparent; /*width: ' + a.offsetWidth + 'px;*/ min-width:280px; border:1px solid #cccccc;';
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
function print_kitchen(){
	//узнаем ярлык страницы - ВРЕМЕННО, ПОТОМ СДЕЛАТЬ ШОРТКОД С id=''
	global $post;
    $post_slug=$post->post_name;
	global $wpdb;
	$sql = "SELECT * FROM `gi_kitchen` WHERE name_eng = '$post_slug'";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row) {
		$name = $row->name;
		$name_eng = $row->name_eng;
		$description = $row->description;
		$avatar = $row->avatar;
		$directory = $row-> folder;
		$type = $row->type;
		$cost = $row->basic_cost;
		$handles = $row->handles;
		$tabletops = $row->tabletops;
		$material = $row->material;
		$wood = $row->wood;
		$plastic = $row->plastic;
		$furnitura = $row->furnitura;
		$sales = $row->sales;
		// Меняем ; на , в строке, переводя ее в массив и и обратно
		$material_arr = explode (';', $material);
		$material_arr = array_diff($material_arr, array('')); //удаляем пустые значения
		$material = implode($material_arr, ', ');
		//Цена со скидкой
		$cost = round($cost / 2.3);
		$sales_cost = round($cost - ($cost / 100 * $sales));
		if ($sales !=='no'){
				$cost = "от <span style='text-decoration:line-through; font-size:14px; color:red;'>$cost</span> <span style='color:darkred; font-size:21px; font-weight:600;'>$sales_cost</span> руб. (в базовой комплектации)";
		}
		
		
		//$a - вывод слайдера
		$a.="
			<div class='fotorama' data-allowfullscreen='native' data-keyboard='true' data-nav='thumbs' data-width='1000px' data-max-width='100%'>	
		";
		$plugins = plugins_url();
		$folder = "wp-content/plugins/bp_kitchen_add/images/kitchen/$post_slug/gallery/images";
		$thumbFolder = "wp-content/plugins/bp_kitchen_add/images/kitchen/$post_slug/gallery/thumbs";
		
		if(is_dir($folder)){
			$dir = scandir($folder);
			foreach ($dir as $number => $file){
				if($file == '.' or $file == '..'){
					continue;
				}
				$a.="
				  <a href='../../$folder/$file'><img src='../../$thumbFolder/$file' width='144' height='96'></a>			
				";
			}			
		}

					
		$e.="
		<div class='aboutKitchenDiv' style='text-align:left; width:1000px; margin:40px auto; position:relative; max-width:100%;'>
			
			<h2>Кухня <span style='color:darkred; font-weight:600;'>$name</span></h2> 
			<div class='salesDiv' style='left:270px;'>-$sales%</div>
			<i>Стиль кухни: $type </i><br><br>
			<div style='width:100%; margin-bottom:60px;'>
				<div style='width:100%; border:1px solid #dcdcdc;'>
					<div style='width:185px; float:left; padding:5px;'> Материал фасада:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px; border-left:1px solid #dcdcdc; position:relative;'> $material
						<button style='float:right; padding:7px 10px; border-color:#6d6d6d; color:#6d6d6d;' class='myBtn'>Материалы фабрики</button>
					</div>
					<div style='clear:both;'></div>
				</div>
				
				<div style='width:100%; border:1px solid #dcdcdc; background:#f9f9f9;'>
					<div style='width:185px; float:left; padding:5px;'> Древесина:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px; border-left:1px solid #dcdcdc;'> $wood</div>
					<div style='clear:both;'></div>
				</div>
				
				<div style='width:100%; border:1px solid #dcdcdc;'>
					<div style='width:185px; float:left; padding:5px;'> Пластик / Акрил:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px; border-left:1px solid #dcdcdc;'> $plastic</div>
					<div style='clear:both;'></div>
				</div>
				
				<div style='width:100%; border:1px solid #dcdcdc; background:#f9f9f9;'>
					<div style='width:185px; float:left; padding:5px;'> Фурнитура:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px; border-left:1px solid #dcdcdc; position:relative;'> $furnitura
						<button style='float:right; padding:7px 10px; border-color:#6d6d6d; color:#6d6d6d;' class='myBtn'>Фурнитура фабрики</button>
					</div>
					<div style='clear:both;'></div>
				</div>
				
				<div style='width:100%; border:1px solid #dcdcdc;'>
					<div style='width:185px; float:left; padding:5px; height:100%;'> Особенности кухни:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px; height:100%; border-left:1px solid #dcdcdc;'> $description</div>
					<div style='clear:both'></div>
				</div>

				<div style='width:100%; border:1px solid #dcdcdc; background:#f9f9f9;'>
					<div style='width:185px; float:left; padding:5px; border-right:1px solid #dcdcdc;'> Рекомендованная стоимость пог. метра:</div>
					<div style='width:calc(100% - 187px); float:left; padding:5px;'>$cost</div>
					<div style='clear:both;'></div>
				</div>
			</div>
			";
				
				
				

				
	//СЮДА ФАСАДЫ В ВИДЕ ВЫПАДАЮЩИХ ВКЛАДОК (ТАБЫ) -------------- заморочился
	//Сразу получаем данные из базы, если типов больше 1, выводим табы, иначе только вкладку Все фасады".	
	$sqlDistinctType = "SELECT DISTINCT type FROM gi_facade WHERE kitchen_eng = '$post_slug' ORDER BY FIELD(type, 'Массив дерева','Шпон','Пластик','Акрил', 'Syncron', 'Skin')";
	$resultDistinctType = $wpdb->get_results($sqlDistinctType);
	$type_count = count($resultDistinctType); //Кол-во типов фасадов
	foreach ($resultDistinctType as $rowOneType){
		$oneTypeName = $rowOneType->type;
	}
	//если фасадов у кухни 0 или 1
	if ($type_count <= 1) $oneType = "($oneTypeName)";
	else $oneType="";

	
	
	$b.= "<span style='margin-top:60px; font-size:24px;'>Материал фасада<hr></span>";
	$b1.="<div class='korpus'>";
	$b2.="<style>
		td {min-width:150px; max-width:calc(100% - 150px);}
		table{max-width:100%;}
		.korpus {position:relative;}
		.korpus label {position:relative; top:0;}
		.korpus > div, .korpus > input { display: none; float:left;}
		.korpus label { padding: 10px 20px; border: 1px solid #aaa; line-height: 28px; cursor: pointer; position: relative; bottom: 1px; }
		.korpus input[type='radio']:checked + label {background:#6d6d6d; border: 1px solid #6d6d6d; color: white;}";
		
	//Выборка для таблцы "Все фасады"
	$sqlAll = "SELECT * FROM gi_facade WHERE kitchen_eng = '$post_slug' ORDER BY type, name";
	$resultAll = $wpdb->get_results($sqlAll);
	$facades_count = count($resultAll);
	//Выводит поле, если фасадов меньше/равно разрешенного (24) или типов фасадов всего 1. Иначе не выводит таблицу "Все фасады", что бы не захламлять сайт
	if ($facades_count <=18 and $facades_count>0 or $type_count ==1){
		$b2.=".korpus > input:nth-of-type(1):checked ~ div:nth-of-type(1){display: block;}";
		$b1.="
		<input type='radio' name='tab' id='vk1' checked/><label class='myBtn' for='vk1'>Все фасады $oneType</label>
		<div style='margin-top:30px; width:100%; background:white; margin-bottom:60px; text-align:center;'>
		";	
		foreach ($resultAll as $rowAll){
			$facade_descr = $rowAll->description;
			$facade_name = $rowAll->name;
			$facade_thumb = $rowAll->thumb;
			$facade_image = $rowAll->image;	
			$b1.="<a href='../..$facade_image' data-lightbox='image-4'>
				<div style='display:inline-block; margin:5px 5px 5px 0; overflow:hidden; text-align:center; vertical-align:top;'>
					<div style='width:158px; height:135px; background:url($facade_thumb) no-repeat; background-position:center; background-size:cover; margin-bottom:5px; word-wrap:break-word;'></div>
					<div style='font-size:15px; font-weight:600; width:150px;'>$facade_name</div>
					<div style='width:158px;font-size:12px; line-height:14px; font-style:italic;'>$facade_descr</div>
				</div>
				</a>
			";
		}
		$b1.="
		</div>
		";
		$i=2;
	}
	else {
		 //Перекидываем чек на другой инпут, где id=1 из след. выборки (см. ниже)
		$i=1;
	}
	

		


	
	// если типов фасадов больше 1, то вывести все типы отдельными табами, иначе выводит только вкладку "Все фасады"
	if ($type_count > 1){
		
		foreach ($resultDistinctType as $rowTypes) {
			if ($i==1){$checked="checked";} else $checked = "";
			$facadeType = $rowTypes->type;
			$b2.=".korpus > input:nth-of-type($i):checked ~ div:nth-of-type($i){display: block;}";
			$b1.="<input type='radio' name='tab' id='vk$i' $checked/><label class='myBtn' for='vk$i'>$facadeType</label>
			<div style='margin-top:30px; width:100%; background:white; margin-bottom:60px; text-align:center;'>";
			$sqlFacade = "SELECT * FROM gi_facade WHERE type = '$facadeType' and kitchen_eng = '$post_slug' ORDER BY name";
			$resultFacade = $wpdb->get_results($sqlFacade);
			foreach ($resultFacade as $rowFacade){
				$facade_descr = $rowFacade->description;
				$facade_name = $rowFacade->name;
				$facade_thumb = $rowFacade->thumb;
				$facade_image = $rowFacade->image;
				if($facade_name==''){
					$facade_name = "<span style='color:red;'>Не заполнено</span>";
					$facade_descr = "Не заполнено";
				}
				$ii = $i+10; //Номер лайтбокса +10 на всякий случай
				$b1.="
				<a href='../..$facade_image' data-lightbox='image-$i'>
				<div style='display:inline-block; margin:5px 5px 5px 0; overflow:hidden; text-align:center; vertical-align:top;'>
					<div style='width:158px; height:135px; background:url($facade_thumb) no-repeat; background-position:center; background-size:cover; margin-bottom:5px; word-wrap:break-word;'></div>
					<div style='font-size:15px; font-weight:600; width:150px;'>$facade_name</div>
					<div style='width:158px; font-size:12px; line-height:14px; font-style:italic;'>$facade_descr</div>
				</div>
				</a>
				";
			}
			$b1.="</div>";
			$i++;
		}		
	}

	
	$b2.="</style>";
	$b1.="</div>";
	$b.= $b1 . $b2;
	
	
		//ВЫВОД РУЧЕК
		$c.="<span style='margin-top:60px; font-size:24px;'>Модели ручек<hr></span>";
		$arrHandle = explode(',',$handles);
		$c.="<div style='width:100%; text-align:center;'>";
		foreach($arrHandle as $number => $modelHandle){
			$sqlHandles = "SELECT * FROM gi_handle WHERE model_name='$modelHandle'";
			$resultHandles = $wpdb->get_results($sqlHandles);
			foreach($resultHandles as $rowHandles) {
				$model_name = $rowHandles->model_name;
				$image_href = $rowHandles->image_href;
				$thumb_href = $rowHandles->thumb_href;
				$c.="
				<a href='../..$image_href' data-lightbox='handles_images'>
					<div style='display:inline-block; text-align:center; vertical-align:top;'>
						<div style='width:134px; height:84px; background:url(../..$thumb_href) no-repeat; background-position:center; background-size:cover; margin:5px 5px 5px 0;'></div>
						<b>$model_name</b>
					</div>
				</a>				
				";
			}
		}
		$c.="</div>";
		//ВЫВОД СТОЛЕШНИЦ
		$d.="<span style='margin-top:60px; font-size:24px;'>Модели столешниц<hr></span>";
		$arrTabletops = explode(',',$tabletops);
		$d.="<div style='width:100%; text-align:center;'>";
		$i = 0;
		foreach($arrTabletops as $numberTabletop => $modelTabletop){
			
			$sqlTabletops = "SELECT * FROM gi_tabletop WHERE tabletop_name='$modelTabletop'";
			$resultTabletops = $wpdb->get_results($sqlTabletops);
			foreach($resultTabletops as $rowTabletops) {
				$tabletopname = $rowTabletops->tabletop_name;
				$tabletopmadeby = $rowTabletops->made_by;
				$image_link = $rowTabletops->image_link;
				$thumb_link = $rowTabletops->thumb_link;
	
				$d.="
				<a href='../..$image_link' data-lightbox='tabletops_images'>
					<div style='display:inline-block; text-align:center; vertical-align:top;'>
						<div style='width:153px; height:150px; background:url(../..$thumb_link) no-repeat; background-position:center; background-size:cover; margin:5px 5px 5px 0;'></div>
						<div style='width:153px; word-wrap:break-word; font-size:14px; margin:5px;'><b>$tabletopname</b> ($tabletopmadeby)</div>
					</div>
				</a>			
				";
			}
		}
		$d.="</div>";		
	}
	$a.="</div> 
	<style>
	.col-md-12  {
		max-width:1100px;
	}
	</style>
	";
	
	$d.="<div style='width:100%; text-align: center; margin:80px 0 30px 0;'>
		<button class='myBtn'>Задать вопрос</button>
		<a href='/saloons/'><button class='myBtn'>Где заказать?</button></a>
		<a href='/tables/'><button class='myBtn'>Столы</button></a>
		<a href='/chairs/'><button class='myBtn'>Стулья</button></a>
		<a href='/commode/'><button class='myBtn'>Комоды</button></a>
		
	</div>
	";
	return $a . $e . $b . $c . $d;
}
?>