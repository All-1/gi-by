<?php
/*Plugin Name: bp_salons
Description: Работа с салонами. Вывод информации на страницу и добавление новых. Редактирование салонов.
Version: 1.0
Author: Business Park*/
add_shortcode('salons_print', 'print_salons');
add_shortcode('salon_card', 'salon_card');
add_shortcode('rec_salons_designers', 'rec_salons_designers');

function rec_salons_designers()
{

	global $wpdb;
	global $user;

	$a = "
	<div style='margin-bottom:96px; float:left;'>
		<b>Обратите внимание на наличие эмблемок:</b> <br><br>
		<div>
			<img src='/wp-content/uploads/2019/12/medal.png' style='width:45px;'> - Фирменный фабричный салон <br>
		</div>
		<div style='margin-top:16px;'>
			<img src='/wp-content/uploads/2020/01/medal2.png' style='width:45px;'> - Монобрендовый дилерский салон
		</div>
	</div>
	";

	$sql = "SELECT * FROM gi_salons ORDER BY `rank` DESC LIMIT 5";
	$result = $wpdb->get_results($sql);

	$a .= "<div class='des_salons_cat'>";
	$locations = '';
	$marker_ids = '';
	$content = '';
	$sumlat = 0;
	$sumlng = 0;
	foreach ($result as $row) {

		$newid = $row->newid;
		$name = $row->name;
		$country = $row->country;
		$city = $row->city;
		$address = $row->address;
		$latlng = $row->lat_lng;
		list($lat, $lng) = explode("_", $latlng);
		$phones = $row->phones;
		$mailbox = $row->mailbox;
		$worktime = $row->worktime;
		$samples = $row->samples;
		$avatar = $row->avatar;
		$firm = $row->firm;
		$monobrend = $row->monobrend;
		$rassrochka = $row->rassrochka;
		$moreinfo = $row->moreinfo;
		$oprosnik = $row->oprosnik;

		if ($moreinfo) {
			$moreinfo = "<br><p style='font-size:12px; font-weight:200; line-height:1.7; color:#1e3350; font-style:italic;'>$moreinfo</p>";
		}
		$vk = $row->vk;
		$fb = $row->fb;
		$insta = $row->insta;
		$site = $row->site;
		$user = $row->user;
		$locations .= "[$lat, $lng],";
		$marker_ids .= "[$newid],";

		$sumlat = $sumlat + $lat;
		$sumlng = $sumlng + $lng;

		$wt1 = array('пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс', 'cб');
		$wt2 = array('<b>Пн</b>', '<b>Вт</b>', '<b>Ср</b>', '<b>Чт</b>', '<b>Пт</b>', '<b>Сб</b>', '<b>Вс</b>', '<b>Пн</b>', '<b>Вт</b>', '<b>Ср</b>', '<b>Чт</b>', '<b>Пт</b>', '<b>Сб</b>', '<b>Вс</b>', '<b>Сб</b>');
		$worktime = str_replace($wt1, $wt2, $worktime);

		$marker_image = "/wp-content/uploads/2019/04/mymarker2.png";
		$content .= "['<h4>$name</h4> $address <br> Телефоны: <br> $phones'],";
		if ($firm == 'yes') {
			$medal_title = "Фирменный салон";
			$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2019/12/medal.png) no-repeat; background-size:cover; background-position:center;'></div>";
		}
		if ($monobrend == 'yes' and $firm !== 'yes') {
			$medal_title = "Монобрендовый салон";
			$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2020/01/medal2.png) no-repeat; background-size:cover; background-position:center;'></div>";
			if ($firm == 'yes') {
				$medal_title = "Фирменный салон";
				$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2019/12/medal.png) no-repeat; background-size:cover; background-position:center;'></div>";
			}
		}
		if ($monobrend !== 'yes' and $firm !== 'yes') {
			$medal = "";
		}
		//if($firm == 'yes' or $monobrend == 'yes')$medal = "<div class='medal' title='$medal_title'></div>"; else $medal = '';
		/*<a href='/wp-content/uploads/avatars_of_salons/images/$avatar' class='botAVA'>
				<div style='width:100%; height: 152px; background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
			</a>*/
		$a .= "
			<div class='salonDiv' style='min-height: 311px; cursor:pointer; margin:0; width:100%' onclick='' id='$newid'>
				$medal
				<div class='salon_left_div' style=''>
					<a href='/wp-content/uploads/avatars_of_salons/images/$avatar' data-lightbox='$newid' class='topAVA'>
						<div class='salon_ava_image' style='background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
					</a>
					<div class='salon_left_div_cont' style='cursor:pointer;'>
						<div class='salon_addr_and_name' style=''>
							<p style='font-size:22px; font-weight:600; line-height:1.3; margin-bottom:8px;'>$name</p>
							<p style='font-size:12px; font-weight:200; line-height:1.7;'>$address</p>
						</div>
					</div>
					
					<div class='second_avatar botAVA' style='width:100%; height: 152px; background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
					
				</div>
				<div class='salon_right_div' style=''>
					<div class='salon_worktime'>
						$worktime
						$moreinfo
					</div>
					<div style='margin-top:32px; text-align:left; font-size:12px;'>
						$phones<br>
						$mailbox
						
						<br><br>
						
						<b>Образцы:</b> $samples
							
					</div>
				</div>
				
			</div>
		";
	}
	$a .= "</div>";

	$a .= "<button class='myBtn' id='opencitieswindow' onclick=\"jQuery('#gde_zakazat_bg').css('display','block');\">Выбрать другой салон</button>";

	$a .= "
	<style>
	.des_salons_cat{display:inline-grid; grid-template-columns: repeat(2, 1fr); grid-gap:32px; width:100%; margin-bottom:72px;}
	
	@media screen and (max-width:1100px){
		.salonDiv{min-width:auto;}
		.des_salons_cat{grid-gap:16px;}
	}
	@media screen and (max-width:1000px){
		.salonDiv{width:100%; margin:24px 0 !important;}
		.des_salons_cat{display:block;}
		.salon_addr_and_name{padding:32px 16px 16px 16px;}
		.salon_right_div{padding:0 16px; margin-bottom:32px;}
		.salon_addr_and_name{}
	}
	</style>
	";


	return $a;
}

function salon_card($atts)
{
	//параметр к шорткоду (Покупателям, Новости)
	extract(shortcode_atts(array(
		'id' => ''
	), $atts));

	global $wpdb;
	$sql = "SELECT * FROM gi_salons WHERE newid='$id'";
	$result = $wpdb->get_results($sql);

	$a = "<div style='width:100%;'>";
	foreach ($result as $row) {
		$name = $row->name;
		$city = $row->city;
		$address = $row->address;
		$latlng = $row->lat_lng;
		$phones = $row->phones;
		$mailbox = $row->mailbox;
		$worktime = $row->worktime;
		$samples = $row->samples;
		$avatar = $row->avatar;
		$firm = $row->firm;
		$monobrend = $row->monobrend;
		$rassrochka = $row->rassrochka;
		$moreinfo = $row->moreinfo;
		$vk = $row->vk;
		$fb = $row->fb;
		$insta = $row->insta;
		$site = $row->site;

		list($lat, $lng) = explode("_", $latlng);

		if ($firm == 'yes') {
			$emblems .= "
			<div style='margin:32px 0;'>
				<div class='emblem' style='width:50px; height:50px; border-radius:50%; display:inline-block; vertical-align:top; background:red;'></div>
				<div style='display:inline-block; margin-left:32px; line-height:50px; vertical-align:top; font-size:24px; font-weight:600;'>- Фирменный салон</div>
			</div>
			";
		} elseif ($firm !== 'yes' and $monobrend == 'yes') {
			$emblems .= "
			<div style='margin:32px 0;'>
				<div class='emblem' style='width:50px; height:50px; border-radius:50%; display:inline-block; vertical-align:top; background:red;'></div>
				<div style='display:inline-block; margin-left:32px; line-height:50px; vertical-align:top; font-size:24px; font-weight:600;'>- Монобрендовый салон</div>
			</div>
			";
		} else
			continue;


		if ($rassrochka == 'yes') {
			$emblems .= "
			<div style='margin:32px 0;'>
				<div class='emblem' style='width:50px; height:50px; border-radius:50%; display:inline-block; vertical-align:top; background:red;'></div>
				<div style='display:inline-block; margin-left:32px; line-height:50px; vertical-align:top; font-size:24px; font-weight:600;'>- Есть рассрочка</div>
			</div>
			";
		}

		if (!isset($vk) and !isset($fb) and !isset($insta) and !isset($site)) {
			$socials = "Не представлен :(";
		} else {
			$socials = "<div>";
			if (isset($vk)) {
				$socials .= "
				<div style='display:inline-block; vertical-align:top; margin:0 8px;'>
					VK
				</div>
				";
			}
			if (isset($fb)) {
				$socials .= "
				<div style='display:inline-block; vertical-align:top; margin:0 8px;'>
					FB
				</div>
				";
			}
			if (isset($insta)) {
				$socials .= "
				<div style='display:inline-block; vertical-align:top; margin:0 8px;'>
					INSTA
				</div>
				";
			}
			if (isset($site)) {
				$socials .= "
				<div style='display:inline-block; vertical-align:top; margin:0 8px;'>
					SITE
				</div>
				";
			}
			$socials .= "</div>";
		}

		$a .= "<h1>Салон $name</h1>";
		$a .= "
		<div class='somesalon_map_and_ava_grid' style='width:100%; margin-bottom:32px; display:grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: max-content; gap: 32px;'>
			<div class='somesalon_ava_div'>
				<img id='img' src='/wp-content/uploads/avatars_of_salons/images/$avatar' style='width:100%; margin-bottom:32px;'/>
				<div class='info1'>
					<p> $city, $address </p>
					<p> $phones </p>
					<!--<p> $mailbox </p>-->
				</div>
			</div>
			<div class='somesalon_map_div' style='margin-right'>
				<iframe id='map' width='100%' height='300px' style='margin-bottom:32px;' src='https://maps.google.com/maps?q=$lat,$lng&hl=ru&z=14&amp;output=embed'></iframe>
				<p> $worktime </p>
				<p> Образцы в наличии: $samples </p>
			</div>
		</div>
		
		$emblems
		
		<div id='somesalon_social_btns' style='width:100vw; min-height:100px; background:#dea993; padding:64px 32px; margin:64px 0; text-align:center; color:#fff;'>
			<h2 style='color:#fff;'>Салон в сети интернет</h2>
			$socials
		</div>
		";

	}
	$a .= "</div>";

	$a .= "
	<script>
		var imageheight = jQuery('#img').height();
		var fw = jQuery('body').width();
		var cw = jQuery('.entry-content').width();
		var social_left_margin = (fw - cw) / 2;
		jQuery('#somesalon_social_btns').css('margin-left', '-'+social_left_margin+'px');
		
		jQuery('#map').css('height', imageheight);
		
	</script>
	";
	return $a;
}

function print_salons($atts)
{

	//параметр к шорткоду (Покупателям, Новости)
	extract(shortcode_atts(array(
		'city' => ''
	), $atts));
	if ($city != "" and $city != "all") {
		$shortcode_att = "AND city='$city'";
	} else {
		$shortcode_att = "";
	}

	global $wpdb;
	$plugins_url = plugins_url();

	$a = "
	<h1 style='margin-bottom:64px;'>Кухни в городе $city</h1>
	<div class='mapDiv' id='mapDiv'>
		<div class='closebutton' id='closebutton'>
			<img src='$plugins_url/bp_salons/images/close.png' style='margin-right:5px;' />
		</div>
		<div id='ajaxcontent' style='width:100%; height:100%; position:absolute; bottom:0; padding:32px; overflow-y:auto;'>
		
		</div>
	</div>
	
	<div class='allSalonsDiv' style='width:100%; padding:0; margin:0;'>
";

	//ПОЛУЧАЕМ СТРАНУ

	#переводим страну на eng, благо стран мало :)
/*
switch ($ip_country){
	case "Belarus": $wherecountry = "AND country = 'Беларусь'"; break;
	case "Russia": $wherecountry = "AND country = 'Россия'"; break;
	case "Russian Federation": $wherecountry = "AND country = 'Россия'"; break;
	case "Ukraine": $wherecountry = "AND country = 'Украина'"; break;
	case "Kazakhstan": $wherecountry = "AND country = 'Казахстан'"; break;
	default: $wherecountry = "AND country = 'Беларусь'"; break;
}*/


	$sql = "SELECT * FROM gi_salons WHERE moderate='yes' $shortcode_att ORDER BY `rank` DESC";
	$result = $wpdb->get_results($sql);
	$countResult = count($result) > 0 ? count($result) : 1;
	$locations = '';
	$marker_ids = '';
	$content = '';
	$marker_image = "/wp-content/uploads/2019/04/mymarker2.png";
	$sumlat = 0;
	$sumlng = 0;
	foreach ($result as $row) {
		$newid = $row->newid;
		$name = $row->name;
		$country = $row->country;
		$city = $row->city;
		$address = $row->address;
		$latlng = $row->lat_lng;
		list($lat, $lng) = explode("_", $latlng);
		$lat = floatval($lat);
		$lng = floatval($lng);
		$phones = $row->phones;
		$mailbox = $row->mailbox;
		$worktime = $row->worktime;
		$samples = $row->samples;
		$avatar = $row->avatar;
		$firm = $row->firm;
		$monobrend = $row->monobrend;
		$rassrochka = $row->rassrochka;
		$moreinfo = $row->moreinfo;
		$oprosnik = $row->oprosnik;

		if ($mailbox) {
			$mailbox_div = "<div class='mailbox_div'><a href='mailto:$mailbox'>Напишите нам</a></div>";
		} else {
			$mailbox_div = "";
		}

		if ($moreinfo) {
			$moreinfo = "<br><p style='font-size:12px; font-weight:200; line-height:1.7; color:#1e3350; font-style:italic;'>$moreinfo</p>";
		}
		$vk = $row->vk;
		$fb = $row->fb;
		$insta = $row->insta;
		$site = $row->site;
		$user = $row->user;
		$locations .= "[$lat, $lng],";
		$marker_ids .= "[$newid],";

		$sumlat = $sumlat + $lat;
		$sumlng = $sumlng + $lng;

		$wt1 = array('пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс', 'Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс', 'cб');
		$wt2 = array('<b>Пн</b>', '<b>Вт</b>', '<b>Ср</b>', '<b>Чт</b>', '<b>Пт</b>', '<b>Сб</b>', '<b>Вс</b>', '<b>Пн</b>', '<b>Вт</b>', '<b>Ср</b>', '<b>Чт</b>', '<b>Пт</b>', '<b>Сб</b>', '<b>Вс</b>', '<b>Сб</b>');
		$worktime = str_replace($wt1, $wt2, $worktime);

		$marker_image = "/wp-content/uploads/2019/04/mymarker2.png";
		$content .= "['<h4>$name</h4> $address <br> Phones: <br> $phones'],";
		if ($firm == 'yes') {
			$medal_title = "Фирменный салон";
			$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2019/12/medal.png) no-repeat; background-size:cover; background-position:center;'></div>";
		}
		if ($monobrend == 'yes' and $firm !== 'yes') {
			$medal_title = "Монобрендовый салон";
			$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2020/01/medal2.png) no-repeat; background-size:cover; background-position:center;'></div>";
			if ($firm == 'yes') {
				$medal_title = "Фирменный салон";
				$medal = "<div class='medal' title='$medal_title' style='background:url(/wp-content/uploads/2019/12/medal.png) no-repeat; background-size:cover; background-position:center;'></div>";
			}
		}
		if ($monobrend !== 'yes' and $firm !== 'yes') {
			$medal = "";
		}
		//if($firm == 'yes' or $monobrend == 'yes')$medal = "<div class='medal' title='$medal_title'></div>"; else $medal = '';
		/*<a href='/wp-content/uploads/avatars_of_salons/images/$avatar' class='botAVA'>
			 <div style='width:100%; height: 152px; background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
		 </a>*/
		$a .= "
		<div class='salonDiv' style='min-height: 311px; cursor:pointer;' onclick='showwindow(this);showmap();' id='$newid'>
			$medal
			<!--$mailbox_div-->
			<div class='salon_left_div' style=''>
				<a href='/wp-content/uploads/avatars_of_salons/images/$avatar' data-lightbox='$newid' class='topAVA'>
					<div class='salon_ava_image' style='background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
				</a>
				<div class='salon_left_div_cont' style='cursor:pointer;'>
					<div class='salon_addr_and_name' style=''>
						<p style='font-size:22px; font-weight:600; line-height:1.3; margin-bottom:8px;'>$name</p>
						<p style='font-size:12px; font-weight:200; line-height:1.7;'>$address</p>
					</div>
				</div>
				
				<div class='second_avatar botAVA' style='width:100%; height: 152px; background:url(/wp-content/uploads/avatars_of_salons/images/$avatar) no-repeat; background-size:cover; background-position:center;'></div>
				
			</div>
			<div class='salon_right_div' style=''>
				<div class='salon_worktime'>
					$worktime
					$moreinfo
				</div>
				<div style='margin-top:32px; text-align:left; font-size:12px;'>
					<a href='tel:+375 29 374 17 06'>$phones</a><br>
					<!--$mailbox-->
					
					<br><br>
					
					<b>Образцы:</b> $samples
						
				</div>
			</div>
			
		</div>
	";
		/*
		 $a.="
			 <div class='salonDiv'>
				 
				 <a href='/wp-content/uploads/avatars_of_salons/images/$avatar' data-lightbox='$newid'>
					 <div class='salonAva' style='background:url(/wp-content/uploads/avatars_of_salons/thumbs/$avatar) no-repeat; background-size:contain; background-position:center;'>
					 </div>
				 </a>		

				 <div class='salonContent' style=''>
					 <div class='contentAlignDiv'>
						 <div style='padding: 16px 32px; margin: -16px 0 16px -16px; width: calc(100% + 32px); background:#dea993; line-height:1.5;'>
							 <span style='font-size:22px; font-weight:600;'>$name</span><br>
							 <span style='font-weight:600; font-size:12px;'>$address</span>
						 </div>
						 <div style='clear:both;'></div>
							 <span style='font-size:12px; line-height:1.5; display:block; margin-left:16px;'>
								 <b>$worktime</b><br><br>
								 $phones<br>
								 $mailbox<br>
							 </span>
						 <div style='clear:both;'></div>
					 </div>
				 </div>
				 <button onclick='getlatlng(this)' value='$latlng' style='border:none; outline:0px; position:absolute; right:0; bottom:0; padding:0;'>
					 <div id='openmap' class='openmap' onclick='showmap()' style='position:relative; padding:10px 20px; cursor:pointer; z-index:10;text-align:right; background: #1e3350; color:#fff;'>
						 На карте
					 </div>
				 </button>
			 </div>

			 
		 
		 ";
		 */
	}
	$content = str_replace("»", "", $content);
	$content = str_replace("«", "", $content);
	$content = str_replace('"', '', $content);
	$content = str_replace("'", '"', $content);
	$content = str_replace("\n", '<br>', $content);
	$content = str_replace("\r", '<br>', $content);

	$a .= "
	</div>
	";

	$a .= "
	<style>
	.salonDiv:hover{ background:#dea993;}
	</style>
	<script>
		var mapdiv = document.getElementById('mapDiv');
		var openmap = document.getElementById('openmap');
		var closebutton = document.getElementById('closebutton');
		
		var info = document.getElementById('info');
		/*var openinfo = document.getElementById('openinfo');*/
		var close = document.getElementById('close');
		
	
		function showmap(){
			mapdiv.style.right='0';
		}
		closebutton.onclick = function(){
			mapdiv.style.right='-104%';
		}
	
		
		//получаем значение latlng, которое прописано в кнопках, которые скрыты 
		function showwindow(obj){
			var thisid = obj.id;
			
			//ГОРЕ AJAX. Пробую впихнуть внутрь и вроде работает
					
			function show(){  
			/*
				jQuery.ajax({  
					url: '/../wp-content/plugins/bp_salons/ajax.php',  
					cache: false,  
					success: function(html){  
						jQuery('#ajaxcontent').html(html);  
					}  
				});
			*/
				jQuery.ajax({
					url: '/wp-content/plugins/bp_salons/ajax.php',
					type: 'POST',
					data: {thisid:thisid},
					success: function(data){ 
						jQuery('#ajaxcontent').html(data);
					} 
				});
			}  
			  
			jQuery(document).ready(function(){ 
				show();  
			}); 
			
		}
		

	</script>
	
	";

	//БЛОК С КАРТОЙ	
	$z = "
	<a href='#fullMapDiv' id='openMapBtn'><button class='myBtn' style='margin:20px 0; padding:14px 20px;'>Все салоны на карте</button> </a>
	<button class='myBtn' id='opencitieswindow' style='padding:14px 40px; margin-left:32px;'>Выбрать город</button>
	";
	$z .= "
	<div id='fullMapDiv' style='width:100%; background:yellow; overflow-y:hidden; height:auto; transition:0.7s ease; margin-bottom:30px;'>
		<div style='width:100%; height:550px; background:#f5f5f5; position:relative;'>
			<div id='map' style='width:100%; height:100%; position:relative;'>
			
			</div>
			<div id='fullMapCloseArrow' style='z-index:999; position:absolute; box-shadow:0px 0px 4px rgba(1,1,1,0.4); width:120px; height:40px; bottom:0; left:calc(50% - 60px); background:white; cursor:pointer; text-align:center; line-height:40px; font-size:32px;'>
				<i class='fa fa-icon fa-angle-up' style='font-size:42px;'></i>
			</div>
		</div>
	
	</div><br><br>
	";
	if (isset($sumlat)) {
		$centerlat = $sumlat / $countResult;
	}
	if (isset($sumlng)) {
		$centerlng = $sumlng / $countResult;
	}


	$z .= "
<link rel='stylesheet' href='/wp-includes/leaflet/leaflet.css' />
<script src='/wp-includes/leaflet/leaflet-src.js'></script>


    <script>

	var gde_zakazat_close = document.getElementById('gde_zakazat_close');
	gde_zakazat_close.onclick = function(){
		gde_zakazat_bg.style.display='none';
	}
	var opencitieswindow = document.getElementById('opencitieswindow');
	opencitieswindow.onclick = function(){
		gde_zakazat_bg.style.display='block';
	}	
	var openMapBtn = document.getElementById('openMapBtn');
	var fullMapDiv = document.getElementById('fullMapDiv');
	var fullMapCloseArrow = document.getElementById('fullMapCloseArrow');
 
	jQuery(document).ready(function() {
	  jQuery('#openMapBtn').click(function() {
		var elementClick = jQuery(this).attr('href');
		var destination = jQuery(elementClick).offset().top;
		jQuery('html:not(:animated),body:not(:animated)').animate({
		  scrollTop: destination - 55
		}, 700);
		jQuery('#fullMapDiv').css('height', '550px');

		return false;
	  });
	});

	
	fullMapCloseArrow.onclick = function (){
		fullMapDiv.style.height='0px';
	}
	var tiles = L.tileLayer('https://a.tile.openstreetmap.org/{z}/{x}/{y}.png', {
		maxZoom: 18,
		attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
	}),
	";
	if (isset($centerlat) && isset($centerlng)) {
		$z .= "latlng = L.latLng($centerlat, $centerlng);";
	}
	$z .= "
	var map = L.map('map', {center: latlng, zoom: 10, layers: [tiles]});

	L.icon = function (options) {
		return new L.Icon(options);
	};

	var myIcon = L.icon({
		iconUrl: '$marker_image',
		iconSize: [30, 39], // size of the icon
	});
	
	var addressPoints = [$locations];
	var marker_ids = [$marker_ids];	
	// разделил перем. чтоб не грузило DOM var contents = [$ content];	
	var markers = {};
	newMarker = L.Marker.extend({
		options: {
			markerId: '',
			zoom: '',
			myLatlng: ''
		}
	});

	for (var i = 0; i < addressPoints.length; i++) {
		
		var a = addressPoints[i];
		//var title = marker_ids[i];
		var id = marker_ids[i];
		//var content = contents[i][0];
		var marker = new newMarker(L.latLng(a[0], a[1]), {title: 'Смотреть', icon:myIcon, markerId: id}).on('click', function(e){
			var myid = this.options.markerId;
			markerclick(myid);
		}).addTo(map);
		
	}
	function markerclick(id){
		jQuery.ajax({
			url: '/wp-content/plugins/bp_salons/ajax.php',
			type: 'POST',
			data: {thisid:id[0]},
			success: function(data){ 
				showmap();
				jQuery('#ajaxcontent').html(data);
			} 
		});	
	}
	</script>

	";

	return $z . $a;
}
?>