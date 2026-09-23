<?php
$img = $_POST['img'];
$img = str_replace(" ", "+", $_POST['img']);
$img = str_replace("data:image/png;base64,", "", $img);
$img = base64_decode($img);
file_put_contents('../../wp-content/plugins/water_depths/testimages2/img.png', $img);
echo "cimplete imge loading";

?>