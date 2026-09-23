<?php
/*Plugin Name: bp_byers_grid
Description: Сетка записей в разделе "Покупателям"
Version: 1.0
Author: Business Park*/

add_action('admin_menu', 'add_news_page');

function add_news_page() {
	add_menu_page('Покупателям', 'Покупателям', 8, 'my_page_slug', 'my_info_grid', 'dashicons-groups');
	add_menu_page('Новости покупателям', 'Новости покупателям', 8, 'news_for_buyers', 'news_info_grid', 'dashicons-groups');
	add_menu_page( 'Новости дилеров', 'Новости дилеров',  'read', __FILE__, 'news_print', 'dashicons-welcome-view-site', 1);
		add_submenu_page(__FILE__, 'Добавить новость', 'Добавить новость', 8, 'add_news', 'add_news');
		add_submenu_page(__FILE__, 'Редакт. новость', 'Редакт. новость', 8, 'redact_news', 'redact_news');
}

add_shortcode('byers_grid', 'byers_grid');
add_shortcode('kd_news', 'kd_news');
add_shortcode('info_grid', 'info_grid');
add_shortcode('news_grid', 'news_grid');
add_shortcode('news_print', 'news_print');

function kd_news($atts){
	
	//параметр к шорткоду (Покупателям, Новости)
	extract( shortcode_atts( array(
        'country' => ''
    ), $atts ) );
	
	global $wpdb;
	$a.="<div class='allnews_admin_div'>";
	$sql = "SELECT * FROM gi_userpanel_news WHERE countries = '$country' OR countries = '' ORDER BY newid DESC LIMIT 15";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$myheader = $row->myheader;
		$description = $row->description;
		$buttontext = $row->button_text;
		$mylink = $row->mylink;
		$mydate = $row->mydate;
		
		if(!empty($mylink) and !empty($buttontext)){
			$mybutton = "<a href='$mylink' target='_blank'><button class='myBtn myBtn_news_admin'>$buttontext</button></a>";
		}
		else $mybutton = "";
		
		$imagelink = $row->imagelink;
		$a.="
		<div class='onenew_admin_div'>
			<div class='news_image_admin' style='background:url($imagelink) no-repeat; background-size:cover; background-position:center;'></div>
			<div class='news_content_admin'>
				<span class='news_header_admin'>$myheader</span>
				<hr style='margin-top:15px;'>
				<div class='newdate_admin_div'><i>$mydate</i></div>
				<div class='descrnews_admin_div'>
					$description
				</div>
				<div class='news_btn_admin_div'>
					$mybutton
				</div>
			</div>
			<div style='clear:both'></div>
		
		</div>";
	}
	$a.="</div>";
	
	$a.="<style>
	/*РАЗДЕЛ НОВОСТЕЙ*/
	.allnews_admin_div{width:98%; min-height:100px; background:white; text-align:center;}
	.onenew_admin_div{width:98%; min-width:300px; max-width:100%; margin:20px 0.5% 20px 0.5%; min-height:100px; display:inline-block; background:#f9f9f9; border:1px solid #dcdcdc; position:relative;}
	.news_image_admin{float:left;  width:400px; height:335px;}
	.news_content_admin{float:left; width:calc(100% - 450px); padding:25px; min-height:calc(300px - 35px);  height:max-content; text-align:left; margin-top:20px;}
	.news_header_admin{font-size:24px;}
	.descrnews_admin_div{z-index:10; position:relative; background:#f9f9f9; /*height:calc(100% - 80px);*/ overflow-y:auto;}
	.news_btn_admin_div{position:relative;}
	.myBtn_news_admin{padding:6px 15px; font-size:14px; margin-top:32px; color:white; background:#1e3350; border:none; cursor:pointer;}
	.newdate_admin_div{font-size:16px; position:absolute; right:20px; top:15px; color:grey;}
	@media screen and (max-width:700px){
		.news_image_admin{width:100%; float:none; margin-top:40px; background-size:contain !important;}
		.news_content_admin{width:calc(100% - 30px); float:none; text-align:center;}
		.news_btn_admin_div{position:relative;}
		.news_content_admin {height:auto; min-height:max-content;}
		.myBtn_news_admin{margin-top:30px;}
		.news_btn_admin_div {right:auto;}
	}	
	</style>";
	return $a;
}

function redact_news(){
	global $wpdb;
	$a="<h1>Редактировать новости</h1>";
	$a.="<div class='allnews_admin_div'>";
	$sql = "SELECT * FROM gi_userpanel_news ORDER BY newid DESC LIMIT 15";
	$result = $wpdb->get_results($sql);
	$by=''; $ru=''; $ua=''; $kz='';
	foreach ($result as $row){
		$newid = $row->newid;
		$mydate = $row->mydate;
		$myheader = $row->myheader;
		$description = $row->description;
		$button_text = $row->button_text;
		$mylink = $row->mylink;
		$imagelink = $row->imagelink;
		$countries = $row->countries;
		switch($countries){
			case 'by': $by = 'selected'; break;
			case 'ru': $ru = 'selected'; break;
			case 'ua': $ua = 'selected'; break;
			case 'kz': $kz = 'selected'; break;
			default: $by=''; $ru=''; $ua=''; $kz='';
		}
		$select = "
		<select name='countries'>
			<option value=''>Все страны</option>
			<option value='by' $by>Беларусь</option>
			<option value='ru' $ru>Россия</option>
			<option value='ua' $ua>Украина</option>
			<option value='kz' $kz>Казахстан</option>
		</select>
		";
		//$description = str_replace("<br /><br />", "<br />", $description);
		//$description = str_replace("<br />", "\n", $description);
		$a.="
		<div class='onenew_admin_div'>
			<div class='news_image_admin' style='background:url($imagelink) no-repeat; background-size:cover; background-position:center;'></div>
			<div class='news_content_admin'>
				<form method='POST'>
					Дата <br>
					<textarea name='mydate' style='width:100%;'>$mydate</textarea><br>
					$select <br>
					Заголовок:<br>
					<textarea name='myheader' style='width:100%;'>$myheader</textarea><br>
					Описание: <br>
					<textarea name='description' style='width:100%; min-height:200px;'>$description</textarea><br>
					Текст кнопки:<br>
					<textarea name='button_text' style='width:100%;'>$button_text</textarea><br>
					Ссылка на страницу какую-нибудь<br>
					<textarea name='mylink' style='width:100%;'>$mylink</textarea><br>
					Ссылка на картинку (из галереи)<br>
					<textarea name='imagelink' style='width:100%;'>$imagelink</textarea>
					<button name='redact' value='$newid' style='cursor:pointer; background:green; padding:15px 30px; border:none; color:white; margin:20px 20px 20px 0;'>Редактировать</button>
					<button name='delete' value='$newid' style='cursor:pointer; background:red; padding:15px 30px; border:none; color:white; margin:20px 20px 20px 0;'>Удалить</button>
				</form>
			</div>
			<div style='clear:both'></div>
		
		</div>	
		";
	}
	$a.="</div>";
	$a.="<style>
	/*РАЗДЕЛ НОВОСТЕЙ*/
	.allnews_admin_div{width:98%; min-height:100px; background:white; text-align:center;}
	.onenew_admin_div{width:98%; min-width:300px; max-width:100%; margin:20px 0.5% 20px 0.5%; min-height:100px; display:inline-block; background:#f9f9f9; border:1px solid #dcdcdc; position:relative;}
	.news_image_admin{float:left;  width:400px; height:335px;}
	.news_content_admin{float:left; width:calc(100% - 450px); padding:25px; min-height:calc(300px - 35px); text-align:left; margin-top:20px;}
	.news_header_admin{font-size:24px;}
	.descrnews_admin_div{z-index:10; position:relative; background:#f9f9f9; /*height:calc(100% - 80px);*/ overflow-y:auto;}
	.news_btn_admin_div{position:relative;}
	.myBtn_news_admin{padding:6px 15px; font-size:14px; margin-top:32px; color:white; background:#1e3350; border:none; cursor:pointer;}
	.newdate_admin_div{font-size:16px; position:absolute; right:20px; top:15px; color:grey;}
	@media screen and (max-width:700px){
		.news_image_admin{width:100%; float:none; margin-top:40px; background-size:contain !important;}
		.news_content_admin{width:calc(100% - 30px); float:none; text-align:center;}
		.news_btn_admin_div{position:relative;}
		.news_content_admin {height:auto; min-height:max-content;}
		.myBtn_news_admin{margin-top:30px;}
		.news_btn_admin_div {right:auto;}
	}	
	</style>";	
	echo $a;
	
	if(isset($_POST['redact'])){
		$id = $_POST['redact'];
		$newmydate = $_POST['mydate'];
		$newmyheader = $_POST['myheader'];
		$newdescription = $_POST['description'];
		$newbutton_text = $_POST['button_text'];
		$newmylink = $_POST['mylink'];
		$newimagelink = $_POST['imagelink'];
		$countries = $_POST['countries'];
		$sqlredact = "UPDATE `gi_userpanel_news` SET `mydate` = '$newmydate', `myheader` = '$newmyheader', `description` = '$newdescription', `button_text` = '$newbutton_text', `mylink` = '$newmylink', `imagelink` = '$newimagelink', `countries`='$countries' WHERE `newid` = '$id'";
		$resultredact = $wpdb->get_results($sqlredact);
		echo "<h1>Правки внесены!</h1>";
		echo "<script>window.location.reload();</script>";
	}
	if(isset($_POST['delete'])){
		$id = $_POST['delete'];
		$sqldelete = "DELETE FROM gi_userpanel_news WHERE newid='$id'";
		$resultdelete = $wpdb->get_results($sqldelete);
	}
}

function news_print(){
	
	global $user_role;
	if($user_role == 'designer_architect'){
		header("Location: /designer-conditions/");
	}
	global $lang_adm;
	$userID = get_current_user_id();
	$userCountry = get_user_meta($userID, 'country', true);
	
	if($userID != 1){
		switch ($userCountry){
			case 'Беларусь': $country_key = 'by';break;
			case 'Россия': $country_key = 'ru';break;
			case 'Украина': $country_key = 'ua';break;
			case 'Україна': $country_key = 'ua';break;
			case 'Казахстан': $country_key = 'kz';break;
			default: $country_key = '';
		}	
	}
	else {
		$country_key = '';
	}
	global $wpdb;

	$a="<div class='allnews_admin_div'>
	<h1 align='left'>Новости фабрики</h1>
	";
	$sql = "SELECT * FROM gi_userpanel_news WHERE countries LIKE '%$country_key%' or countries = '' ORDER BY newid DESC LIMIT 15";
	$result = $wpdb->get_results($sql);
	foreach ($result as $row){
		$myheader = $row->myheader;
		$description = $row->description;
		$buttontext = $row->button_text;
		$mylink = $row->mylink;
		$mydate = $row->mydate;
		
		if(!empty($mylink) and !empty($buttontext)){
			$mybutton = "<a href='$mylink' target='_blank'><button class='myBtn classic_btn mynewsbtn'>$buttontext</button></a>";
		}
		else $mybutton = "";
		$description = str_replace("<br><br>", "<br>", $description);
		$description = str_replace("<br /><br />", "<br>", $description);
		$imagelink = $row->imagelink;

		$a.="
		<div class='mynews_div'>
			<div class='mynews_image' style='background:url($imagelink) no-repeat; background-size:cover; background-position:center;'></div>
			<div class='mynews_content'>
				<h3>$myheader</h3>
				<span class='mynews_date'>$mydate</span>
				<span class='mynews_descr'>$description</span>
				
				<div class='news_btn_admin_div'>
					$mybutton
				</div>
			</div>
		</div>
		";
	}
	$a.="</div>";
	
	$a.="<style>
	/*РАЗДЕЛ НОВОСТЕЙ*/
	.entry-content{background:#fff;}
	.allnews_admin_div{width:100%; min-height:100px; background:white; text-align:center;}
	.onenew_admin_div{width:100%; min-width:300px; max-width:100%; margin:20px 0.5% 20px 0.5%; min-height:100px; display:inline-block; background:#f9f9f9; border:1px solid #dcdcdc; position:relative;}
	.news_image_admin{float:left;  width:380px; height:335px;}
	.news_content_admin{float:left; width:calc(100% - 450px); padding:25px; min-height:calc(300px - 35px); height:max-content; text-align:left; margin-top:20px;}
	.news_header_admin{font-size:24px;}
	.descrnews_admin_div{z-index:10; position:relative; background:#f9f9f9; /*height:calc(100% - 80px);*/ overflow-y:auto;}
	.news_btn_admin_div{position:relative;}
	.myBtn_news_admin{padding:15px 25px; font-size:14px; margin-top:32px; color:white; background:#1e3350; border:none; cursor:pointer;}
	.newdate_admin_div{font-size:16px; position:absolute; right:20px; top:15px; color:grey;}
	@media screen and (max-width:700px){
		.news_image_admin{width:100%; float:none; margin-top:40px; background-size:contain !important;}
		.news_content_admin{width:calc(100% - 30px); float:none; text-align:center;}
		.news_btn_admin_div{position:relative;}
		.news_content_admin {height:auto; min-height:max-content;}
		.myBtn_news_admin{margin-top:30px;}
		.news_btn_admin_div {right:auto;}
	}	
	</style>";
	return $a;
}

function add_news(){
	global $wpdb;
	$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
	$lat=array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
	
	$a="<h1>Добавить новость</h1>";
	
	$a.="
	<form method='POST' enctype='multipart/form-data'>
		<input type='text' name='myheader' placeholder='Заголовок' required style='width:500px; max-width:100%;'/><span style='font-size:22px; color:red;'>*</span><br>
		<input type='text' name='mydate' placeholder='Дата' style='width:500px; max-width:100%;' /><br>
		<input type='text' name='buttontext' placeholder='Текст кнопки' style='width:500px; max-width:100%;' /><br>
		<input type='text' name='mylink' placeholder='Ссылка новости' style='width:500px; max-width:100%;'/><br>
		<input type='text' name='imagelink' placeholder='Ссылка на картинку' style='width:500px; max-width:100%;' required/><span style='font-size:22px; color:red;'>*</span><br>
		<select name='country'>
			<option value=''>Все страны</option>
			<option value='by'>Беларусь</option>
			<option value='ru'>Россия</option>
			<option value='ua'>Украина</option>
			<option value='kz'>Казахстан</option>
		</select><br> 
		Описание новости<span style='font-size:22px; color:red;'>*</span> <br> 
		<i> &ltb&gt&lt/b&gt - тег для жирного текста. &lti&gt&lt/i&gt - курсив. &lth1&gt - &lth5&gt - Заголовки </i> <br><br>
		<textarea name='descr' wrap='soft' placeholder='Описание' required style='width:500px; max-width:100%; height:200px;'></textarea><br>
		<input type='submit' class='myBtn' name='add_news' Value='Добавить новость'/>
	</form>
	";
	
	if(isset($_POST['add_news'])){
		$myheader = $_POST['myheader'];
		$buttontext = $_POST['buttontext'];
		$mylink = $_POST['mylink'];
		$descr = $_POST['descr'];
		$country = $_POST['country'];
		$header_eng = str_replace($rus, $lat, $myheader);
		$dateforname = date('H') . date('i') . date('s');
		$descr = nl2br($descr);
		$button_text = '';


		$imagelink = $_POST['imagelink']; 
		if($_POST['mydate']){
			$mydate = $_POST['mydate'];
		}
		else {$mydate = date("d.m.Y");}
		//Вносим изменения в БД
					
		$sql = "INSERT INTO `gi_userpanel_news` (`mydate`, `myheader`, `description`, `button_text`, `mylink`, `imagelink`, `countries`) 
				VALUES ('$mydate', '$myheader', '$descr', '$button_text', '$mylink', '$imagelink', '$country');";
		$result = $wpdb->get_results($sql);
				
		echo "<h1>Новость добавлена!</h1>";
		//echo "<script>window.location.reload();</script>";
			
	}
	echo $a;
}


function byers_grid($atts){
	
	//параметр к шорткоду (Покупателям, Новости)
	extract( shortcode_atts( array(
        'rubric' => ''
    ), $atts ) );
		
	global $wpdb;
	//Получает ID рубрики
	$sqlterm = "SELECT term_id FROM wp_terms WHERE name='$rubric'";
	$resultterm = $wpdb->get_results($sqlterm);
	foreach ($resultterm as $rowterm){
		$term_id = $rowterm->term_id;
	}
	$a="<div class='byer_grid'>";
	//Получаем ID всех постов в рубрике с ID $term_id
	$sqlpostid = "SELECT object_id FROM wp_term_relationships WHERE term_taxonomy_id='$term_id' ORDER BY object_id DESC";
	$resultpostid = $wpdb->get_results($sqlpostid);
	foreach($resultpostid as $rowpostid){
		$post_id = $rowpostid->object_id;
		$post_info = get_post($post_id);
		$title = $post_info->post_title;
		$link = get_permalink($post_id);
		$post_date_main = get_the_time('d.m.Y', $post_id);
		$image = get_the_post_thumbnail_url( $post_id, 'large' );
		$a.="
		<a href='$link' style='color:inherit;'>
		<div class='byer_grid_div'>
			<div class='byer_grid_image' style='background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
			<div class='byer_grid_header_main'>
				<span class='byer_grid_title'>$title </span>
				<span class='byer_grid_date'>$post_date_main</span>
			</div>
			<!----<button class='byer_grid_btn'>Читать...</button>---->
		</div>
		</a>
		";
	}
	$a.="</div>";

	return $a;
}


function my_info_grid(){
	global $wpdb;
	echo "<h1>Записи в разделе \"Покупателям\"</h1>";
	
	
	$a="
	<form method='POST'>
		<h2>Добавить запись в раздел:</h2>
		Введите ссылку на новость:<br>
		<input name='newLink'><br>
		Введите рейтинг для новости:<br>
		<input name='newRank'><br>
		<input type='submit' name='addLink' value='Добавить'><br><br>
	</form>
	";
	if (isset($_POST['addLink'])){
		if (!empty($_POST['newLink'])){
			$newLink = trim($_POST['newLink']);
			$newRank = trim($_POST['newRank']);
			$postid = url_to_postid( $newLink );
			$post_info = get_post($postid);
			$title = $post_info->post_title;
			$image = get_the_post_thumbnail( $postid, 'large' );
			$sqlAdd = "INSERT INTO `gi_info_grid` (`my_link`, `name`, `rank`) VALUES ('$newLink', '$title', '$newRank')";
			$resultAdd = $wpdb->get_results($sqlAdd);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
	}
	$a.="<h2>Статьи в списке:</h2>";

	$sql = "SELECT * FROM gi_info_grid ORDER BY `rank`";
	$result = $wpdb->get_results($sql);
	$a.="<div style='width:90%; '>";
	foreach ($result as $row){
		$link = $row-> my_link;
		$rank = $row-> rank;
		$header = $row->name;
		$newid = $row->newid;
		$rankId = "rank" . $newid;
		$a.="
		<form method='POST'>
			<div style='margin: 5px 0;'>
				
				<div style='width:300px; float:left; background:white; padding: 5px 10px; border-right:1px solid #dcdcdc; border-bottom:1px solid #dcdcdc;'>$header</div>
				<div style='width:300px; float:left; background:white; padding: 5px 10px; border-right:1px solid #dcdcdc; border-bottom:1px solid #dcdcdc;'><a href='$link' target='_blank'>$link</a></div>
				<input style='float:left; margin-left: 10px; border:1px solid #dcdcdc;' name='rank' value='$rank'/>
				<button name='changeRank' value='$newid' style='float:left; cursor:pointer; background:green; padding: 5px 15px; border:none; color:white; margin:0px 20px;'>Редактировать</button>
				<button name='deletePost' value='$newid' style='float:left; cursor:pointer; background:red; padding: 5px 15px; border:none; color:white; margin:0px'>Удалить</button>
				<div style='clear:both'></div>
			</div>
		</form>
		";
		

		if (isset($_POST['deletePost'])){
			$postId = $_POST['deletePost'];
			$sqlDelete = "DELETE FROM `gi_info_grid` WHERE newid='$postId'";
			$resultDelete = $wpdb->get_results($sqlDelete);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
		if (isset($_POST['changeRank'])){
			$postId = $_POST['changeRank'];
			$newRank = $_POST['rank'];
			$sqlRedact = "UPDATE `gi_info_grid` SET `rank` = '$newRank' WHERE newid = '$postId'";
			$resultRedact = $wpdb->get_results($sqlRedact);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
	}
	echo $a;
}

function news_info_grid(){
	global $wpdb;
	echo "<h1>Записи в разделе \"Фабрика > Новости\"</h1>";
	$a="
	<form method='POST'>
		<h2>Добавить запись в раздел:</h2>
		Введите ссылку на новость:<br>
		<input name='newLink'><br>
		Введите рейтинг для новости:<br>
		<input name='newRank'><br>
		<input type='submit' name='addLink' value='Добавить'><br><br>
	</form>
	";
	if (isset($_POST['addLink'])){
		if (!empty($_POST['newLink'])){
			$newLink = trim($_POST['newLink']);
			$newRank = trim($_POST['newRank']);
			$postid = url_to_postid( $newLink );
			$post_info = get_post($postid);
			$title = $post_info->post_title;
			$image = get_the_post_thumbnail( $postid, 'large' );
			$sqlAdd = "INSERT INTO `gi_news_grid` (`my_link`, `name`, `rank`) VALUES ('$newLink', '$title', '$newRank')";
			$resultAdd = $wpdb->get_results($sqlAdd);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
	}
	$a.="<h2>Статьи в списке:</h2>";

	$sql = "SELECT * FROM gi_news_grid ORDER BY `rank`";
	$result = $wpdb->get_results($sql);
	$a.="<div style='width:90%; '>";
	foreach ($result as $row){
		$link = $row-> my_link;
		$rank = $row-> rank;
		$header = $row->name;
		$newid = $row->newid;
		$rankId = "rank" . $newid;
		$a.="
		<form method='POST'>
			<div style='margin: 5px 0;'>
				
				<div style='width:300px; float:left; background:white; padding: 5px 10px; border-right:1px solid #dcdcdc; border-bottom:1px solid #dcdcdc;'>$header</div>
				<div style='width:300px; float:left; background:white; padding: 5px 10px; border-right:1px solid #dcdcdc; border-bottom:1px solid #dcdcdc;'><a href='$link' target='_blank'>$link</a></div>
				<input style='float:left; margin-left: 10px; border:1px solid #dcdcdc;' name='rank' value='$rank'/>
				<button name='changeRank' value='$newid' style='float:left; cursor:pointer; background:green; padding: 5px 15px; border:none; color:white; margin:0px 20px;'>Редактировать</button>
				<button name='deletePost' value='$newid' style='float:left; cursor:pointer; background:red; padding: 5px 15px; border:none; color:white; margin:0px'>Удалить</button>
				<div style='clear:both'></div>
			</div>
		</form>
		";
		if (isset($_POST['deletePost'])){
			$postId = $_POST['deletePost'];
			$sqlDelete = "DELETE FROM `gi_news_grid` WHERE newid='$postId'";
			$resultDelete = $wpdb->get_results($sqlDelete);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
		if (isset($_POST['changeRank'])){
			$postId = $_POST['changeRank'];
			$newRank = $_POST['rank'];
			$sqlRedact = "UPDATE `gi_news_grid` SET `rank` = '$newRank' WHERE newid = '$postId'";
			$resultRedact = $wpdb->get_results($sqlRedact);
			echo "Опеация выполнена";
			echo "<script>window.location.reload();</script>";
		}
	}
	echo $a;
}

function info_grid(){
	global $wpdb;
	$sql = "SELECT * FROM gi_info_grid ORDER BY `rank`";
	$result = $wpdb->get_results($sql);
	
	$a="<div class='byer_grid'>";	
	foreach ($result as $row){
		$link = $row-> my_link;
		$newid = $row->newid;
		$postid = url_to_postid( $link );
		$post_info = get_post($postid);
		
		$title = $post_info->post_title;
		$image = get_the_post_thumbnail_url( $postid, 'large' );
		
		$a.="
		<a href='$link' style='color:inherit;'>
		<div class='byer_grid_div'>
			<div class='byer_grid_image' style='background:url($image) no-repeat; background-size:cover; background-position:center;'></div>
			<div class='byer_grid_header'>$title</div>
			<!----<button class='byer_grid_btn'>Читать...</button>---->
		</div>
		</a>
		";
	}
	

	
	
	$a.="</div>";
	return $a;
}
?>