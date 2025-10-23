<?php

$perPage=10;
$page = get("page") ?: 1;
$start = ($perPage * $page) - $perPage;
$ExecuteArray=[];


$result = Page::GetById(10);

$countSelect = "select count(s.id) as totalCount ";
$normalSelect = "SET LANGUAGE Turkish;select s.baslik,s.title,'/".$result["url"]."/'+s.url as url,s.resim,s.kisa_icerik,FORMAT(s.tarih,'MMM dd yyyy') as tarih ";
$sql = " from sayfalar s where s.blog=1 and s.aktif=1 ";
// order by s.tarih desc

if (get("search")){
    $ExecuteArray["text"]="%".get("search")."%";
    $sql.=" and s.baslik like :text COLLATE Turkish_CI_AS ";
}

$totalRecord =$db->prepare($countSelect.$sql);
$totalRecord->execute($ExecuteArray);
$totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];

$arr = $db->prepare($normalSelect.$sql." order by s.tarih desc OFFSET $start ROWS FETCH NEXT $perPage ROWS ONLY");
$arr->execute($ExecuteArray);


$query = $db->prepare("SET LANGUAGE Turkish;select top 5 s.baslik,s.title,'/".$result["url"]."/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar s where s.aktif=1 and s.blog=1 order by s.tarih desc");
$query->execute([]);
$LastShared = $query->fetchAll(PDO::FETCH_ASSOC);

$query = $db->prepare("SET LANGUAGE Turkish;select top 5 s.baslik,s.title,'/".$result["url"]."/'+s.url as url,s.resim,FORMAT(s.tarih,'MMM dd yyyy') as tarih from sayfalar s where s.aktif=1 and s.blog=1 order by s.tarih desc");
$query->execute([]);
$Popular = $query->fetchAll(PDO::FETCH_ASSOC);


$result["BlogData"]=[
    "CurrentPage"=>$page,
    "TotalRecord"=>$totalRecord,
    "TotalPage"=>(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0),
    "result"=>$arr->fetchAll(PDO::FETCH_ASSOC),
    "LastShared"=>$LastShared,
    "Popular"=>$Popular
];


echo json_encode($result);