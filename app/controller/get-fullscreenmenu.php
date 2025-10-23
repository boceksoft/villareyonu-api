<?php
SetHeader(200);
$json = [];

//Fullscreen Footer Menü
$sql = "select id,baslik as name,title,url from sayfalar where aktif=1 and cat=2 order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$json["footerMenu"] = $query->fetchAll(PDO::FETCH_ASSOC);
//Fullscreen Footer Menü

//Fullscreen Butonlar
$sql = "select id,baslik as name,title,url, case when id = 319 then 1 else 0 end as [primary] from sayfalar where id in(12,319) order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$json["links"] = $query->fetchAll(PDO::FETCH_ASSOC);
//Fullscreen Butonlar

$sql = "select id,baslik as name,title,url from sayfalar where id in(320,317,14) order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$json["mainContent"] = $query->fetchAll(PDO::FETCH_ASSOC);

$sql = "select id,baslik as name from sayfalar where id in(15,8,2) order by siralama asc";
$query = $db->prepare($sql);
$query->execute();
$rows = $query->fetchAll(PDO::FETCH_ASSOC);

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


$json["menu"] = $rows;

$json["social"]["facebook"] = $qsql["facebook"];
$json["social"]["youtube"] = $qsql["youtube"];
$json["social"]["instagram"] = $qsql["instagram"];


echo json_encode($json);