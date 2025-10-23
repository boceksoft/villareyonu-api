<?php

SetHeader(200);

$sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where lower(Slug) Collate SQL_Latin1_General_CP1254_CS_AS=lower(:Slug)  and Routing.Site=".SITE;
$query = $db->prepare($sql);

$url = array_values(explode("/",get("url")=="/"?"/":ltrim(get("url"),"/")));

$query->execute([
    "Slug"=>$url[0] ? $url[0] : "/"
]);
$json = $query->fetch(PDO::FETCH_ASSOC);


if (!empty($url[1]) && $json && is_numeric($url[1]) === false) {
    $sql = "select * from Routing 
    inner join RoutingType on RoutingType.RoutingTypeId=Routing.RoutingTypeId 
         where lower(Slug) Collate SQL_Latin1_General_CP1254_CS_AS=LOWER(:Slug) and Routing.Site=" . SITE;
    $query = $db->prepare($sql);
    $query->execute([
        "Slug" => ($url[1])
    ]);
    $json = $query->fetch(PDO::FETCH_ASSOC);


    if($json && is_numeric($url[2])){
        $json["Page"]=$url[2];
    }
}else if ($json && !empty($url[1])){
    if (is_numeric($url[1])) {
        $json["Page"] = $url[1];
    }
}


$query = $db->prepare("select * from (select eski_link,yeni_link,turu_code from yonlendirme where aktif=1
           union
           select '/'+originalLink,redirectTo,301 from redirects) as a
where a.eski_link=:eski_link");
$query->execute(["eski_link"=>get("url")]);
$redirect = $query->fetch(PDO::FETCH_ASSOC);
if($redirect){
    $json=["redirect"=>$redirect];
}

echo json_encode($json);

