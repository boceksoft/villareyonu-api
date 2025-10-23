<?php

SetHeader(200);

$sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where RoutingId=:RoutingId ";
$query = $db->prepare($sql);

$url = explode("/",get("url")?get("url"):"/");
$query->execute([
    "RoutingId"=>get("RoutingId")
]);
$json = $query->fetch(PDO::FETCH_ASSOC);
$json["MetaData"]=true;
$url=explode("/",$json['Slug']);

$json= MetaData::GenerateMetaData($json);

echo json_encode($json);

