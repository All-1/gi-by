<?php
/*Template Name: My account pages

 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Mystery Themes
 * @subpackage WP Diary
 * @since 1.0.0
 */
$current_user_id = get_current_user_id();
$check_user_blocked = "SELECT status_activity FROM gi_new_users WHERE id_user = '$current_user_id'";
$result_user_blocked = $wpdb->get_var($check_user_blocked);
if ($result_user_blocked === 'blocked') {
	wp_logout();
	wp_redirect(home_url("/"));
}
if (!is_user_logged_in() && !is_page('login-page')) {
	wp_redirect(home_url("/login-page/"));
	exit;
}
get_header(); ?>

<body <?php body_class(); ?>>

	<!-- Google Tag Manager (noscript) -->
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WCDGGVW" height="0" width="0"
			style="display:none;visibility:hidden"></iframe></noscript>
	<!-- End Google Tag Manager (noscript) -->

	<div id="page" class="site">

		<div id="content" class="site-content">
			<div class="mt-container login-account">
				<?php
				
				$user = get_userdata($current_user_id);
				$user_roles = $user->roles;
				// Check if the role you're interested in, is present in the array.
				$check_administrator = in_array('administrator', $user_roles, true);
				$check_manager = in_array('manager', $user_roles, true);
				$check_consultant = in_array('consultant', $user_roles, true);
				$check_complaint_handler = in_array('complaint_handler', $user_roles, true);
				$check_specialist = in_array('specialist', $user_roles, true);
				$check_sales_manager = in_array('sales_manager', $user_roles, true);
				$check_subscriber = in_array('subscriber', $user_roles, true);
				$check_free_dealer = in_array('free_dealer', $user_roles, true);
				$check_dealer = in_array('dealer', $user_roles, true);
				$check_distributor = in_array('distributor', $user_roles, true);
				$check_designer_dealer = in_array('designer_dealer', $user_roles, true);
				$check_designer_architect = in_array('designer_architect', $user_roles, true);

				if ($check_administrator) {
					$user_role = "administrator";
				} elseif ($check_manager || $check_consultant || $check_complaint_handler) {
					$user_role = "manager";
				} elseif ($check_specialist) {
					$user_role = "specialist";
				} elseif ($check_sales_manager) {
					$user_role = "sales_manager";
				} elseif ($check_subscriber || $check_dealer) {
					$user_role = "dealer";
				} elseif ($check_free_dealer) {
					$user_role = "free_dealer";
				} elseif ($check_distributor) {
					$user_role = "distributor";
				} elseif ($check_designer_dealer) {
					$user_role = "designer_dealer";
				} elseif ($check_designer_architect) {
					$user_role = "designer_architect";
				}

				include "lang-admin.php";

				$account_navigation_start = "<div class='account_navigation_links'>";
				$account_navigation_finish = "</div>";

				// $my_orders = "<a href='/my-orders/' title='$lang_adm->m_zakazi'> <i class='fa fa-shopping-cart'></i> <span class='menu_link_text'> $lang_adm->m_zakazi</span></a>";
				$my_orders_development = "<a href='/orders/' title='$lang_adm->m_zakazi'> <i class='fa fa-shopping-cart'></i> <span class='menu_link_text'> $lang_adm->m_zakazi</span></a>";
				$contracts = "<a href='/contracts/' title='$lang_adm->m_contracts' class='contracts_link'> <i class='fa fa-contracts'></i> <span class='menu_link_text'> $lang_adm->m_contracts</span></a>";
				$invoices = "<a href='/invoices/' title='$lang_adm->m_invoices' class='invoices_link'> <i class='fa fa-invoices'></i> <span class='menu_link_text'> $lang_adm->m_invoices</span></a>";
				$connect_points = "<a href='/connect-points/' title='$lang_adm->m_connect_points'> <i class='fa fa-map-marker' style='text-align:center'></i> <span class='menu_link_text'>$lang_adm->m_connect_points</span></a>";
				$points_dev = "<a href='/points-dev/' title='$lang_adm->m_connect_points_dev'> <i class='fa fa-map-marker' style='text-align:center'></i> <span class='menu_link_text'>$lang_adm->m_connect_points_dev</span></a>";

				$my_profile = "<a href='/profil/' title='$lang_adm->m_my_profile' class='my-profile'> <i class='fa fa-my-profile' style='text-align:center'></i> <span class='menu_link_text'>$lang_adm->m_my_profile</span></a>";
				$my_staff = "<a href='/staff/' title='$lang_adm->m_staff_management' class='stuff-management'> <i class='fa fa-co-workers' style='text-align:center'></i> <span class='menu_link_text'>$lang_adm->m_staff_management</span></a>";
				$co_workers = "<a href='/co-workers/' title='$lang_adm->m_co_workers' class='stuff-management'> <i class='fa fa-co-workers' style='text-align:center'></i> <span class='menu_link_text'>$lang_adm->m_co_workers</span></a>";
				$partners = "<a href='/partners/' title='$lang_adm->m_partners' class='partners_link'> <i class='fa fa-partners'></i> <span class='menu_link_text'> $lang_adm->m_partners</span></a>";
				$add_users = "<a href='/wp-admin/users.php' title='$lang_adm->m_add_users' class='add_user_link'> <i class='fa fa-add-user'></i> <span class='menu_link_text'> $lang_adm->m_add_users</span></a>";

				$my_salons = "<a href='/salons/' title='$lang_adm->m_my_salons'> <i class='fa fa-map-marker'></i> <span class='menu_link_text'> $lang_adm->m_my_salons</span></a>";
				$my_samples = "<a href='/my-samples/' title='$lang_adm->m_my_samples'> <i class='fa fa-briefcase'></i> <span class='menu_link_text'> $lang_adm->m_my_samples</span></a>";
				$factory_news = "<a href='/updates/' title='$lang_adm->m_news'> <i class='fa fa-calendar'></i> <span class='menu_link_text'> $lang_adm->m_news</span></a>";
				$designer_conditions = "<a href='/designer-conditions/' title='$lang_adm->m_info'> <i class='fa fa-tasks'></i> <span class='menu_link_text'> $lang_adm->m_info</span></a>";
				$load_files = "<a href='/load-files/' title='$lang_adm->m_download'> <i class='fa fa-save'></i> <span class='menu_link_text'> $lang_adm->m_download</span></a>";
				$my_files = "<a href='/files/' title='$lang_adm->m_files'> <i class='fa fa-file'></i> <span class='menu_link_text'> $lang_adm->m_files</span></a>";

				$analytics = "<a href='/analytics/' title='$lang_adm->m_analytics' class='analytics_link'> <i class='fa fa-analytics'></i> <span class='menu_link_text'> $lang_adm->m_analytics</span></a>";

				//MENU FOR ROLES
				switch ($user_role) {
					case "designer_architect":
						$menu =
							$account_navigation_start .
							// $my_orders .
							$designer_conditions .
							$load_files .
							$account_navigation_finish;
						break;
					case "dealer":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$factory_news .
							// $my_salons .
							$my_staff .
							$my_profile .
							$my_files .
							

							// $my_orders .
							// $my_samples .
							$account_navigation_finish;
						break;
					case "free_dealer":
					case "distributor":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$invoices .
							$factory_news .
							// $my_salons .
							$my_staff .
							$my_profile .
							$my_files .
							

							// $my_orders .
							// $my_samples .
							$account_navigation_finish;
						break;
					case "designer_dealer":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$factory_news .
							$my_profile .
							// $my_salons .
							$my_files .
							// $co_workers .
							// $my_orders .
							// $my_samples .
							$account_navigation_finish;
						break;
					case "manager":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$invoices .
							$my_profile .
							$co_workers .
							$my_files .
							// $my_orders .
							// $connect_points .
							$account_navigation_finish;
						break;
					case "administrator":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$invoices .
							$my_staff .
							// $connect_points .
							$points_dev .
							$partners .
							$analytics .
							$add_users .
							$my_profile .
							$my_files .
							// $my_orders .
							// $my_salons .
							// $my_samples .
							// $designer_conditions .
							// $factory_news .
							// $my_files .
							// $load_files .
							$account_navigation_finish;
						break;
					case "sales_manager":
						$menu =
							$account_navigation_start .
							$contracts .
							$my_orders_development .
							$invoices .
							$my_staff .
							// $connect_points .
							$points_dev .
							$partners .
							$add_users .
							$my_profile .
							$my_files .
							// $my_orders .
							// $my_salons .
							// $my_samples .
							// $designer_conditions .
							// $factory_news .
							// $my_files .
							// $load_files .
							$account_navigation_finish;
						break;
					case "specialist":
						$menu =
							$account_navigation_start .
							$contracts .
							$invoices .
							$my_profile .
							$my_files .
							// $my_orders .
							// $connect_points .
							$account_navigation_finish;
						break;
					default:
						$menu =
							$account_navigation_start .
							$my_orders .
							$my_samples .
							$factory_news .
							$my_files .
							$my_profile .
							// $my_salons .
							$account_navigation_finish;
						$menu = $account_navigation_start .
							"
		</div>
		";
						break;
				}
				?>
				<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
				<link href="/wp-content/themes/wp-diary/assets/css/admin_panel.css?v=35" rel="stylesheet">


				<div class="account_navigation">
					<div class='account_logo_icon'>
						<a href='https://gi.by'>
							<br><img src='/wp-content/themes/wp-diary/images/Favicon.png' alt="GeosIdeal" /> <span
								class='menu_link_text'> GeosIdeal</span>
						</a>
					</div>
					<?php echo $menu; ?>
					<div class='account_menu_openclose' id='account_menu_openclose'>
						<i class="fa fa-chevron-right" onclick='open_account_menu();' title='Развернуть'></i>
					</div>
					<div class='account_exit_div' id='account_exit_div'>
						<a href='<?php echo wp_logout_url(home_url()); ?>' title='<?php echo $lang_adm->m_exit; ?>'><i
								class="fa fa-sign-out"></i><span class='menu_link_text exit_link_text'>
								<?php echo $lang_adm->m_exit; ?></span></a>
					</div>
				</div>



				<div id="primary" class="content-area">
					<main id="main" class="site-main">

						<?php
						while (have_posts()):
							the_post();

							get_template_part('template-parts/content', 'page');

							// If comments are open or we have at least one comment, load up the comment template.
							if (comments_open() || get_comments_number()):
								comments_template();
							endif;

						endwhile; // End of the loop.
						?>

					</main><!-- #main -->
				</div><!-- #primary -->



				<script>
					function open_account_menu() {
						var close_btn = "<i class='fa fa-chevron-left' onclick='close_account_menu();'></i>";
						jQuery('.account_navigation').css({ 'width': '250px' });
						jQuery('.account_navigation_links').css({ 'width': 'calc(100% - 48px)' });
						jQuery('.account_navigation_links a').css({ 'text-align': 'left' });
						jQuery('.account_exit_div').css({ 'left': '32px' });
						function show_menu_link_text() {
							jQuery('.menu_link_text').css({ 'visibility': 'visible' });
						}
						setTimeout(show_menu_link_text, 50);
						jQuery('#account_menu_openclose').html(close_btn);
					}
					function close_account_menu() {
						var open_btn = "<i class='fa fa-chevron-right' onclick='open_account_menu();'></i>";
						jQuery('.account_navigation').css('width', '38px');
						jQuery('.account_navigation_links').css({ 'width': 'calc(100% - 24px)' });
						jQuery('#account_menu_openclose').html(open_btn);
						jQuery('.menu_link_text').css({ 'visibility': 'hidden' });
						jQuery('.account_exit_div').css({ 'left': '12px' });
						function menu_text_align() {
							jQuery('.account_navigation_links a').css({ 'text-align': 'center' });
						}
						setTimeout(menu_text_align, 0);

					}
				</script>

				<style>
					.back_to_cabinet {
						display: none !important;
					}
				</style>