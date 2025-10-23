<?php
SetHeader(200);
$json = [];

//Fullscreen Footer Menü
$sql = "select id,baslik as name from sayfalar where aktif=1 and id in(2,4,15) order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$json["data"] = array_map(function ($item){
    global $db;
    if($item["id"]=="15"){
        $sql = "select id,baslik as name,title,'/'+url as url from tip where aktif=1 and favori=1 order by siralama asc";
    }else{
        $sql = "select id,baslik as name,title,'/'+url as url,case when id=5 then 1 else 0 end as nofollow from sayfalar where aktif=1 and cat=".$item["id"]." order by siralama asc";
    }
    $query = $db->prepare($sql);
    $query->execute();
    $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);

    return $item;
},$query->fetchAll(PDO::FETCH_ASSOC));
//Fullscreen Footer Menü

echo json_encode($json);