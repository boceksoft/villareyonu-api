<?php
SetHeader(200);
$json = [];

//$CacheName = "FullScreenMenu";
//$json = file_get_contents(realpath(".") . "/app/cache/" . $CacheName . ".json");
//if ($json) {
//    echo $json;
//    exit;
//}

$json = FullScreenMenu::GetAll();
echo json_encode($json);

$myfile = fopen(realpath(".")."/app/cache/".$CacheName.".json", "w");
fwrite($myfile, ob_get_contents());
fclose($myfile);