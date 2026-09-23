<?php
/**
 * Template Name: TEST HEADET

 * @package    bp
 * @author     averta (c) 2014-2018
 * @link       http://averta.net
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>

	<!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-WCDGGVW');</script>
	<!-- End Google Tag Manager -->
	
</head>

<body <?php body_class(); ?>>

<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WCDGGVW"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<div id="page" class="site">
	
	<header id="masthead" class="site-header">

		<div class="sidebar-header sticky-header-sidebar">
			<div class="sticky-header-sidebar sidebar-header-block">
			<?php

				/**
				 * wp_diary_header_categories_lists hook
				 *
				 * @hooked wp_diary_header_categories_lists_content - 10
				 *
				 * @since 1.0.0
				 */
				do_action( 'wp_diary_header_categories_lists' );

				/**
				 * wp_diary_header_author_box hook
				 *
				 * @hooked wp_diary_header_author_box_content - 10
				 *
				 * @since 1.0.0
				 */
				do_action( 'wp_diary_header_author_box' );

			?>
		</div>
		</div><!-- .sidebar-header -->

		<div class="mt-logo-row-wrapper clearfix">
			<div class="mt-container" style='position:relative;'>
				
				<!-------new header-------->
				<div class='myheader'>
					
					<div class='left_myheader' itemscope itemtype="http://schema.org/WPHeader">
						<meta itemprop="headline" content="ГеосИдеал">
						<meta itemprop="description" content="Фабрика кухонь ГеосИдеал">
						
						<div class='logocontainer'>
							<div class='headerlogo'>
								<a href='/'>
									<img src='/wp-content/uploads/2019/11/logo12.png'/>
								</a>
							</div>
							<div class='headerdescr'>Корпоративный сайт фабрики</div>
						</div>
						
						<div class='header_social_container'>
							<div>
								<a rel="nofollow" href='https://www.instagram.com/geosideal/' target='_blank'><div class='insta_icon'></div></a>
								<a rel="nofollow" href='https://www.facebook.com/geosidealby' target='_blank'><div class='fb_icon'></div></a>
								<a rel="nofollow" href='viber://pa?chatURI=geosideal'><div class='viber_icon'></div></a>
								<a rel="nofollow" href='https://www.pinterest.ru/geosideal_official/' target='_blank'><div class='pin_icon' style=''></div></a>
								<a rel="nofollow" href='https://vk.com/gi_kuhni' target='_blank'><div class='vk_icon' style=''></div></a>
								<a rel="nofollow" href='https://www.youtube.com/channel/UCXCl2-MvTOpaY2kqqf8W5RQ' target='_blank'><div class='youtube_icon'></div></a>	
							</div>
						</div>
						
					</div>
					
					<div class='myheader_btns'>
						
						<div class='myheader_cabinet_btn' onclick="window.location.replace('/login-page/');">
							Мой кабинет
						</div>
						
						<div class='myheader_call_btn'>
							
						</div>
					</div>
					
				</div>
				
			</div> <!-- mt-container -->
		</div><!-- .mt-logo-row-wrapper -->
        
          			
        <div class="main-menu-wrapper">
            <div class="menu-toggle"> <i class="fa fa-navicon"></i> <?php esc_html_e( '', 'wp-diary' ); ?> </div>
    		<nav id="site-navigation" class="main-navigation">
    			<div class="mt-container">
    				<?php
    					wp_nav_menu( array(
    						'theme_location' => 'primary_menu',
    						'menu_id'        => 'primary-menu',
    					) );
    				?>
    			</div>
    		</nav><!-- #site-navigation -->
      </div> <!-- main menu wrapper -->

	</header><!-- #masthead -->

	<?php
	
	global $wpdb;
	$sql = "SELECT * FROM gi_homepage";
	$result = $wpdb->get_results($sql);
	foreach($result as $row){
		$modern_image = $row->modern_image;
		$classic_image = $row->classic_image;
		$modern_descr = $row->modern_descr;
		$classic_descr = $row->classic_descr;
		$first_header = $row->first_header;
		$first_content = $row->first_content;
		$mater_image_1 = $row->mater_image_1;
		$mater_image_2 = $row->mater_image_2;
		$mater_image_3 = $row->mater_image_3;
		$mater_image_4 = $row->mater_image_4;
		$mater_image_5 = $row->mater_image_5;
		$mater_header_1 = $row->mater_header_1;
		$mater_header_2 = $row->mater_header_2;
		$mater_header_3 = $row->mater_header_3;
		$mater_header_4 = $row->mater_header_4;
		$mater_header_5 = $row->mater_header_5;
		$mater_descr_1 = $row->mater_descr_1;
		$mater_descr_2 = $row->mater_descr_2;
		$mater_descr_3 = $row->mater_descr_3;
		$mater_descr_4 = $row->mater_descr_4;
		$mater_descr_5 = $row->mater_descr_5;
		$mater_link_1 = $row->mater_link_1;
		$mater_link_2 = $row->mater_link_2;
		$mater_link_3 = $row->mater_link_3;
		$mater_link_4 = $row->mater_link_4;
		$mater_link_5 = $row->mater_link_5;
		$city_image_1 = $row->city_image_1;
		$city_image_2 = $row->city_image_2;
		$city_image_3 = $row->city_image_3;
		$city_image_4 = $row->city_image_4;
		$city_image_5 = $row->city_image_5;
		$city_image_6 = $row->city_image_6;
		$city_name_1 = $row->city_name_1;
		$city_name_2 = $row->city_name_2;
		$city_name_3 = $row->city_name_3;
		$city_name_4 = $row->city_name_4;
		$city_name_5 = $row->city_name_5;
		$city_name_6 = $row->city_name_6;
		$city_link_1 = $row->city_link_1;
		$city_link_2 = $row->city_link_2;
		$city_link_3 = $row->city_link_3;
		$city_link_4 = $row->city_link_4;
		$city_link_5 = $row->city_link_5;
		$city_link_6 = $row->city_link_6;
		$header_2 = $row->header_2;
		$content_2 = $row->content_2;
		$content_2_2 = $row->content_2_2;
		$button_text_content_2 = $row->button_text_content_2;
		$button_link_content_2 = $row->button_link_content_2;
		$utp_image_1 = $row->utp_image_1;
		$utp_image_2 = $row->utp_image_2;
		$utp_image_3 = $row->utp_image_3;
		$utp_header_1 = $row->utp_header_1;
		$utp_header_2 = $row->utp_header_2;
		$utp_header_3 = $row->utp_header_3;
		$utp_descr_1 = $row->utp_descr_1;
		$utp_descr_2 = $row->utp_descr_2;
		$utp_descr_3 = $row->utp_descr_3;
		$utp_link_1 = $row->utp_link_1;
		$utp_link_2 = $row->utp_link_2;
		$utp_link_3 = $row->utp_link_3;
		$header_3 = $row->header_3;
		$content_3 = $row->content_3;
		$button_text_content_3 = $row->button_text_content_3;
		$button_link_content_3 = $row->button_link_content_3;
		$image_content_3 = $row->image_content_3;

	}
		/**
		 * wp_diary_after_header hook
		 *
		 * @since 1.0.0
		 */
		do_action( 'wp_diary_after_header' );


		if( ! is_front_page() ) {
            /**
    		 * wp_diary_innerpage_header hook
    		 *
    		 * @hooked - wp_diary_innerpage_header_start - 5
    		 * @hooked - wp_diary_innerpage_header_title - 10
    		 * @hooked - wp_diary_breadcrumb_content - 15
    		 * @hooked - wp_diary_innerpage_header_end - 20
    		 *
    		 * @since 1.0.0
    		 */
    		do_action( 'wp_diary_innerpage_header' );
        }
	?>
	
		<div class="slider homepageslider" style=''>

		<?php
		//SLIDER
		$header = "";
		$descr = "";
		$btn_text = "";
		$sqlslider = "SELECT * FROM gi_homeslider ORDER BY rank";
		$resultslider = $wpdb->get_results($sqlslider);
		foreach ($resultslider as $rowslider){
			$image = $rowslider->image;
			$header = $rowslider->header;
			$descr = $rowslider->descr;
			$btn_text = $rowslider->btn_text;
			$link = $rowslider->link;
			$color = $rowslider->color;
			
			if($header){
				$header_div = "<div class='sliderHeader' >$header</div>";
			}
			else {$header_div = "";}
			if($descr){
				$descr_div = "<div class='sliderDescription' style='background:$color'>$descr</div><br>";
			}
			else $descr_div = "";
			if($btn_text){
				$btn_text_div = "<div class='sliderBtn'>$btn_text</div>";
			}
			else $btn_text_div = "";
			echo "
			<a href='$link'>
				<div class='slide'>
					<img src='$image' alt='Первый слайд'>
					<div class='slideText'>
						$header_div
						$descr_div
					</div>
					<!--<div class='slideText' style='position:relative; bottom:64px;'>
						$btn_text_div
					</div>-->
				</div>	
			</a>			
			";
		}
		?>
		<a class="prev" onclick="minusSlide()">&#10094;</a>
		<a class="next" onclick="plusSlide()">&#10095;</a>
	</div>	
	
<div id="content" class="site-content">
	<div class="mt-container">	
		<div id="primary" class="content-area">
		
			<a rel="nofollow" href='/kuhni/sovremennie-kuhni/'>
			<div class='catDiv1 homeCatDiv'>
				<div class='rose_overlay'></div>
				<div class='catInfoDiv'>
					<div class='catInfoText'>
						<span class='catInfoHeader'>Модерн</span>
										
					</div>
				</div>
				<img class='homeCatImg' src='<?php echo $modern_image;?>' alt='Современные кухни'/>
			</div>
			</a>
								
			<a rel="nofollow" href='/kuhni/klassicheskie-kuhni/'>
			<div class='catDiv2 homeCatDiv'>
				<div class='rose_overlay'></div>
				<div class='catInfoDiv'>
					<div class='catInfoText'>
						<span class='catInfoHeader'>Классика</span>					
					</div>
				</div>
				<img class='homeCatImg' src='<?php echo $classic_image;?>' alt='Классические кухни'/>
			</div>	
								</a>
			<!--<h1> <?php echo $first_header;?> </h1>-->
			<h1>Фабрика кухонь GeosIdeal</h1>
			<?php //echo $first_content . "<br><br>";?> 
			
			<p>
			Фабрика кухонь ГеосИдеал - хорошо известный производитель мебели для кухни в СНГ. Если вы хотите купить кухню, обратите внимание на продукцию ООО ГеосИдеал! Выбор в нашу пользу определяется многими факторами. Давайте разберемся, что же такое Геос, и в чем заключается Идеал.
			</p>
			
			<div class='numbers_utp_div'>
				<div class='numbers_div'>
					<span class='number_header'>22</span><br>
					модели кухонь
				</div>
				<div class='numbers_utp_hr'>
					<hr>
				</div>
				<div class='numbers_utp_content'>
					Фабрика предлагает <a href='/kuhni/' style='text-decoration:underline;'>более 20 моделей кухонь</a> из массива дерева, натурального шпона, пластика, акрила, ЛДСП, крашеной МДФ, выполненных в разных стилевых направлениях.
					Цена кухни может варьировать в широком диапазоне: от недорогих кухонь для небольших помещений до эксклюзивных гарнитуров из натурального дерева для 
					кухонь-гостиных. Неизменным остается только одно - безупречность исполнения.
				</div>
			</div>
			
			<div class='homeAllMaterials'>
			
				<a rel="nofollow" href='<?php echo $mater_link_1;?>'>
					<div class='homeMaterialDiv homeMaterialDiv1'>
						<div class='homeMaterialAvatar' style='background:url(<?php echo $mater_image_1;?>) no-repeat; background-position:center; background-size:cover;'></div>
						<div class='homeMaterialText'>
							<span class='homeMaterialHeader'><?php echo $mater_header_1;?></span><br>
						</div>
					</div>
				</a>
				
				<a rel="nofollow" href='<?php echo $mater_link_2;?>'>
					<div class='homeMaterialDiv homeMaterialDiv2'>
						<div class='homeMaterialAvatar' style='background:url(<?php echo $mater_image_2;?>) no-repeat; background-position:center; background-size:cover;'></div>
						<div class='homeMaterialText'>
							<span class='homeMaterialHeader'><?php echo $mater_header_2;?></span><br>
						</div>
					</div>
				</a>
				
				<a rel="nofollow" href='<?php echo $mater_link_3;?>'>
					<div class='homeMaterialDiv homeMaterialDiv3'>
						<div class='homeMaterialAvatar' style='background:url(<?php echo $mater_image_3;?>) no-repeat; background-position:center; background-size:cover;'></div>
						<div class='homeMaterialText'>
							<span class='homeMaterialHeader'><?php echo $mater_header_3;?></span><br>						
						</div>
					</div>
				</a>
				<a rel="nofollow" href='<?php echo $mater_link_4;?>'>
					<div class='homeMaterialDiv homeMaterialDiv4'>
						<div class='homeMaterialAvatar' style='background:url(<?php echo $mater_image_4;?>) no-repeat; background-position:center; background-size:cover;'></div>
						<div class='homeMaterialText'>
							<span class='homeMaterialHeader'><?php echo $mater_header_4;?></span><br>							
						</div>
					</div>
				</a>
				
				<a rel="nofollow" href='<?php echo $mater_link_5;?>'>
					<div class='homeMaterialDiv homeMaterialDiv5'>
						<div class='homeMaterialAvatar' style='background:url(<?php echo $mater_image_5;?>) no-repeat; background-position:center; background-size:cover;'></div>
						<div class='homeMaterialText'>
							<span class='homeMaterialHeader'><?php echo $mater_header_5;?></span><br>							
						</div>
					</div>
				</a>
				
			</div>	
			
	</div>	
	</div>
</div>
<div id="content2" class="site-content black-bg" style='background:#1e3350; color:#f7f7f7;'>
	<div class="mt-container">	
		<div id="primary2" class="content-area">
		
			<div class='numbers_utp_div'>
				<div class='numbers_div'>
					<span class='numbers_utp_header'>cемейная<br>фабрика</span>
					
				</div>
				<div class='numbers_utp_hr'>
					<hr>
				</div>
				<div class='numbers_utp_content'>
					Фабрика ГеосИдеал является частным семейным бизнесом, в основе которого лежит любовь к своему делу и трепетное отношение к продукту. Полный цикл производства 
					с контролем на каждом этапе изготовления фасада и корпуса гарантирует соблюдение высоких стандартов качества мебели.
				</div>
			</div>
			<div class='numbers_utp_div'>
				<div class='numbers_div'>
					<span class='number_header'>30</span><br>
					лет на рынке
				</div>
				<div class='numbers_utp_hr'>
					<hr>
				</div>
				<div class='numbers_utp_content'>
					Мебельная фабрика GeosIdeal производит кухни и мебель для гостиных <a href='/kuhnya-po-individualnomu/' style='color:#fff; text-decoration:underline;'>по индивидуальному заказу</a> уже 30 лет. Производство расположено в Беларуси, а 
					дилерская сеть салонов, где можно купить кухню GeosIdeal, охватывает территорию Беларуси, России, Украины и Казахстана.
				</div>
			</div>
			<div class='numbers_utp_div'>
				<div class='numbers_div'>
					<span class='numbers_utp_header'>более</span><br><span class='number_header'> 130</span><br>
					салонов
				</div>
				<div class='numbers_utp_hr'>
					<hr>
				</div>
				<div class='numbers_utp_content'>
					Купить красивую и качественную мебель по индивидуальному проекту можно в одном из более, <a href='/gde-zakazat/minsk/' style='color:#fff; text-decoration:underline;'>чем 130 салонов</a>, расположенных как в торговых центрах, так и обособлено.
				</div>
			</div>		
		
		</div>	
	</div>
</div>

<div style='width:100%; height: 500px; background:#dcdcdc;' id='map'></div>

<?php
//ТУТ ЧЕТ НЕ ТО
$sql = "SELECT * FROM gi_salons WHERE moderate='yes'";
$result = $wpdb->get_results($sql);
foreach ($result as $row){
	$newid = $row -> newid;
	$name = $row->name;
	$country = $row->country;
	$city = $row->city;
	$address = $row->address;
	$phones = $row->phones;
	$latlng = $row->lat_lng;
	$arr = explode("_", $latlng);
	$lat = $arr[0];
	$lng = $arr[1];
	if(!empty($lat) and !empty($lng)){
		$locations .= "[$lat, $lng],";	
	}
	//$locations .= "[$lat, $lng],";
	$marker_ids .= "[$newid],";
	
	//$content .= "['<h4>$name</h4> $address <br> Телефоны: <br> $phones'],";
}
$marker_image = "/wp-content/themes/wp-diary/images/greencircle.png";
$m .= "
	<link rel='stylesheet' href='/wp-includes/leaflet/leaflet.css' />
	<script src='/wp-includes/leaflet/leaflet-src.js'></script>
	<script>
		var tiles = L.tileLayer('https://cartodb-basemaps-{s}.global.ssl.fastly.net/light_all/{z}/{x}/{y}.png', {
			maxZoom: 18,
			attribution: '&copy; <a href=\"https://www.openstreetmap.org/copyright\">OpenStreetMap</a> contributors, Points &copy 2012 LINZ'
		}),
		latlng = L.latLng(54.018462, 31.094590);
		var map = L.map('map', {center: latlng, zoom: 4, layers: [tiles]});

		L.icon = function (options) {
			return new L.Icon(options);
		};

		var myIcon = L.icon({
			iconUrl: '$marker_image',
			iconSize: [18, 18], // size of the icon
		});	
		var addressPoints = [$locations];
		var marker_ids = [$marker_ids];	
		var contents = [$content];	
		for (var i = 0; i < addressPoints.length; i++) {
			var a = addressPoints[i];
			//var title = marker_ids[i];
			var id = marker_ids[i];
			//var content = contents[i][0];
			//L.marker(L.latLng(a[0], a[1]), {title: 'Смотреть', icon:myIcon, markerId: id}).bindPopup(content).addTo(map);
			L.marker(L.latLng(a[0], a[1]), {title: 'Смотреть', icon:myIcon, markerId: id}).addTo(map);
		}
		map.scrollWheelZoom.disable(); 
	</script>
";
echo $m;

?>
</div>
<div id="content3" class="site-content">
	<div class="mt-container">	
	<div id="primary3" class="content-area">
		
			<div class="aux-container aux-fold">
			
				<p class="h2">Что такое кухня и как правильно её купить</p>
				<p>Покупая кухню от производителя, важно учитывать различные факторы, которые определяют качество мебели и стоимость готового изделия. </p>
				
				<div class='allUtp'>
				
					<a rel="nofollow" href='<?php echo $utp_link_1;?>' style='color:#838383;'>
						<div class='utpDiv utpDiv1'>
							<div class='rose_overlay'></div>
							<div class='utpDivImage' style='background:url(<?php echo $utp_image_1;?>) no-repeat; background-size:cover; background-position:center;'></div>
							<div class='utpDivText'>
								<span class='utpHeader'><?php echo $utp_header_1;?></span><br>
								<span class='utpDescr'><?php echo $utp_descr_1;?></span>
							</div>
						</div>
					</a>
						
					<a rel="nofollow" href='<?php echo $utp_link_2;?>' style='color:#838383;'>
						<div class='utpDiv utpDiv2'>
						<div class='rose_overlay'></div>
							<div class='utpDivImage' style='background:url(<?php echo $utp_image_2;?>) no-repeat; background-size:cover; background-position:center;'></div>
							<div class='utpDivText'>
								<span class='utpHeader'><?php echo $utp_header_2;?></span><br>
								<span class='utpDescr'><?php echo $utp_descr_2;?></span>
							</div>
						</div>
					</a>
					
					<a rel="nofollow" href='<?php echo $utp_link_3;?>' style='color:#838383;'>
						<div class='utpDiv utpDiv3'>
						<div class='rose_overlay'></div>
							<div class='utpDivImage' style='background:url(<?php echo $utp_image_3;?>) no-repeat; background-size:cover; background-position:center;'></div>
							<div class='utpDivText'>
								<span class='utpHeader'><?php echo $utp_header_3;?></span><br>
								<span class='utpDescr'><?php echo $utp_descr_3;?></span>
							</div>
						</div>
					</a>

				</div>
				
				<?php //echo $content_2_2;?> 
				<p>
				Больше информации, которую будет полезным изучить перед покупкой кухни, вы найдёте, нажав на кнопку.
				</p>
				<div style='width:100%; text-align:center; margin:30px auto;'>
					<!--<a href='<?php echo $button_link_content_3;?>'>-->
					<a rel="nofollow" href='/information/'>
						<br><br><div class='myBtn myBtnBig' style='width:max-content;'>
							<?php //echo $button_text_content_3;?>
							Важно знать!
						</div>
					</a>
				</div>		
			</div>
	
			</div>
		</div>

			<div class='homeAboutDiv' style=''>
				<div class='homeAboutText'>
					<div class='homeAboutHeader'><p class="h2">Немного о фабрике ГеосИдеал</p></div>
					<div class='homeAboutDescr'>
						<?php //echo $content_3;?>
						<p>Истоки биографии ГеосИдеал нужно искать в далеком <b>1991 году</b>. Это довольно интересная история, повествующая о смелости, упорстве и мечтах..., которую предлагаем почитать <a href='/factory/' style='text-decoration:underline;'>в этой статье</a>. Компания ГеосИдеал прошла путь от небольшого предприятия по изготовлению мебели из массива до современной высокотехнологичной фабрики. Сейчас фабрика представляет собой производство мебели полного цикла и состоит из 8 подразделений.</p>
						<br><br><a rel="nofollow" href='/o-fabrike/'><div class='myBtn' style='width:max-content;'>Читать</div></a>
					</div>
				</div>
				<div class='homeAboutMedia'>
					<!---<iframe class='homeAboutFrame' src="https://www.youtube.com/embed/lHucp7F_l2k?start=74" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					--->
				</div>
			</div>


	<div id="primary4" class="content-area">
		<?php
		while ( have_posts() ) :
			the_post();

			get_template_part( 'template-parts/content', 'page' );

			// If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif;

		endwhile; // End of the loop.
		?>

	</div><!-- #primary -->
<?php echo do_shortcode("[cities_window]"); ?>
<?php echo do_shortcode("[samples_cities_window]"); ?>
</div>


<?php
get_sidebar();
get_footer();
