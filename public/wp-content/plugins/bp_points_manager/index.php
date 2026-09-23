<?php
/*Plugin Name: bp_points_manager
Description: Раздел "Подключение точек" в кабинете менеджер
Version: 1.0
Author: Business Park*/



add_shortcode('points_manager', 'points_manager');

function points_manager(){
	require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
	include $_SERVER['DOCUMENT_ROOT'] . '/lang-admin.php';
	$perpage = 20;
	global $wpdb;
	global $user_role;
	global $lang_adm;

	$Id = get_current_user_id();

	// Функция для получения пользователей по роли
	function get_users_by_role($role) {
	    $users = get_users(array(
	        'role' => $role,
	    ));

	    return $users;
	}

	//Ищем только менеджеров
	$role_to_search = 'manager'; 
	$users_with_role = get_users_by_role($role_to_search);
	// Вывод результатов
	foreach ($users_with_role as $user) {
	    echo 'User ID: ' . $user->ID . '<br>';
	    echo 'Username: ' . $user->user_login . '<br>';
	    echo 'User Email: ' . $user->user_email . '<br>';
	    // Другие данные пользователя, которые вы хотите вывести
	    echo '<hr>';
	}
	

?>	
	<h1>Подключение точек</h1>
	<form method='POST'>
		<div class='choice_manager_div'>

		<?php 
		// Вывод результатов
		foreach ($users_with_role as $user) {
		    $user_id = $user->ID;
		    $user_first_name = $user->first_name;
		   	$user_last_name = $user->last_name;
		   	$sql_count = "SELECT COUNT(*) FROM gi_points WHERE ManagerId_gi = '$user_id'";
			$rows_count = $wpdb->get_var($sql_count);
			if ($Id === $user_id) {
				$points_manager_checked = "checked";
			} else {
				$points_manager_checked = "";
			}
		?>

			<input class='choice_points_manager' type='checkbox' name='choice_manager' id='manager-<?php echo $user_id; ?>' <?php echo $points_manager_checked; ?>>
			<label for='manager-<?php echo $user_id; ?>'><?php echo $user_first_name; ?><br><?php echo $user_last_name; ?> (<?php echo $rows_count; ?>) </label>
		<?php 
		}?>
		<?php 
			$sql_count = "SELECT COUNT(*) FROM gi_points";
			$rows_count = $wpdb->get_var($sql_count);
		?>
			<input class='choice_points_manager' type='checkbox' name='choice_manager' id='manager-0'><label for='manager-0'>Все<br>точки (<?php echo $rows_count; ?>) </label>
		</div>
		<div class='point_search_div'>
			<input	type='search' name='point_search' id='point_search' placeholder='Название точки или город или артикул...'>
		</div>


		
			<?php
				//Выводим точки дилеров
				foreach ($users_with_role as $user) {
					$user_id = $user->ID;
					$user_first_name = $user->first_name;
		   			$user_last_name = $user->last_name;
					$sql_points = "SELECT * FROM gi_points WHERE ManagerId_gi = '$user_id'";
					$result_points = $wpdb->get_results($sql_points);

					$sql_points_temp = "SELECT * FROM gi_points WHERE FIND_IN_SET('$user_id', ManagerIdTemp_gi) > 0";
					$result_points_temp = $wpdb->get_results($sql_points_temp);
					if ($Id === $user_id) {
						$display_block = "style = 'display: block'";
						$point_checked = "checked";
					} else {
						$display_block = "style = 'display: none'";
						$point_checked = "";
					}
				?>
					<div class="points_main_box" <?php echo $display_block ;?> id='manager_points_box_<?php echo $user_id;?>'>
						<h3>Точки менеджера: <?php echo $user_first_name;?> <?php echo $user_last_name;?> </h3>
						<div class='points_div'  >

							<?php
							foreach($result_points as $row_points){
								$point_id = $row_points->PKId_gi;
								$point_name = $row_points->PointName_gi;
								$manager_id = $row_points->ManagerId_gi;
								?>
							<div class='points_box' >
								<input type='checkbox' name='choice_points' id='point-<?php echo $point_id;?>' class='point' <?php echo $point_checked;?> ><label for='point-<?php echo $point_id;?>'><?php echo $point_name;?></label>
							</div>
						<?php } ?>
						</div>
					</div>
				<?php 
					if ($result_points_temp) {
				?>
					<div class="points_main_box" <?php echo $display_block ;?> id='manager_points_box_<?php echo $user_id;?>'>
						<h3>Временные точки менеджера: <?php echo $user_first_name;?> <?php echo $user_last_name;?> </h3>
						<div class='points_div'  >

							<?php
							foreach($result_points_temp as $row_points_temp){
								$point_id = $row_points_temp->PKId_gi;
								$point_name = $row_points_temp->PointName_gi;
								$manager_id = $row_points_temp->ManagerIdTemp_gi;
								?>
							<div class='points_box' >
								<input type='checkbox' name='choice_points' id='point-<?php echo $point_id;?>' class='point' <?php echo $point_checked;?> ><label for='point-<?php echo $point_id;?>'><?php echo $point_name;?></label>
							</div>
						<?php } ?>
						</div>
					</div>	
			<?php   }
				}?> 


			<?php 
			//Выводим все точки на экран
				$sql_points = "SELECT * FROM gi_points";
				$result_points = $wpdb->get_results($sql_points);
			?>
			<div class="points_main_box" <?php echo $display_block ;?> id='manager_points_box_0'>
				<h3>Все точки </h3>
				<div class='points_div'  >

					<?php
					foreach($result_points as $row_points){
						$point_id = $row_points->PKId_gi;
						$point_name = $row_points->PointName_gi;
						$manager_id = $row_points->ManagerId_gi;
						?>
					<div class='points_box' >
						<input type='checkbox' name='choice_points' id='point-<?php echo $point_id;?>' class='point' <?php echo $point_checked;?> ><label for='point-<?php echo $point_id;?>'><?php echo $point_name;?></label>
					</div>
				<?php } ?>
				</div>
			</div>

		
		<div class='point_amount'>
			Выбрано точек: <span id='total_points_checked'>0</span> точек
		</div>
		<div class='point_control'>
			<button class='point_save'>Сохранить точки</button>
			<button class='point_default'>По умолчанию</button>
		</div>
	</form>
	<script type="text/javascript" src="/wp-content/plugins/bp_points_manager/js/points_script.js"></script>
	
<script>
    
</script>
<?php
}
?>