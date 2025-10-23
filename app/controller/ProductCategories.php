<?php

SetHeader(200);
$uzanti="";

$query = $db->prepare("
select t.baslik".UZANTI." as baslik,t.id,
replace(replace(t.resim".UZANTI.",' ','-'),'ı','i') as resim,
t.url".UZANTI." as url,
t.title".UZANTI." as title,r.* from tip t
 inner join Routing r on r.EntityId=t.id and r.RoutingTypeId='ProductCategory'
 where t.aktif=1 and t.favori".UZANTI."=1  order by t.siralama asc
");
$query->execute();
$json["data"]=array_chunk(array_map(function($item){
    global $db;

    $row = new Query();
    if($item["id"]=="29"){
        $row->setQuery("count");
        $row->addParam("and isnull((select max(year(convert(date,tarih1,103))) from sezonlar where islem='emlak' and site=".PRICE_SITE." and islem_id=h.id and convert(date,tarih2,103)>=convert(date,getdate(),103) and year(convert(date,tarih1,103)) in (2024)),'')!=''");
    }else{
        $row->setQuery("count");
        $row->addParam("and (','+replace(h.kategori,' ','')+',' like '%,".$item["id"].",%' or h.emlak_tipi=".$item["id"].")");
    }
    $a = $row->Run()[0];
    $item["total"]=$a["total"];

    return $item;
},$query->fetchAll(PDO::FETCH_ASSOC)),2);

echo json_encode($json);
