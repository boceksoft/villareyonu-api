<?php

SetHeader(200);



$sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where Slug=:Slug and not Routing.RoutingTypeId='BlogDetail' ";
$query = $db->prepare($sql);

$url = explode("/",get("url")?get("url"):"/");
$query->execute([
    "Slug"=>$url[0] ? $url[0] : "/"
]);
$json = $query->fetch(PDO::FETCH_ASSOC);

if ($url[1] && $json && is_numeric($url[1])===false){
    $sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where Slug=:Slug and Routing.Site=1 ";
    $query = $db->prepare($sql);
    $query->execute([
        "Slug"=>$url[1]
    ]);
    $json = $query->fetch(PDO::FETCH_ASSOC);
}

if (!$json){
    $query = $db->prepare("select * from (select eski_link,yeni_link,turu_code from yonlendirme where aktif=1
               union
               select '/'+originalLink,redirectTo,301 from redirects) as a
where a.eski_link=:eski_link");
    $query->execute(["eski_link"=>"/".get("url")]);
    $redirect = $query->fetch(PDO::FETCH_ASSOC);
    if($redirect){
        $json=["redirect"=>$redirect];
    }else{
        $json["error"]="Not Found";
    }
}

echo json_encode($json);

