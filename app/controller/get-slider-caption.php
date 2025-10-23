<?php
SetHeader(200);
$json = [];

$CacheName = "get-slider-caption";

$sql = "select filename,aciklama,aciklama2,aciklama3 from upload where islm='slider' and site=".SITE." order by sira";
$query = $db->prepare($sql);
$query->execute();
$json = $query->fetchAll(PDO::FETCH_ASSOC);



echo json_encode($json);

$myfile = fopen(realpath(".")."/app/cache/".$CacheName.".json", "w");
fwrite($myfile, ob_get_contents());
fclose($myfile);

