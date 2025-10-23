<?php
SetHeader(200);
$json = [];

$Ids = [
    "tr" =>[
        'populer-bolgeler' => 8,
        'populer-tipler' => 15,
    ]
];

$query = $db->prepare("select id,baslik".UZANTI." as name,title,'/'+url as url from destinations where favori".UZANTI."=1  and aktif=1  order by siralama asc");
$query->execute();
$json["data"][] = [
    "id"=>8,
    "name"=>"Popüler Villa Kiralama Bölgeleri",
    "items"=>$query->fetchAll(PDO::FETCH_ASSOC)
];
$query = $db->prepare("select id,baslik" . UZANTI . " as name,title,'/'+url as url from tip where aktif=1 and favori" . UZANTI . "=1 order by siralama asc");
$query->execute();
$json["data"][] = [
    "id"=>15  ,
    "name"=>"Popüler Villa Tipleri",
    "items"=>$query->fetchAll(PDO::FETCH_ASSOC)
];


//Fullscreen Footer Menü
$sql = "select id,case when id =10 then N'Popüler İçeriklerimiz' else baslik end as name from sayfalar".UZANTI." where aktif=1 and id in(7,10,2) order by (case when id=7 then 0 else case when id=10 then 1 else case when id=10 then 2 else 3 end end end) asc";
$query = $db->prepare($sql);
$query->execute();

foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $item) {
    if($item["id"]=="7") {
        $query = new Query();
        $query->setTop(15);
        $query->setQuery("Product");
        $query->addParam("and  h.favori".UZANTI."=1");
        $item["items"] = $query->run();
        $json["data"][] = $item;
        continue;
    }else if($item["id"]=="10"){
        $sql = "select id,baslik as name,title,'/'+url as url,case when id=5 then 1 else 0 end as nofollow from sayfalar".UZANTI." where aktif=1 and blog=1 and favori=1 order by siralama asc";
    }else{
        $sql = "select id,baslik as name,title,'/'+url as url,case when id=5 then 1 else 0 end as nofollow from sayfalar".UZANTI." where aktif=1 and cat=".$item["id"]."  order by siralama asc";
    }
    $query = $db->prepare($sql);
    $query->execute();
    $item["items"] = $query->fetchAll(PDO::FETCH_ASSOC);
    $json["data"][] = $item;
}



$json["static"]["email"]=$qsql["sitemail"];
$json["static"]["adres"]=$qsql["adres"];
$json["static"]["telefon"]=$qsql["telefon"];
$json["static"]["whatsapp"]=$qsql["whatsapp"];

$json["social"]["facebook"]=$qsql["facebook"];
$json["social"]["youtube"]=$qsql["youtube"];
$json["social"]["twitter"]=$qsql["twitter"];
$json["social"]["instagram"]=$qsql["instagram"];

$json["footerMenu"]=Menu::GetItemsByMenuId(3);

$json["staticPages"]["Home"] = Page::GetById(1, "/");
$json["staticPages"]["Reservation"] = Page::GetById(3, "/");

if (SITE == 1)
    $json["staticPages"]["SearchPage"] = Page::GetById(318, "/");
else
    $json["staticPages"]["SearchPage"] = Page::GetById(34, "/");

if (SITE == 1)
    $json["staticPages"]["RentYourHouse"] = Page::GetById(294, "/");
else
    $json["staticPages"]["RentYourHouse"] = Page::GetById(33, "/");



echo json_encode($json);