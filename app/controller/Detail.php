<?php
SetHeader(200);
//http://api.villavillam.com.tr/Detail?url=/villa-kuzey-yildizi&start=2023-03-11&end=2023-03-17

$CacheName = $_GET["url"];
$json = file_get_contents(realpath(".") . "/app/cache/Detail" . $CacheName . ".json");
if ($json) {
    echo $json;
    exit;
}

$sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where Slug=:Slug and not Routing.RoutingTypeId='BlogDetail' ";
$query = $db->prepare($sql);

$url = explode("/",get("url")?ltrim(get("url"),"/"):"/");
$query->execute([
    "Slug"=>$url[0] ? $url[0] : "/"
]);
$json = $query->fetch(PDO::FETCH_ASSOC);
$json["result"]=Detail::Index($json);
echo json_encode($json);