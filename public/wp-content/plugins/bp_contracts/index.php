<?php
/*
Plugin Name: bp_contracts
Description: Раздел "Новый личный кабинет" в кабинете дилера
Version: 3.0
file: index.php
Author: Abbasov Ruslan
*/

add_shortcode('contracts', 'contracts');
add_shortcode('staff_management', 'staff_management');
add_shortcode('co_workers', 'co_workers');
add_shortcode('my_profile', 'my_profile');
add_shortcode('orders_dev', 'orders_dev');
add_shortcode('points_manager_new', 'points_manager_new');
add_shortcode('points_dev', 'points_dev');
add_shortcode('contractors_management', 'contractors_management');
add_shortcode('reg_users', 'reg_users');
add_shortcode('invoices', 'invoices');
add_shortcode('analytics', 'analytics');

function transferUserToJS($Id, $user_role)
{
	?>
	<script>
		let Id = "<?php echo $Id; ?>";
		let role = "<?php echo $user_role; ?>";
	</script>
	<?php
}

function addCssLinks()
{
	$version = setVPC();
	?>
	<link rel="stylesheet" href="/wp-content/plugins/bp_contracts/css/style.css<?php echo $version; ?>">
	<?php
}
function addJsLinks()
{
	$version = setVPC();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/utility.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/user.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/controls_for_page.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/notification.js<?php echo $version; ?>"></script>
	<?php
}
function invoiceJsFunctionals()
{
	$version = setVPC();
	?>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/integer_to_words.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/invoice_print.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/pdfmake.min.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/vfs_fonts.js<?php echo $version; ?>"></script>
	<?php
}
function contractors_management()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	$version = setVPC();
	?>
	<h1>Партнёры</h1>
	<div class="header-contractors">
		<div class="box-filter">
			<span>Статус</span>
			<div class="filter-contractors">
				<div class="header-filters-contractors"> - </div>
				<ul class="list-filters-contractors">
					<li id="bind">привязанные</li>
					<li id="nobind">не привязанные</li>
					<li id="active">активные</li>
					<li id="blocked">заблокированные</li>
				</ul>
			</div>
		</div>
		<div class="box-search-contractors">
			<input type='search' id='search_contractors' placeholder='Поиск партнёра'>
		</div>
	</div>
	<div class="container-contractors">
		<div class="tabs-contractors">
			<div class="tab">
				<input type="radio" id="distributors" name="contractors" checked>
				<label for="distributors">Дистрибьютеры</label>
			</div>
			<div class="tab">
				<input type="radio" id="dealers" name="contractors">
				<label for="dealers">Дилеры</label>
			</div>
			<div class="tab">
				<input type="radio" id="designers" name="contractors">
				<label for="designers">Дизайнеры</label>
			</div>
		</div>
		<div class="box-contractors">
			<div class="tr_header_contractors" id="tr_header_contractors">
				<div class="checkbox-overal-contractors">
					<input type="checkbox" id="contractors-overall" name="contractors-overall">
				</div>
				<div class="title-name-contractors">
					<span>Имя</span>
				</div>
				<div class="title-status-contractors">
					<span>Статус</span>
				</div>
				<div class="bind-contractors">
					<span>Привязан</span>
				</div>
			</div>
		</div>
	</div>
	<div class="control-table-contractors">
		<div id="box-before-pagination">
		</div>
		<!-- <div class="empty-box"></div> -->
		<div class='pagination-admin' id='pagination'>

		</div>
		<div class="choice-perpage-row">
			<div class='perpage-text'>Выводить по:</div>
			<div class="perpage-numbers">
				<div class="perpage-number-selected">20</div>
				<div class="perpage-number-list">
					<div class="perpage-number-disable">10</div>
					<div class="perpage-number-disable">15</div>
					<div class="perpage-number-enable">20</div>
					<div class="perpage-number-disable">30</div>
					<div class="perpage-number-disable">40</div>
					<div class="perpage-number-disable">50</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/partners.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}
function points_manager_new()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	$version = setVPC();
	ob_start();
	?>
	<h1>Подключение точек</h1>
	<!-- <h2>
		<?php echo $display_name . ' - Роль: ' . $user_role ?>
	</h2> -->
	<div class="choice_manager_div"></div>
	<div class="main_container_points"></div>
	<div class='point_control'>
		<button class='save-points' id="save-points">Сохранить точки</button>
	</div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/points.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
}

function points_dev()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	$version = setVPC();
	ob_start();
	?>
	<h1>Подключение точек</h1>
	<div class="points_top">
		<div class="points_search">
			<input type="search" class="search_points" id="search_points" placeholder="Поиск точки">
		</div>
		<div class="point_control">
			<button class='disabled-button' id="show-hidden-points">
				Показать скрытые
			</button>
			<button class='save-points' id="save-points">
				Сохранить изменения
			</button>
		</div>
	</div>
	<div class="table-points">
		<div class="outer_container points-header-wrapper">
			<div class="header_points" id="tr_header_points"></div>
		</div>
		<div class="main_container_points"></div>
	</div>
	<div class="control-table-contract">
		<div id="box-before-pagination">
		</div>
		<div class='pagination-admin' id='pagination'>

		</div>
		<div class="choice-perpage-row">
			<div class='perpage-text'>Выводить по:</div>
			<div class="perpage-numbers">
				<div class="perpage-number-selected">20</div>
				<div class="perpage-number-list">
					<div class="perpage-number-disable">3</div>
					<div class="perpage-number-disable">5</div>
					<div class="perpage-number-disable">10</div>
					<div class="perpage-number-disable">15</div>
					<div class="perpage-number-enable">20</div>
					<div class="perpage-number-disable">30</div>
					<div class="perpage-number-disable">40</div>
					<div class="perpage-number-disable">50</div>
					<div class="perpage-number-disable">100</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/points_dev.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>


	<?php
	$html = ob_get_clean();
	return $html;
}

function orders_dev()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	$term_order = 60;
	$version = setVPC();
	ob_start();
	?>
	<h1>
		Заказы
	</h1>
	<!-- <h2>
		<?php echo $display_name . ' - Роль: ' . $user_role ?>
	</h2> -->
	<!--<p>Срок изготовления заказа с момента передачи в прозводство:<b> <?php echo $term_order; ?> дней </b></p>-->
	<?php
	if ($user_role !== 'designer_architect') { ?>
		<div class='header_orders_dev'>
			<div class="header-orders-row">
				<div class="create-orders">
					<div class="create-order-button">Создать</div>
					<ul class="create-order-list">
						<li id="package-info-orders">Упаковочные</li>
						<li id="print-report-orders">Краткий отчёт</li>
						<!-- <li id="create-invoice-orders">Счёт</li> -->
						<!-- <li id="create-shipment-orders">Отгрузка</li>-->
					</ul>
				</div>
				<div class='characteristics-orders'>
					<div class="total_characteristics">
						<span>Брутто: </span>
						<span class="total_bruto">0</span>
					</div>
					<div class="total_characteristics">
						<span>Нетто: </span>
						<span class="total_netto">0</span>
					</div>
					<div class="total_characteristics">
						<span>Объём: </span>
						<span class="total_volume">0</span>
					</div>
				</div>
			</div>

			<div class='orders_filters_block'>
				<div class='order_search'>
					<input type='search' id='search_orders' placeholder='Поиск заказа'>
				</div>
				<div class="filter_date_orders">
					<label for="start_date_orders">c: </label>
					<input type="date" id="start_date_orders" name="startDate">

					<label for="end_date_orders">по: </label>
					<input type="date" id="end_date_orders" name="endDate">
				</div>
				<div class='filters_orders'>
					<div class='box-filter'>
						<input type='checkbox' id='confirm' name='Подтверждён'>
						<label for="confirm">Подтверждён</label>

					</div>
					<div class='box-filter'>
						<input type='checkbox' id='in_production' name='Производство'>
						<label for="in_production">В производстве</label>
						<!-- <span>Заказы</span> -->
					</div>
					<div class='box-filter'>
						<input type='checkbox' id='packaged' name='Упакован'>
						<label for="packaged">Упакован</label>
						<!-- <span>Рекламации</span> -->
					</div>
				</div>
			</div>
		</div>
	<?php } ?>
	<div class='scrolling_div_orders'>
		<?php date_default_timezone_set('Europe/Moscow');
		?>
		<div class='table_div_orders'>
			<div class='tr_div_header_orders' id='tr_header_orders'>
				<div class='td_div_header_orders'><input type='checkbox' id='all_check_orders'></div>
				<div class='td_div_header_orders orders_number_factory'>№</div>
				<div class='td_div_header_orders orders_number_client'>Имя</div>
				<div class='td_div_header_orders orders_status'>Состояние</div>
				<div class='td_div_header_orders orders_accepted'>Принят</div>
				<div class='td_div_header_orders orders_invoice'>Счёт</div>
				<div class='td_div_header_orders orders_completion'>Сдача</div>
				<div class='td_div_header_orders orders_shipment'>Отгрузка</div>
				<div class='td_div_header_orders orders_point'>Точка</div>
				<div class='clear-fix'></div>
			</div>
		</div>
	</div>
	<div class="control-table-contract">
		<div id="box-before-pagination">
		</div>
		<!-- <div class="empty-box"></div> -->
		<div class='pagination-admin' id='pagination'>
		</div>
		<div class="choice-perpage-row">
			<div class='perpage-text'>Выводить по:</div>
			<div class="perpage-numbers">
				<div class="perpage-number-selected">20</div>
				<div class="perpage-number-list">
					<div class="perpage-number-disable">3</div>
					<div class="perpage-number-disable">5</div>
					<div class="perpage-number-disable">10</div>
					<div class="perpage-number-disable">15</div>
					<div class="perpage-number-enable">20</div>
					<div class="perpage-number-disable">30</div>
					<div class="perpage-number-disable">40</div>
					<div class="perpage-number-disable">50</div>
					<div class="perpage-number-disable">100</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	invoiceJsFunctionals();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/orders.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/modal_window.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<!-- <script src="https://stuk.github.io/jszip/dist/jszip.min.js"></script> -->
	<script src="/wp-content/plugins/bp_contracts/js/jszip.min.js<?php echo $version; ?>"></script>
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/exceljs/4.3.0/exceljs.min.js"></script> -->
	<script src="/wp-content/plugins/bp_contracts/js/exceljs.js<?php echo $version; ?>"></script>
	<!-- Add jsPDF and its dependencies -->
	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script> -->


	<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script> -->
	<!-- <script>
		// Initialize pdfMake with fonts
		pdfMake.vfs = pdfMake.vfs || {};
		pdfMake.fonts = {
			Roboto: {
				normal: 'Roboto-Regular.ttf',
				bold: 'Roboto-Medium.ttf',
				italics: 'Roboto-Italic.ttf',
				bolditalics: 'Roboto-MediumItalic.ttf'
			}
		};
	</script> -->
	<?php
	$html = ob_get_clean();
	return $html;
}

function contracts()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	$version = setVPC();
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}

	ob_start();
	?>
	<!-- <button id="enable-notifications">Включить уведомления</button> -->
	<div class="header-projects">
		<h1>
			<?php echo $lang_adm->m_contracts ?>
		</h1>
		<a href="/Manual001.pdf" target="_blank">
			<div class='instruction'><span>Инструкция</span></div>
		</a>
	</div>
	<?php
	if ($user_role !== 'designer_architect') { ?>
		<div class='header_contracts'>
			<div class='contracts_search'>
				<?php if ($user_role === 'free_dealer' || $user_role === 'dealer' || $user_role === 'designer_dealer' || $user_role === 'distributor') { ?>
					<input type='search' id='search_contracts' placeholder='Поиск / Новый'>
					<input type='text' name='point_id' style='display:none;'>
					<input type='submit' name='gen_contract' id='gen-contracts' value='Создать'>
				<?php } else { ?>
					<input type='search' id='search_contracts' placeholder='Поиск'>
				<?php }
	}
	?>
		</div>
		<div class='filters_contracts'>
			<!-- <div class='box-filter'>
				<input type='checkbox' id='consultation' name='Consultation'>
				<span>Консультации</span>
			</div>
			<div class='box-filter'>
				<input type='checkbox' id='order' name='Order'>
				<span>Заказы</span>
			</div>
			<div class='box-filter'>
				<input type='checkbox' id='complaint' name='Complaint'>
				<span>Рекламации</span>
			</div> -->
			<div class='box-filter'>
				<input type='checkbox' id='my' name='My' checked>
				<span>Мои</span>
			</div>
		</div>


	</div>

	<div class='scrolling_div_contracts'>
		<?php date_default_timezone_set('Europe/Moscow');
		?>
		<div class='table_div_contracts'>
			<div class='tr_div_header_contracts' id='tr_header_contracts'>
				<div class='td_div_header_contracts'><input type='checkbox' id='all_check_contracts'></div>
				<div class='td_div_header_contracts contracts_number'>№</div>
				<div class='td_div_header_contracts contracts_name'>Имя</div>
				<div class='td_div_header_contracts contracts_point'>Точка</div>
				<div class='td_div_header_contracts contracts_date'>Активность</div>
				<div class='clear-fix'></div>
			</div>
		</div>
	</div>
	<div class="control-table-contract">
		<div id="box-before-pagination">
			<button class='delete_contract' id='delete-empty-contracts'>Удалить договор</button>
		</div>
		<div class='pagination-admin' id='pagination'>

		</div>
		<div class="choice-perpage-row">
			<div class='perpage-text'>Выводить по:</div>
			<div class="perpage-numbers">
				<div class="perpage-number-selected">20</div>
				<div class="perpage-number-list">
					<div class="perpage-number-disable">3</div>
					<div class="perpage-number-disable">5</div>
					<div class="perpage-number-disable">10</div>
					<div class="perpage-number-disable">15</div>
					<div class="perpage-number-enable">20</div>
					<div class="perpage-number-disable">30</div>
					<div class="perpage-number-disable">40</div>
					<div class="perpage-number-disable">50</div>
				</div>
			</div>
		</div>
	</div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	invoiceJsFunctionals();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/contracts.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/modal_window.js<?php echo $version; ?>"></script>
	<!-- <script src="https://stuk.github.io/jszip/dist/jszip.min.js"></script> -->
	<script src="/wp-content/plugins/bp_contracts/js/jszip.min.js<?php echo $version; ?>"></script>

	<?php
	$html = ob_get_clean();
	return $html;
}

function invoices()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	$version = setVPC();
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$display_name = $user_data->display_name;
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_invoices ?>
	</h1>
	<div id='invoices'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>

	<div class='header_invoices'>
		<div class='invoices_search'>
			<input type='search' id='search_invoices' placeholder='Поиск'>
		</div>
		<div class='characteristics-invoices'>
			<div class="total_characteristics">
				<span>Общая сумма: </span>
				<span class="total_sum">0</span>
			</div>
			<div class="total_characteristics">
				<span>Оплачено: </span>
				<span class="total_paid">0</span>
			</div>
			<div class="total_characteristics">
				<span>Долг: </span>
				<span class="total_debt">0</span>
			</div>
		</div>
	</div>
	<div class='scrolling_div_invoices'>
		<?php date_default_timezone_set('Europe/Moscow');
		?>
		<div class='table_div_invoices'>
			<div class='tr_div_header_invoices' id='tr_header_invoices'>
				<div class='td_div_header_invoices'><input type='checkbox' id='all_check_invoices'></div>
				<div class='td_div_header_invoices invoices_number_firm'>Компания</div>
				<div class='td_div_header_invoices invoices_number_number'>Номер</div>
				<div class='td_div_header_invoices invoices_number_date'>Дата</div>
				<div class='td_div_header_invoices invoices_amount'>Сумма</div>
				<div class='td_div_header_invoices invoices_debt'>Долг</div>
				<div class='clear-fix'></div>
			</div>
		</div>
	</div>
	<div class="control-table-contract">
		<div id="box-before-pagination">
		</div>
		<!-- <div class="empty-box"></div> -->
		<div class='pagination-admin' id='pagination'>
		</div>
		<div class="choice-perpage-row">
			<div class='perpage-text'>Выводить по:</div>
			<div class="perpage-numbers">
				<div class="perpage-number-selected">20</div>
				<div class="perpage-number-list">
					<div class="perpage-number-disable">3</div>
					<div class="perpage-number-disable">5</div>
					<div class="perpage-number-disable">10</div>
					<div class="perpage-number-disable">15</div>
					<div class="perpage-number-enable">20</div>
					<div class="perpage-number-disable">30</div>
					<div class="perpage-number-disable">40</div>
					<div class="perpage-number-disable">50</div>
					<div class="perpage-number-disable">100</div>
				</div>
			</div>
		</div>
	</div>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/integer_to_words.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/invoices.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/invoice_print.js<?php echo $version; ?>"></script>
	<script src="/wp-content/plugins/bp_contracts/js/exceljs.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/pdfmake.min.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/vfs_fonts.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function staff_management()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$version = setVPC();
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_staff_management ?>
	</h1>
	<div id='stuff-management'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/status-user.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/stuff_management.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function co_workers()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$version = setVPC();
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_co_workers ?>
	</h1>
	<div id='co-workers'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/status-user.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/stuff_management.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function my_profile()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$version = setVPC();
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_my_profile ?>
	</h1>
	<div id='my-profile'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/status-user.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/my_profile.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function reg_users()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$version = setVPC();
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_reg_users ?>
	</h1>
	<div id='reg-users'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/reg_users.js<?php echo $version; ?>"></script>
	<script type="text/javascript"
		src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function analytics()
{
	require($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
	require($_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php');
	// global $wpdb;
	global $lang_adm;
	$Id = get_current_user_id();
	$user_data = get_userdata($Id);
	$user_role = $user_data->roles[0];
	$version = setVPC();
	if ($user_role === 'subscriber') {
		$user_role = 'dealer';
	}
	ob_start();
	?>
	<h1>
		<?php echo $lang_adm->m_analytics ?>
	</h1>
	<div class="sla_analytics">
		<div class="sla_analytics_block sla_analytics_first">
			<span class="sla_analytics_title">Первый ответ менеджера — SLA</span>
			<div class="sla_analytics_duration" data-target="first">
				<label class="sla_analytics_label" for="sla_first_days">д.</label>
				<input type="number" id="sla_first_days" class="sla_analytics_input sla_analytics_input_short" min="0" max="7" value="0">
				<label class="sla_analytics_label" for="sla_first_hours">ч.</label>
				<input type="number" id="sla_first_hours" class="sla_analytics_input sla_analytics_input_short" min="0" max="23" value="3">
				<label class="sla_analytics_label" for="sla_first_minutes">мин.</label>
				<input type="number" id="sla_first_minutes" class="sla_analytics_input sla_analytics_input_short" min="0" max="59" value="0">
			</div>
			<div class="sla_analytics_presets">
				<button type="button" class="sla_analytics_preset" data-target="first" data-minutes="180">3 ч</button>
				<button type="button" class="sla_analytics_preset" data-target="first" data-minutes="240">4 ч</button>
				<button type="button" class="sla_analytics_preset" data-target="first" data-minutes="300">5 ч</button>
			</div>
		</div>
		<div class="sla_analytics_block sla_analytics_subsequent">
			<span class="sla_analytics_title">Последующие ответы менеджера — SLA</span>
			<div class="sla_analytics_duration" data-target="subsequent">
				<label class="sla_analytics_label" for="sla_subsequent_days">д.</label>
				<input type="number" id="sla_subsequent_days" class="sla_analytics_input sla_analytics_input_short" min="0" max="7" value="0">
				<label class="sla_analytics_label" for="sla_subsequent_hours">ч.</label>
				<input type="number" id="sla_subsequent_hours" class="sla_analytics_input sla_analytics_input_short" min="0" max="23" value="2">
				<label class="sla_analytics_label" for="sla_subsequent_minutes">мин.</label>
				<input type="number" id="sla_subsequent_minutes" class="sla_analytics_input sla_analytics_input_short" min="0" max="59" value="0">
			</div>
			<div class="sla_analytics_presets">
				<button type="button" class="sla_analytics_preset" data-target="subsequent" data-minutes="60">1 ч</button>
				<button type="button" class="sla_analytics_preset" data-target="subsequent" data-minutes="120">2 ч</button>
				<button type="button" class="sla_analytics_preset" data-target="subsequent" data-minutes="180">3 ч</button>
			</div>
		</div>
	</div>
	<div class="analytics_header">
		<div class="analytics_header_left">
			<div class="whose_analytics">
				<div class="whose_analytics_title">Все </div>
				<div class="whose_analytics_select">
					<div class="whose_analytics_select_option" data-value="all">Все</div>
					<div class="whose_analytics_select_option" data-value="factory">Фабрика</div>
					<div class="whose_analytics_select_option" data-value="contractor">Контрагент</div>
					<div class="whose_analytics_select_option" data-value="dealer">Дилеры</div>
				</div>
			</div>
			<div class="user_search">
				<input type="text" id="user_search" class="input_user_search" placeholder="Введите имя">
			</div>
		</div>
		<div class="analytics_header_right">
			<div class="period_analytics">
				<div class="period_analytics_title">Период</div>
				<div class="period_analytics_select">
					<div class="period_analytics_select_option" data-value="all">Вместе</div>
					<div class="period_analytics_select_option" data-value="week">Неделя</div>
					<div class="period_analytics_select_option" data-value="month">Месяц</div>
					<div class="period_analytics_select_option" data-value="quarter">Квартал</div>
					<div class="period_analytics_select_option" data-value="year">Год</div>
				</div>
			</div>
			<div class="time_mode_analytics">
				<div class="time_mode_analytics_title">Рабочие часы</div>
				<div class="time_mode_analytics_select">
					<div class="time_mode_analytics_select_option" data-value="work_hours">Рабочие часы</div>
					<div class="time_mode_analytics_select_option" data-value="standard">Календарные</div>
				</div>
			</div>
			<div class="date_analytics">
				<div class="date_analytics_input">
					<label for="start_date_analytics">От</label>
					<input type="date" id="start_date_analytics" placeholder="От">
					<label for="end_date_analytics">До</label>
					<input type="date" id="end_date_analytics" placeholder="До">
				</div>
			</div>
			<div class="dialogues_type_analytics">
				<div class="dialogues_type_analytics_title">Диалог </div>
				<div class="dialogues_type_analytics_select">
					<div class="dialogues_type_analytics_select_option" data-value="">Все</div>
					<div class="dialogues_type_analytics_select_option" data-value="consultation">Консультация</div>
					<div class="dialogues_type_analytics_select_option" data-value="order">Заказ</div>
					<div class="dialogues_type_analytics_select_option" data-value="complaint">Рекламация</div>
				</div>
			</div>
		</div>
	</div>
	<div id='analytics'></div>
	<?php
	transferUserToJS($Id, $user_role);
	addJsLinks();
	addCssLinks();
	?>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/communication_server.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/chart.umd.js<?php echo $version; ?>"></script>
	<script type="text/javascript" src="/wp-content/plugins/bp_contracts/js/analytics.js<?php echo $version; ?>"></script>
	<?php
	$html = ob_get_clean();
	return $html;
}

function convert_timestamp_index($date)
{
	$date = new DateTime($date);
	$date = $date->getTimestamp();
	return $date;
}
