<?php
SetHeader(200);
$json = [];
//Fullscreen Footer Menü
$sql = "select id,baslik as name,title,
       case when id = 1 then '/' else '/'+url end  as url,siralama,sitemap,'sayfalar' as tablo
from sayfalar".UZANTI." where aktif=1 and menu=1
union select id,kisa_baslik".UZANTI." as kisa_baslik ,title".UZANTI." as title ,'/'+url".UZANTI." as url,siralama,1 as sitemap,'tip' as tablo from tip where aktif=1 and menu".UZANTI."=1
order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);


$array_map = [];
foreach ($rows as $key => $item) {


    if($item["id"]=="15" && $item["tablo"]=="sayfalar"){
        $sql = "select id,baslik".UZANTI." as name,title".UZANTI." as title,url".UZANTI." as url,1 as sitemap from tip where aktif=1  order by siralama asc";
    } elseif ($item["id"] == "8" && $item["tablo"]=="sayfalar") {
        $sql = "select id,baslik".UZANTI." as name,title".UZANTI." as title,url".UZANTI." as url,1 as sitemap from destinations where aktif=1 and favori".UZANTI."=1 order by siralama asc";
    }else if( $item["tablo"]=="sayfalar" &&  $item["id"]!="10"){
        $sql = "select id,baslik as name,title, url,sitemap from sayfalar".UZANTI." where aktif=1 and cat={$item["id"]} order by siralama asc";
    }
    $query = $db->prepare($sql);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    if($result && $item["id"]!="1" && ($item["tablo"]=="sayfalar" &&  $item["id"]!="10"))
        $item["items"] = $result;
    $array_map[$key] = $item;
}
$rows = $array_map;


$json["data"] = $rows;
$json["static"][0] = Page::GetById("3","/");
$json["static"][1] = Page::GetById("385","/");
$json["static"][2] = Page::GetById("319","/");

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