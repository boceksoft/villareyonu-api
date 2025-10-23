<?php

SetHeader(200);

$sql = "select h.id,h.title,h.url,h.description,kisi,yatak_odasi,banyo,concat(d2.baslik,'/',d.baslik) as destination,h.onecikan as onecikan,
       h.fiyata_dahil
       from homes h
inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat
where h.aktif=1 and h.url=:url order by h.siralama asc";
$query = $db->prepare($sql);
$query->execute([
    "url"=>get("url")
]);
$json = $query->fetch(PDO::FETCH_ASSOC);
if ($json["onecikan"]!="")
    $json["onecikan"]=explode(",",$json["onecikan"]);
else
    $json["onecikan"]=null;

    $query = $db->prepare("select baslik
                from dahilOlanlar 
                where aktif=1 and ','+replace(replace(:fiyata_dahil,'#',','),' ','')+',' 
                      like '%,'+convert(varchar,id)+',%' order by siralama asc");
    $query->execute(["fiyata_dahil"=>$json["fiyata_dahil"]]);
    $json["fiyata_dahil"]=array_values($query->fetchAll(PDO::FETCH_COLUMN));

if (!$json){
    $json["error"]="Not Found";
}




echo json_encode($json);

