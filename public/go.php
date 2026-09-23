<?php
require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');

// $url1 = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_kitchen_add/images/kitchen/";
// $dir = scandir($url1);
// foreach($dir as $num=>$dir2){
	// if($dir2 == '.' or $dir2 == '..') continue;
	// $dir3 = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_kitchen_add/images/kitchen/$dir2/";
	// $papka = scandir($dir3);
	// foreach($papka as $numer => $dir4){
		// if($dir4 == '.' or $dir4 =='..') continue;
		// $dir5 = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_kitchen_add/images/kitchen/$dir2/$dir4/";
		// $dir6 = scandir($dir5);
		// foreach($dir6 as $number => $dir7){
			// if($dir7 == '.' or $dir7 =='..') continue;
			// $dir7 = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/plugins/bp_kitchen_add/images/kitchen/$dir2/$dir4/$dir7/";
			// if(is_dir($dir7)){
				// $dir8 = scandir($dir7);
				// foreach($dir8 as $num8 => $file2){
					// if($file2 == '.' or $file2 =='..') continue;
					// if(stristr($file2, 'optipic-orig')){
						// $filelink = $dir7 . "$file2";
						// echo $filelink . "<br>";
						// unlink($filelink);
					// }
				// }
			// }
			
		// }
	// }
// }

$url = $_SERVER['DOCUMENT_ROOT'] . "/wp-content/themes/wp-diary/images/";
$dir = scandir($url);
foreach($dir as $num8 => $file2){
	if($file2 == '.' or $file2 =='..') continue;
	if(stristr($file2, 'optipic-orig')){
		$filelink = $url . "$file2";
		echo $filelink . "<br>";
		unlink($filelink);
	}
}
?>