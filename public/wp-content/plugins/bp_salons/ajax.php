<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
$weburl = setWebsiteUrl();
global $wpdb;
$thisid = $_POST['thisid'];
$sql = "SELECT * FROM gi_salons WHERE newid='$thisid'";
$result = $wpdb->get_results($sql);
$rus=array(' ', 'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',' ');
$latin =array('_', 'a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya','a','b','v','g','d','e','e','gh','z','i','y','k','l','m','n','o','p','r','s','t','u','f','h','c','ch','sh','sch','y','y','y','e','yu','ya',' ');
echo "<script>console.log(\"$sql\");</script>";
$a = '';
foreach($result as $row){
	
	$name = $row-> name;
	$city = $row-> city;
	$address = $row-> address;
	$latlng = $row-> lat_lng;
	$phones = $row-> phones;
	$mailbox = $row-> mailbox;
	$worktime = $row-> worktime;
	$samples = $row-> samples;
	$avatar = $row-> avatar;
	$firm = $row-> firm;
	$monobrend = $row-> monobrend;
	$rassrochka = $row-> rassrochka;
	$moreinfo = $row-> moreinfo;
	$vk = $row-> vk;
	$fb = $row-> fb;
	$insta = $row-> insta;
	$site = $row-> site;
	$moreinfo = $row-> moreinfo;
	$oprosnik = $row-> oprosnik;
	$opr_div = '';
	if(!empty($oprosnik)){
		$opr_div = "
		<a href='$oprosnik' target='_blank'>
			<button style='background:#1e3350; color:#fff; padding:16px 32px; text-align:center; border:none; margin-top:16px;'>
				Рассчитать кухню тут!
			</button>
		</a>";
	}
	
	list($lat,$lng)= explode ("_", $latlng);
	
	$samples = str_replace(" ", "", $samples);
	$samples_arr = explode (",", $samples);
	$new_samples_arr = array();
	foreach ($samples_arr as $num => $kitchenname){
		$kitchenname_eng = str_replace($rus, $latin, $kitchenname);
		$kitchenname = "<a href='/kuhni/$kitchenname_eng/' target='_blank' style='color:#e78c68; text-decoration:none;'>$kitchenname</a>";
		array_push($new_samples_arr, $kitchenname);
	}
	$samples = implode(", ", $new_samples_arr);
	$emblems = '';
	if($firm == 'yes'){
		$emblems .="
		<div style='margin:16px 0;'>
			<div class='emblem' style='width:40px; height:40px; border-radius:50%; display:inline-block; vertical-align:top; background:url(/wp-content/uploads/2019/12/medal.png) no-repeat; background-size:cover; background-position:center;'></div>
			<div style='display:inline-block; margin-left:8px; line-height:40px; vertical-align:top; font-size:18px; font-weight:600;'>—   Фирменный салон</div>
		</div>
		";
	}
	elseif ($firm !== 'yes' and $monobrend == 'yes'){
		$emblems .="
		<div style='margin:16px 0;'>
			<div class='emblem' style='width:40px; height:40px; border-radius:50%; display:inline-block; vertical-align:top; background:url(/wp-content/uploads/2020/01/medal2.png) no-repeat; background-size:cover; background-position:center;'></div>
			<div style='display:inline-block; margin-left:8px; line-height:40px; vertical-align:top; font-size:18px; font-weight:600;'>—   Монобрендовый салон</div>
		</div>
		";
	}
	else $emplems = "";
	
	
	if($rassrochka == 'yes'){
		$emblems .="
		<div style='margin:16px 0;'>
			<div class='emblem' style='width:40px; height:40px; border-radius:50%; display:inline-block; vertical-align:top; background:url(/wp-content/uploads/2020/08/rassr.png) no-repeat; background-size:cover; background-position:center;'></div>
			<div style='display:inline-block; margin-left:8px; line-height:40px; vertical-align:top; font-size:18px; font-weight:600;'>—   Есть рассрочка</div>
		</div>
		";
	}
	
	if(!isset($vk) and !isset($fb) and !isset($insta) and !isset($site) ){
		$socials = "Не представлен :(";
	}
	else {
		$socials = "<div>";
		if(!empty($vk)){
			$socials .= "
			<div style='display:inline-block; vertical-align:top; margin:0;'>
				<a href='$vk' target='_blank'><div class='vk_icon'></div></a>
			</div>
			";
		}
		else $socials .="";
		if(!empty($fb)){
			$socials .= "
			<div style='display:inline-block; vertical-align:top; margin:0;'>
				<a href='$fb' target='_blank'><div class='fb_icon'></div></a>
			</div>
			";
		}
		if(!empty($insta)){
			$socials .= "
			<div style='display:inline-block; vertical-align:top; margin:0;'>
				<a href='$insta' target='_blank'><div class='insta_icon'></div></a>
			</div>
			";
		}
		if(isset($site)){
			$socials .= "
			<div style='margin:16px 8px;'>
				<a href='$site' target='_blank' style='color:#e78c68'>$site</a>
			</div>
			";
		}
		$socials .="</div>";
	}
	
	$a.="<h1 style='margin:32px 0 !important;'>Салон $name</h1>";
	$a.="
	<div class='somesalon_map_and_ava_grid' style='width:100%; margin-bottom:32px; display:grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: max-content; gap: 32px;'>
		<div class='somesalon_ava_div'>
			<img id='img' class='salonimage' src='$weburl/wp-content/uploads/avatars_of_salons/images/$avatar' style='width:100%; margin-bottom:32px;'/>
			<div class='info1'>
				<p style='font-size:18px;'><b> $city, $address </b></p>
				<p><i style='color:#e78c68;'>$moreinfo</i></p>
				<p> <a href='tel:$phones'>$phones </a></p>
				<!-- <p> $mailbox </p> -->
				<!-- <p> $socials </p> -->
			</div>
		</div>
		<div class='somesalon_map_div' style='margin-right'>
			<iframe id='salonmap' width='100%' height='300px' style='margin-bottom:32px;' src='https://maps.google.com/maps?q=$lat,$lng&hl=ru&z=14&amp;output=embed'></iframe>
			<p> $worktime </p>
			<p> Образцы в наличии: $samples </p>
			
		</div>
	</div>
	
	$emblems
	";
	
}

$a.="
<script>
	var imageheight = jQuery('.salonimage').height();
	
	jQuery('#salonmap').css('height', imageheight);
	
</script>
<style>
#closebutton {left:10px; top:10px; width:max-content; height:max-content;}
@media screen and (max-width:600px){
	.somesalon_map_and_ava_grid {grid-template-columns: repeat(1, 1fr) !important;}
}
</style>
";
echo $a;
?>
