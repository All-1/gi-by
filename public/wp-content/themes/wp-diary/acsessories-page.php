<?php
/**
 * Template Name: Фурнитура

 * @package    bp
 * @author     Artem (c) 2019
 * @link       none
 */
get_header();
?>

<body <?php body_class(); ?>>

	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WCDGGVW" height="0" width="0"
			style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<div id="page" class="site">

		<?php require_once (dirname(__FILE__) . '/header-for-webpage.php'); ?>
		<div id="content" class="site-content">
			<div class="mt-container">
				<div id="primary" class="content-area">
					<main id="main" class="site-main">
						<div class='acsess_preview'>
							<h1 style='margin-bottom:32px !important;'>Фурнитура и наполнение кухонь ГеосИдеал</h1>
							<p>
								Эргономика, долговечность и даже красота любой кухни в большой степени определяется качеством и
								функциональностью фурнитуры, которой укомплектована
								мебель. Фабрика ГеосИдеал для оснащения всех подвижных элементов своих кухонных гарнитуров выбрала
								фурнитуру ведущих мировых производителей. Это означает лишь то, что
								кухни ГеосИдеал будут служить Вам долгие годы (эта цифра измеряется десятками лет!)...
							</p>
						</div>
						<div class="kitchenmap">
							<img src="/wp-content/uploads/2019/11/kitchenmap.jpg" alt="Аксессуары" />
							<div class="petli_icon" data-id='petli' data-cat='petli' data-header='Петли' onclick='showcontent(this);'>
							</div>
							<div class="sushki_icon" data-id='sushki' data-cat='napolnenie' data-header='Наполнение'
								onclick='showcontent(this);'></div>
							<div class="ugl_shkaf_icon" data-id='uglovoi' data-cat='petli' data-header='Петли'
								onclick='showcontent(this);'></div>
							<div class="podiom_icon" data-id='podjemnie' data-cat='podjemnie' data-header='Подъемные механизмы'
								onclick='showcontent(this);'></div>
							<div class="kolonni_icon" data-id='kolonni' data-cat='napolnenie' data-header='Наполнение'
								onclick='showcontent(this);'></div>
							<div class="kargo_icon" data-id='kargo' data-cat='napolnenie' data-header='Наполнение'
								onclick='showcontent(this);'></div>
							<div class="ruchka_icon" data-id='ruchka' data-cat='otkrivanie' data-header='Открывание'
								onclick='showcontent(this);'></div>
							<div class="magic_icon" data-id='magic' data-cat='napolnenie' data-header='Наполнение'
								onclick='showcontent(this);'></div>
							<div class="vidvij_icon" data-id='vidvijnie' data-cat='vidvijnie' data-header='Выдвижные ящики'
								onclick='showcontent(this);'></div>
						</div>

						<div class='acsessory_categories'>
							<div class='petli_cat cat_div' data-id='petli' data-cat='petli' data-header='Петли'
								onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/petli-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_bot_cat_name'>Петли</div>
							</div>
							<div class='podjemnie_cat cat_div' data-id='podjemnie' data-cat='podjemnie'
								data-header='Подъемные механизмы' onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/podjemnie-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_bot_cat_name'>Подъемные<br> механизмы</div>
							</div>
							<div class='vidvijnie_cat cat_div' data-id='vidvijnie' data-cat='vidvijnie' data-header='Выдвижные ящики'
								onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/vidvijnie-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_top_cat_name'>Выдвижные <br>ящики</div>
							</div>
							<div class='podsvetka_cat cat_div' data-id='podsvetka' data-cat='podsvetka' data-header='Подсветка'
								onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/osveschenie-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_top_cat_name'>Подсветка</div>
							</div>
							<div class='napolnenie_cat cat_div' data-id='napolnenie' data-cat='napolnenie' data-header='Наполнение'
								onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/napolnenie-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_top_cat_name'>Наполнение</div>
							</div>
							<div class='otkrivanie_cat cat_div' data-id='otkrivanie' data-cat='otkrivanie' data-header='Открывание'
								onclick='showcontent(this);'>
								<img src="/wp-content/uploads/2019/11/otkrivanie-cat.png" alt="Аксессуары" style='width:100%;' />
								<div class='acsess_cat_overlay'></div>
								<div class='acsess_left_top_cat_name'>Открывание</div>
							</div>
						</div>

						<div id='acsess_content'></div>
						<div class='page_content_div'>
							<div id='post_content' class='page_post_content'></div>
						</div>

						<style>
							.acsess_preview {
								width: 100%;
								min-height: 100px;
								background: #dea993;
								;
								margin: 32px 0 64px 0;
								padding: 32px;
							}

							.acsessory_categories {
								width: 100%;
							}

							.acsess_cat_overlay {
								width: 100%;
								height: 100%;
								position: absolute;
								top: 0;
								left: 0;
								background: rgba(222, 169, 147, 0.6);
								display: none;
								cursor: pointer;
							}

							.cat_div:hover .acsess_cat_overlay {
								display: block;
							}

							.acsess_left_bot_cat_name {
								bottom: 32px;
								left: 32px;
								position: absolute;
								font-size: 22px;
								font-weight: 400;
								cursor: pointer;
							}

							.acsess_left_top_cat_name {
								top: 32px;
								left: 32px;
								position: absolute;
								font-size: 22px;
								font-weight: 400;
								cursor: pointer;
							}

							.petli_cat {
								width: calc(35% - 17px);
								margin-right: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.podjemnie_cat {
								width: calc(65% - 22px);
								margin-left: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.vidvijnie_cat {
								width: calc(50% - 20px);
								margin-right: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.podsvetka_cat {
								width: calc(50% - 20px);
								margin-left: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.napolnenie_cat {
								width: calc(65% - 22px);
								margin-right: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.otkrivanie_cat {
								width: calc(35% - 17px);
								margin-left: 16px;
								display: inline-block;
								margin-top: 32px;
								position: relative;
							}

							.kitchenmap {
								width: 1000px;
								max-width: 100%;
								position: relative;
								margin: auto;
								margin-bottom: 32px;
							}

							.kitchenmap img {
								width: 100%;
							}

							.petli_icon,
							.sushki_icon,
							.ugl_shkaf_icon,
							.podiom_icon,
							.kolonni_icon,
							.kargo_icon,
							.ruchka_icon,
							.magic_icon,
							.vidvij_icon {
								cursor: pointer;
								position: absolute;
								width: 8.8%;
								height: 13%;
								background: transparent;
								border-radius: 50%;
							}

							.petli_icon {
								top: 10.9%;
								left: 7.2%;
							}

							.sushki_icon {
								top: 10.9%;
								left: 26.4%;
							}

							.ugl_shkaf_icon {
								top: 10.9%;
								left: 43.7%;
							}

							.podiom_icon {
								top: 10.9%;
								left: 62.22%;
							}

							.kolonni_icon {
								top: 10.9%;
								left: 80.81%;
							}

							.kargo_icon {
								bottom: 14.25%;
								left: 16.3%;
							}

							.ruchka_icon {
								bottom: 14.25%;
								left: 34.9%;
							}

							.magic_icon {
								bottom: 14.25%;
								left: 53.28%;
							}

							.vidvij_icon {
								bottom: 14.25%;
								left: 72.8%;
							}

							.petli_icon:hover {
								background: url(/wp-content/uploads/2019/11/petli.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.sushki_icon:hover {
								background: url(/wp-content/uploads/2019/11/sushki.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.ugl_shkaf_icon:hover {
								background: url(/wp-content/uploads/2019/11/shkaf.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.podiom_icon:hover {
								background: url(/wp-content/uploads/2019/11/podjem.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.kolonni_icon:hover {
								background: url(/wp-content/uploads/2019/11/kolonni.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.kargo_icon:hover {
								background: url(/wp-content/uploads/2019/11/kargo.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.ruchka_icon:hover {
								background: url(/wp-content/uploads/2019/11/ruchki.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.magic_icon:hover {
								background: url(/wp-content/uploads/2019/11/magic.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							.vidvij_icon:hover {
								background: url(/wp-content/uploads/2019/11/vidvij.png) no-repeat;
								background-size: contain;
								background-position: center;
							}

							iframe {
								margin: 32px 0;
							}

							#post_content h1,
							#post_content h2 {
								margin: 64px 0 32px 0;
							}

							#post_content p {
								margin: 32px 0;
							}

							#post_content ul {
								margin: 32px 0 64px 32px;
							}

							.wp-block-gallery {
								margin: 32px 0 !important;
							}

							.subcats_div {
								width: calc(100% + 32px);
								margin-left: -16px;
								margin-top: 64px;
								margin-bottom: 32px;
							}

							.subcats_header {
								font-size: 24px;
								display: block;
								margin-left: 16px;
								color: #585858;
							}

							.subcats_hr {
								margin: 16px 0 16px 16px;
								width: calc(100% - 33px);
								background-color: #484848;
							}

							.one_subcat_div {
								display: inline-block;
								margin: 16px;
								width: calc(16.8% - 37px);
								text-align: center;
								vertical-align: top;
							}

							.acsess_subcat_image {
								cursor: pointer;
								border-radius: 50%;
								width: 100%;
								min-height: 100px;
							}

							.one_subcat_name {
								margin: 16px 0;
							}

							.page_content_div {
								width: 100%;
								background: #f9f9f9;
								display: none;
							}

							.page_post_content {
								max-width: 950px;
								margin: auto;
								padding: 32px 32px 32px 32px;
								font-size: 16px;
							}

							@media screen and (max-width:1024px) {
								.acsess_left_bot_cat_name {
									bottom: 16px;
									left: 16px;
									font-size: 18px;
								}

								.acsess_left_top_cat_name {
									top: 16px;
									left: 16px;
									font-size: 18px;
								}

								.one_subcat_div {
									width: calc(25% - 37px);
								}

								.page_post_content {
									padding: 1px 32px;
								}
							}

							@media screen and (max-width:800px) {
								.cat_div {
									width: 100%;
									height: 200px;
									overflow: hidden;
									background: #dadada;
									margin: 8px 0 8px 4px;
								}

								.cat_div img {
									width: auto !important;
									height: 100%;
									max-width: 200% !important;
									float: right;
								}

								.napolnenie_cat img {
									margin-right: -10%;
								}

								.one_subcat_div {
									width: calc(33% - 37px);
								}
							}

							@media screen and (max-width:700px) {
								.kitchenmap {
									display: none;
								}
							}

							@media screen and (max-width:600px) {
								.one_subcat_div {
									width: calc(50% - 20px);
								}

								.one_subcat_div {
									margin: 8px;
								}

								.page_post_content {
									padding: 0;
								}

								.page_content_div {
									background: transparent;
								}
							}

							@media screen and (max-width: 500px) {
								.napolnenie_cat img {
									margin-right: -25%;
									margin-top: 7%;
								}

								.wp-block-gallery .blocks-gallery-image,
								.wp-block-gallery .blocks-gallery-item {
									margin: 0 0 16px 0;
								}
							}
						</style>

						<script>
							function showcontent(obj) {
								dataid = jQuery(obj).data('id');
								datacat = jQuery(obj).data('cat');
								dataheader = jQuery(obj).data('header');
								var content_div = document.getElementById('acsess_content');
								var post_content = document.getElementById('post_content');

								var target = document.getElementById('acsess_content');
								jQuery('html, body').animate({ scrollTop: jQuery(target).offset().top - 150 }, 500);

								jQuery(post_content).html('');
								jQuery('.page_content_div').css('display', 'none');

								jQuery.ajax({
									url: '/wp-content/plugins/bp_materials/ajax_subcats.php',
									type: 'POST',
									data: { type: datacat, header: dataheader },
									cache: false,
									success: function (html) {
										jQuery('#acsess_content').html(html);
									}
								});

								//alert(datacat);

							}
						</script>

						<?php
						while (have_posts()):
							the_post();
							get_template_part('template-parts/content', 'page');
							// If comments are open or we have at least one comment, load up the comment template.
						endwhile; // End of the loop.
						?>



						<div id="content" class="site-content">
							<div class="mt-container">

								<?php echo do_shortcode("[cities_window]"); ?>

							</div>
						</div>
				</div>
				<?php
				get_sidebar();
				get_footer();
