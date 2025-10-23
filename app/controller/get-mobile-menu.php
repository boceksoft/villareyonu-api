<?php
SetHeader(200);
$json = [];

//Fullscreen Footer Menü
$sql = "select id,baslik as name,title,'/'+url as url from sayfalar where aktif=1 and menu=1 order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);


$array_map = [];
foreach ($rows as $key => $item) {


    if($item["id"]=="15"){
        $sql = "select id,baslik as name,title,url from tip where aktif=1  order by siralama asc";
    } elseif ($item["id"] == "8") {
        $sql = "select id,baslik as name,title,url from destinations where aktif=1 and favori=1 order by siralama asc";
    }else{
        $sql = "select id,baslik as name,title, url from sayfalar where aktif=1 and cat={$item["id"]} order by siralama asc";
    }
    $query = $db->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    if($result && $item["id"]!="1" )
        $item["items"] = $result;
    $array_map[$key] = $item;
}
$rows = $array_map;



$json["data"] = $rows;
//Fullscreen Footer Menü



if(1==2){
$rows = array_map(function ($item) use ($db) {
    if ($item["id"] == "15") {
        $sql = "select top 10 id,baslik as name,url from tip where aktif=1 and favori=1 order by siralama asc";
        $query = $db->prepare($sql);
        $query->execute();
        $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($item["id"] == "8") {
        $sql = "select id,baslik as name,url from destinations where aktif=1 and favori=1 order by siralama asc";
        $query = $db->prepare($sql);
        $query->execute();
        $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $sql = "select id,baslik as name,url from sayfalar where aktif=1 and cat={$item["id"]} order by siralama asc";
        $query = $db->prepare($sql);
        $query->execute();
        $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
    }
    return $item;
}, $rows);

}


echo json_encode($json);