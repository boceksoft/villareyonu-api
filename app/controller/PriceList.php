<?php
SetHeader(200);
$uzanti="";
$DefaultCurrencyId=get("currency");
$CacheName = get("id")."_".$DefaultCurrencyId;
//$json = file_get_contents(realpath(".") . "/app/cache/Prices/" . $CacheName . ".json");
//if ($json) {
//    echo $json;
//    exit;
//}
$json = Product::PriceListNew(get("id"),$DefaultCurrencyId);


//if(!is_dir(PATH."/app/cache/Prices"))
//    mkdir(PATH."/app/cache/Prices");
//
//
//
//$myfile = fopen(realpath(".")."/app/cache/Prices/".get("id")."-".$DefaultCurrencyId.".json", "w");
//fwrite($myfile, json_encode($json));
//fclose($myfile);

echo json_encode($json);