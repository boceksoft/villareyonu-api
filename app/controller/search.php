<?php
SetHeader(200);
$json = [];

if (strlen(get("q"))<3)
    $json["error"]="Aramak için minumum 3 karakter giriniz.";


if(!$json["error"]){
    if($qsql["gavelSearchOption"]=="0"){
        $ek = " and isnull(ka.gavel, 0)=0 ";
    }
$query = $db->prepare("Select top 10 h.id,h.baslik,h.title,concat('/',h.url".UZANTI.") as url,d1.baslik+' / '+d2.baslik as bolge,dbo.FnRandomSplit(h.resim,',') as resim,
                     Routing.RoutingId,
h.evkodu from homes h INNER JOIN destinations d2 on d2.id=h.emlak_bolgesi 
        left join kanun7464 ka on ka.homeId=h.id
    INNER JOIN destinations d1 on d1.id=d2.cat 
    INNER JOIN destinations d0 on d0.id=d1.cat 
         left join Routing on Routing.EntityId=h.id and Routing.RoutingTypeId='ProductDetail' and Site=".SITE." 
         where h.aktif=1 ".$ek." and d2.aktif=1 and d1.aktif=1 and ((h.baslik) like :se COLLATE Turkish_CI_AS or h.baslik COLLATE Latin1_General_CI_AI LIKE :se2) order by (h.baslik) asc
");
$query->execute([
    "se"=>"%".(get("q"))."%",
    "se2"=>"%".(get("q"))."%"
]);
$json["data"]=$query->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($json);

//select string_agg(concat(h.baslik,'##',h.title,'##',h.url,'##',d1.baslik+' / '+d2.baslik,'##',+'thumbs/'+dbo.FnRandomSplit(h.resim,','),'##',h.evkodu),';/') as veriler from homes h INNER JOIN destinations d2 on d2.id=h.emlak_bolgesi INNER JOIN destinations d1 on d1.id=d2.cat INNER JOIN destinations d0 on d0.id=d1.cat where h.aktif=1 and d2.aktif=1 and d1.aktif=1
