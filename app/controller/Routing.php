<?php

SetHeader(200);

if(get("RoutingId")){

    $sql = "select * from Routing 
        inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
             where RoutingId=:RoutingId ";
    $query = $db->prepare($sql);

    $url = explode("/",get("url")?get("url"):"/");
    $query->execute([
        "RoutingId"=>get("RoutingId")
    ]);
    $json = $query->fetch(PDO::FETCH_ASSOC);
    $url=explode("/",$json['Slug']);
    $json=$json['RoutingController']::{$json['RoutingAction']}($json);
    $json["title"]=$json["title"]." | ".$qsql["siteadi"];
}else{
    $json["title"]="Routing Error";
}
echo json_encode($json);

