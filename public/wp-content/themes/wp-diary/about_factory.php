<?php
/**
 * Template Name: Про фабрику

 * @package    bp
 * @author     tushko (c) 2014-2020
 * @link       none
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
<div id="content" class="site-content" style='font-size:16px;'>
	
	<div class='about_big_bg_image' style='width:100%; height: 100vh; margin-bottom:64px; background:url(/wp-content/uploads/2020/01/3-1.jpg) no-repeat; background-size:cover;'>
		
		<div class="mt-container">	
	
			<div id="primary" class="content-area">
				
				<div style='display:inline-block; width:49%;'>
					<h1 style='margin-top:64px; color:#fff; font-size:48px;'>История<br> фабрики<br> ГеосИдеал</h1>
				</div>
				
				<div class='about_head_slide_right_div' style='color:#fff; display:inline-block; width:49%; text-align:right;'>
					<span style='text-align:left;'>
						Летней ночью в очередной экспедиции<br>
						романтичные геологи, которые, тем не менее, привыкли реально смотреть на жизнь,  думали о том, <br>
						что делать дальше - шёл 91-ый год..........
					</span>
				</div>
			</div>
			
		</div>
	</div>
	
	<div class="mt-container">	
	
	<div id="primary" class="content-area">
	
		<main id="main" class="site-main">		
	
		<div class='about_right_text' style='vertical-align:middle;'>
		1991
		</div>
		
		<div class='about_left_text' style='vertical-align:middle;'>
		<i>
			<span style='text-align:left;'>
						Летней ночью в очередной экспедиции<br>
						романтичные геологи, которые, тем не менее, привыкли реально смотреть на жизнь,  думали о том, 
						что делать дальше: шёл 91-ый год...
					</span>
		<p>— имеет смысл заняться торговлей? – нет, не лежит душа! <br>
		— хочется делать что-то полезное людям<br>
		— добротная мебель — сладкая мечта советского человека<br>
		— и чтобы можно было гордиться!<br><br></p>
		</i>
		<p>Много мыслей, сомнений — и под июльским звездным небом родилась
		мечта, воплощенная в компании «ГеосИдеал». Название появилось от
		греческого «geo» - земля, и связано с «Центральной геофизической
		экспедицией», где работали на тот момент начинающие бизнесмены.
		Эта связь неразрывна и сейчас, 30 лет спустя.
		</p>
		<p>Реализация мечты началась с малого предприятия «Геос» — небольшого производства, выполнявшего разовые заказы на 
		изготовление мебели. Возглавил его Владимир Иванович Михайленко.</p>
		</div>
		
		<div style='clear:both'></div>
		
		<div class='bigtext'>
		<p>Бывшие романтики-геологи стали дизайнерами, конструкторами, технологами, столярами и даже установщиками.</p>
		</div>
		
		<div class='about_left_text' style='vertical-align:middle;'>
			<p>
			Приходилось учиться работать с самым капризным материалом – древесиной – уникальным и невероятно богатым. 
			Только мебель из массива получалась по-настоящему душевной, только на кухне из дерева были пироги вкуснее, а 
			беседы приятнее и роднее, как в молодости у костра.
			</p>
		</div>
		
		<div class='firstimage91' style=''>
			<div style='position:absolute; bottom:-32px; color:#8a8a8a; font-size:12px;'>Отцы-основатели-геологи-романтики</div>
		</div>
		<div class='secondimage91' style=''>
			<div style='position:absolute; bottom:-48px; color:#8a8a8a; font-size:12px;'>Когда-то генеральный директор сам устанавливал кухни</div>
		</div>
		
		
		
		<div class='about_right_text' style='vertical-align:top;'>
		1995
		</div>
		<div class='about_left_text' style='vertical-align:top;'>
			<p>Фабрика стала активно принимать участие в специализированных выставках, первые шаги были скромными, 
			однако исторически важными: именно благодаря выставочной деятельности были заключены первые внешнеторговые контракты.</p>
			<p><b>1995 год</b> – старт работы на рынке Российской Федерации. Представительства компании в Москве, 
			Санкт-Петербурге и Воронеже организуют поставки мебели во все регионы России. В 2013 году открыто 
			представительство компании в Украине.
			</p>
		</div>

		<div style='clear:both'></div>
		
		
		<div class='image50proc' style='margin:64px 32px 64px 0; background:url(/wp-content/uploads/2020/01/6.png) no-repeat; background-size:cover; background-position:center;'>
		</div>
		<div class='image50proc'  style='margin:64px 0px 64px 0; background:url(/wp-content/uploads/2020/01/5.png) no-repeat; background-size:cover; background-position:center;'>
		</div>		

		<div class='about_left_text' style='vertical-align:top;'>
			<p>За 5 лет предприятие «Геос» стало специализироваться исключительно на изготовлении кухонной мебели из 
			массива. Многие операции производились вручную, однако уже тогда стали налаживаться связи с иностранными 
			поставщиками оборудования.</p>
		</div>
		<div style='clear:both'></div>
		
		
		<div class='bigtext'>
		<p>Произошли изменения в составе учредителей: большинство выбрало более простой и быстрый способ заработка. Остался самый упорный 
		и настойчивый – Владимир Михайленко. Человек, который не изменил своей мечте.</p>
		</div>

		
		<div class='about_right_text' style='vertical-align:bottom;'>
		1997
		</div>
		<div class='about_left_text' style='vertical-align:bottom;'>
			<p>С этого момента компания превращается в семейный бизнес, новый для Беларуси, но очень популярный формат в Италии, 
			где мебельные фабрики существуют десятилетиями, а руководство компаниями передается от поколения к поколению.</p>
			<p>В <b>1997 году</b> малое предприятие выросло до уровня фабрики. Высокие требования к качеству продукции стали определяющими 
			в названии компании – оно было дополнено словом <b>Ideal</b>.</p>
		</div>

		<div style='clear:both'></div>
		
		<div class='norma_image' style=''>
			<img src='/wp-content/uploads/2020/01/8.png' alt="Кухни" style='width:100%;'/>
		</div>
		
		<div class='norma_image_descr' style=''>
		Самая первая модель - Норма, прародительница всех моделей фабрики.
		</div>	
		
		
		</main><!-- #main -->
	</div><!-- #primary -->
	</div>



	<div class='about_big_bg_image wood_big_image' style='width:100%; height: 100vh; min-height:500px; margin-bottom:128px; background:url(/wp-content/uploads/2020/01/4-1.jpg) no-repeat; background-size:cover; background-position:bottom;'>
		
		<div class="mt-container">	
	
			<div id="primary" class="content-area">
				<div class='about2011' style=''>
				
					<div class='about2011_number about_right_text' style='vertical-align:bottom;'>
					2011
					</div>
					<div class='about_left_text' style='vertical-align:bottom;'>
						<p>Для снижения зависимости фабрики от поставщиков древесины создан филиал компании, занимающийся лесозаготовкой и 
						производством пиломатериалов. Приобретение современных сушильных камер позволило улучшить качество элементов фасада.</p>
					</div>
					
					<div style='clear:both'></div>
				</div>
				
			</div>
			
		</div>
	</div>

	<div class="mt-container">	
	
	<div id="primary" class="content-area">
	
		<main id="main" class="site-main">
					
			<div class='about_right_text' style='vertical-align:top;'>
				2012
			</div>
			<div class='about_left_text' style='vertical-align:top;'>
				<p>Параллельно стартовал глобальный проект – строительство единого производственного центра фабрики «ГеосИдеал» в 
				Смолевическом районе. Мы всегда возвращаемся к истокам. Вот и семейный бизнес продолжился в районе, где жили 
				прадеды. Эта земля была отобрана большевиками, а сейчас через 100 лет началось ее возрождение. Здесь будут работать 190 
				человек, здесь будет создаваться удивительная душевная мебель, здесь будут жить традиции.</p>
			</div>
			
			<div style='clear:both'></div>
			
			<div class='image50proc' style='display:inline-block; width: calc(50% - 19px); position:relative; height:350px; margin:128px 32px 128px 0; background:url(/wp-content/uploads/2020/01/1-1.jpg) no-repeat; background-size:cover; background-position:center;'>
			</div>
			<div class='image50proc' style='display:inline-block; width: calc(50% - 19px); position:relative; height:350px; margin:128px 0px 128px 0; background:url(/wp-content/uploads/2020/01/2-1.jpg) no-repeat; background-size:cover; background-position:center;'>
			</div>	
		
					
			<div class='about_right_text' style='vertical-align:top;'>
				2019
			</div>
			<div class='about_left_text' style='vertical-align:top;'>
				<p>Построены новые цеха, спроектированные с учётом требований высокотехнологичного производства, создана 
				инфраструктура, закуплено новое современное оборудование, организован логистический терминал, проектируется большой 
				шоу-рум и обучающий центр..</p>
			</div>
			
			<div style='clear:both'></div>
			
			
			<div class='bigtext' style=''>
			<p>Главная цель — объединение всех производственных площадок в единый комплекс — достигнута. «Геос» готов расправить крылья!</p>
			</div>
			
		</main>
	
	</div>
	
	</div>

	<div class='about_big_bg_image factory_big_image' style='width:100%; height: 100vh; min-height:500px; margin-bottom:-80px; background:url(/wp-content/uploads/2020/01/9-1.jpg) no-repeat; background-size:cover; background-position:top;'>
		
		<div class="mt-container">	
	
			<div id="primary" class="content-area">
				<div class='newmargin32' style='margin-top:64px;'>
					<div class='about_left_text' style='font-weight:600; color:#484848;'>
						<p>Сегодня фабрика «ГеосИдеал» — это растущая компания, продукция которой представлена в 130 салонах Беларуси, 
						России, Украины и Казахстана. В компании работают более 180 специалистов, искренне влюбленных в своё дело.</p>
						<p>А принципы, провозглашённые в ту давнюю летнюю ночь, и сейчас лежат в основе работы фабрики.</p>
					</div>
				</div>
				
			</div>
			
		</div>
	</div>

</div>

<style>
.no-bg-img{display:none;}
.about_left_text{width:66%; display:inline-block; padding-right:32px; line-height:1.8; float:left;}
.about_right_text {width:33%; display:inline-block; text-align:right; font-size:16rem; color:#dea993; font-weight:600; line-height:0.8; float:right;}
.firstimage91{display:inline-block; width: 45%; position:relative; height:350px; margin:64px 32px 128px 0; background:url(/wp-content/uploads/2020/01/10.png) no-repeat; background-size:cover; background-position:center;}
.secondimage91{display:inline-block; width: 25%; position:relative; height:350px; margin:64px 0px 128px 0; background:url(/wp-content/uploads/2020/01/7.png) no-repeat; background-size:cover; background-position:center;}
.image50proc{display:inline-block; width: calc(50% - 19px); position:relative; height:350px;}
.norma_image{width:calc(66% - 32px); display:inline-block; vertical-align:bottom; margin:64px 32px 64px 0;}
.norma_image_descr{width:33%; display:inline-block; vertical-align:bottom; text-align:left; color:#8a8a8a; margin-bottom:64px;}
.about2011{margin-top:64px;}
.bigtext{font-size:42px; font-weight:900; margin:64px 0; line-height:1.3;}
@media screen and (max-width:1000px){
	.about_right_text{font-size: 11rem;}
	.firstimage91{width:calc(66% - 36px);}
	.secondimage91{width:33%;}
}
@media screen and (max-width:800px){
	.about_left_text, .about_right_text {width:100%;}
	.about_right_text{margin:64px 0; text-align:left;}
	.about_head_slide_right_div{display:none !important;}
}
@media screen and (max-width:600px){
	.firstimage91, .secondimage91 {width:100%; margin:32px 0 64px 0;}
	.image50proc, .norma_image {width:100% !important; margin:32px 0 !important;}
	.secondimage91{height:450px;}
	.norma_image_descr {width:100%; margin-bottom:0 !important;}
	.about2011{margin-top:0 !important; text-shadow:2px 2px 9px rgba(255,255,255,1);}
	.about2011_number{margin: 0 0 32px 0;}
	.wood_big_image{margin-bottom:32px !important; background-position:bottom !important; height:110vh !important;}
	.factory_big_image{background-position:bottom !important; height:140vh !important;}
	.newmargin32{margin-top:32px !important;}
	.bigtext{font-size:28px;}
	
}
</style>

<?php
	while ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/content', 'page' );
		// If comments are open or we have at least one comment, load up the comment template.
	endwhile; // End of the loop.
?>
		
		
		
<div id="content" class="site-content">
	<div class="mt-container">	
	
<?php echo do_shortcode("[cities_window]"); ?>

<?php
get_sidebar();
get_footer();
