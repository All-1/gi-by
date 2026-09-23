<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */

?>
	</div> <!-- mt-container -->
	</div><!-- #content -->


<span itemtype="http://data-vocabulary.org/Rating" itemscope="" itemprop="rating">
<meta content="5" itemprop="value">
<meta content="5" itemprop="best">
</span>

		<footer id="colophon" itemscope itemtype="http://schema.org/WPFooter" class="footer site-footer">
<div class="footer__holder container">
	 <meta itemprop="copyrightYear" content="2020">	
	

		<?php
			$wp_diary_footer_widget_option = get_theme_mod( 'wp_diary_enable_footer_widget_area', true );
			if( true === $wp_diary_footer_widget_option ) {
				get_sidebar( 'footer' );
			}
			
			if(is_user_logged_in()){
				$logged_btn = "
					<a href='/login-page/'>
						<div class='back_to_cabinet'>
							<i class='fa fa-home fa-2x' title='Вернуться в кабинет'></i>
						</div>
					</a>
				";
				echo $logged_btn;
			}
		?>
	
        <div id="bottom-footer">
			
            <div class="mt-container">
        		<?php
        			$wp_diary_enable_footer_menu = get_theme_mod( 'wp_diary_enable_footer_menu', true );
        			if( true === $wp_diary_enable_footer_menu ) {
        		?>
        				<nav id="footer-navigation" class="footer-navigation">
    						<?php
    							wp_nav_menu( array(
    								'theme_location' => 'footer_menu',
    								'menu_id'        => 'footer-menu',
    								'fallback_cb' 	 => false,
    								'depth'			 => 1
    							) );
    						?>
        				</nav><!-- #footer-navigation -->
        		<?php
        			}
        		?>
				<!--
        		<div class="site-info">
					ООО "ГеосИдеал" УНН 101 243 849
					<span class="footer__copyright"> © 1991 - <span itemprop="copyrightYear">2020</span> <span itemprop="copyrightHolder"> GeosIdeal</span></span>
        		</div-- .site-info -->

            </div>
        </div>
	
		
		
	</footer><!-- #colophon -->

	<?php

		/**
		 * wp_diary_scroll_top hook
		 *
		 * @hooked - wp_diary_scroll_top_content - 10
		 *
		 * @since 1.0.0
		 */
		do_action( 'wp_diary_scroll_top' );
	?>
	
</div><!-- #page -->

<?php
	/**
     * wp_diary_after_page hook
     *
     * @since 1.0.0
     */
    do_action( 'wp_diary_after_page' );
?>

<?php wp_footer(); ?>
	<script type='application/ld+json'> 
	{
	  "@context": "http://www.schema.org",
	  "@type": "LocalBusiness",
	  "name": "ГеосИдеал",
	  "url": "https://gi.by/",
	  "logo": "https://gi.by/wp-content/uploads/2020/01/logo_new_3.png",
	  "image": "https://gi.by/wp-content/uploads/2020/01/logo_new_3.png",
	  "description": "Фабрика кухонь ГеосИдеал",
	   "telephone" : ["+375(29) 374-10-81", "+375(33) 676-18-36"],
	  "email" : "info@gi.by",
	  "priceRange" : "$",
	  "address": {
		"@type": "PostalAddress",
		"streetAddress": "д. 11, каб 51 вблизи пос, Октябрьский 222220",
		"addressLocality": "Минск",
		"addressCountry": "Беларусь"
	  },
	  "geo": {
		"@type": "GeoCoordinates",
		"latitude": "53.9870364",
		"longitude": "27.7444311"
	  },
	  "hasMap": "https://goo.gl/maps/wUqmUYtctRPsiARUA",
	  "openingHours": "Mo, Tu, We, Th, Fr, 10:00-21:00",
	  "contactPoint": {
		"@type": "ContactPoint",
		"telephone": "+375293741081",
		"contactType": "office"
	  }
	}
	 </script>
<div itemscope itemtype="https://schema.org/Organization">
			<meta itemprop="name" content="ГеосИдеал" />
            <meta itemprop="description" content="Кухни от производителя" />
				<meta itemprop="email" content="sales@gi.by"/>
				<meta itemprop="address" content="ул. Каменногорская, д. 6" />
                </div>
		<script type="text/javascript">
var phone = document.getElementsByClassName('ga_ym_t');
var mail = document.getElementsByClassName('ga_ym_m');
console.log(mail);
for (i=0; i< phone.length; i++){
  phone[i].onclick = function(e) {

      console.log('click');
      ga('send', 'event', 'tel-info', 'ClickTel');
      yaCounter46264947.reachGoal('ClickTelYM');
      return true;
  };
  phone[i].oncopy = function(e) {
      console.log('click');
      ga('send', 'event', 'tel-info', 'CopyTel');
      yaCounter46264947.reachGoal('CopyTelYM');
      return true;
  };
  phone[i].oncontextmenu = function(e) {
      ga('send', 'event', 'tel-info', 'RightTel');
      yaCounter46264947.reachGoal('RightClickTelYM');
      return true;
  }
}
for (i=0; i< mail.length; i++){
  mail[i].click = function(e) {
      ga('send', 'event', 'mail-info', 'ClickMail');
      yaCounter46264947.reachGoal('ClickMailYM');;
      return true;
  };
  mail[i].oncopy = function(e) {
      ga('send', 'event', 'mail-info', 'CopyMail');
      yaCounter46264947.reachGoal('CopyMailYM');
      return true;
  };
  mail[i].oncontextmenu = function(e) {
      ga('send', 'event', 'mail-info', 'RightMail');
      yaCounter46264947.reachGoal('RightClickMailYM');
      return true;
  }
}
</script>
<script>
	var gde_zakazat_bg = document.getElementById('gde_zakazat_bg');
	var gde_zakazat_close = document.getElementById('gde_zakazat_close');
	var open_gde_zakazat = document.getElementById('menu-item-148');
	var opencitieswindow = document.getElementById('opencitieswindow');
	console.log(open_gde_zakazat);
	open_gde_zakazat.onclick = function(){
		gde_zakazat_bg.style.display='block';
	}
	if(opencitieswindow){
		opencitieswindow.onclick = function(){
			gde_zakazat_bg.style.display='block';
		}		
	}

	gde_zakazat_close.onclick = function(){
		gde_zakazat_bg.style.display='none';
	}
</script>	


<div  itemscope itemtype="http://schema.org/Store"><meta itemprop="name" content="Интернет-магазин кухонь Geos Ideal"><div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><meta itemprop="streetAddress" content="Минская область, Смолевичский район, Плисский с/с, вблизи п. Октябрьский, д. 11,"><meta itemprop="postalCode" content=" 222220"><meta itemprop="addressLocality" content="Минск"><meta itemprop="addressCountry" content="BY"></div><div itemprop="geo" itemscope itemtype="http://schema.org/GeoCoordinates"><meta itemprop="latitude" content="53.93220921741439"><meta itemprop="longitude" content="27.556115893638005"></div><meta itemprop="image" content="https://gi.by/wp-content/uploads/2020/01/logo_new_3.png"><meta itemprop="telephone" content="+375(29)374-10-81"><meta itemprop="email" content="info@gi.by"><meta itemprop="priceRange" content="0,01-10000"></div>



</body>
</html>
