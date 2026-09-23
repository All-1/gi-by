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
      do_action('wp_diary_header_categories_lists');

      /**
       * wp_diary_header_author_box hook
       *
       * @hooked wp_diary_header_author_box_content - 10
       *
       * @since 1.0.0
       */
      do_action('wp_diary_header_author_box');

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
                <img src='/wp-content/themes/wp-diary/images/logo-vector.svg' />
              </a>
            </div>
            <div class='headerdescr'>Корпоративный сайт фабрики</div>
          </div>

          <div class='header_social_container'>
            <div>
              
            </div>
          </div>
          <div class='header_lang_container'>
            <?php $url = $_SERVER['REQUEST_URI'] ?>
            <div>
              
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
    <div class="menu-toggle">
      <i class="fa fa-navicon"></i>
      <?php esc_html_e('', 'wp-diary'); ?>
      <div class="mobile-social-container">
        <a rel="nofollow" href='https://www.instagram.com/geosideal_official/' target='_blank'>
          <div class='insta_icon'></div>
        </a>
        <a rel="nofollow" href='https://www.facebook.com/idealkuhni/' target='_blank'>
          <div class='fb_icon'></div>
        </a>
        <a rel="nofollow" href='https://www.pinterest.com/geosidealofficial/' target='_blank'>
          <div class='pin_icon' ></div>
        </a>
        <a rel="nofollow" href='https://vk.com/gi_kuhni' target='_blank'>
          <div class='vk_icon' ></div>
        </a>
        <a rel="nofollow" href='https://www.youtube.com/channel/UCXCl2-MvTOpaY2kqqf8W5RQ' target='_blank'>
          <div class='youtube_icon'></div>
        </a>
        <a rel="nofollow" href='https://www.tiktok.com/@geosideal.official?_t=8ftvIeIoF53' target='_blank'>
          <div class='tiktok_icon'></div>
        </a>
        <a rel="nofollow" href='https://www.threads.net/@geosideal_official' target='_blank'>
          <div class='threads_icon'></div>
        </a>
        <a rel="nofollow" href='viber://chat?number=375291641409'>
          <div class='viber_icon'></div>
        </a>
      </div>
    </div>
    <nav id="site-navigation" class="main-navigation">
      <div class="mt-container">
        <?php
        wp_nav_menu(
          array(
            'theme_location' => 'primary_menu',
            'menu_id' => 'primary-menu',
          )
        );
        ?>
      </div>
    </nav><!-- #site-navigation -->
  </div> <!-- main menu wrapper -->
  <span itemscope itemtype="http://schema.org/Organization">
    <meta itemprop="name" content="ГеосИдеал">
    <meta itemprop="logo" content="https://gi.by/wp-content/uploads/2020/01/logo_new_3.png">
    <meta itemprop="url" content="https://gi.by/">
    <meta itemprop="telephone" content="+375(29)374-10-81">
    <meta itemprop="email" content="info@gi.by">
    <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
      <meta itemprop="addressLocality" content="Минск">
      <meta itemprop="streetAddress" content="д. 11, каб 51 вблизи пос, Октябрьский 222220">
      <meta itemprop="addressCountry" content="Беларусь">
    </span>
  </span>


</header><!-- #masthead -->

<?php
/**
 * wp_diary_after_header hook
 *
 * @since 1.0.0
 */
do_action('wp_diary_after_header');


if (!is_front_page()) {
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
  do_action('wp_diary_innerpage_header');
}
?>


<?php echo do_shortcode("[cities_window]"); ?>
<?php echo do_shortcode("[samples_cities_window]"); ?>