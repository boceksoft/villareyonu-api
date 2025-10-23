<?php

SetHeader(200);

$uzanti="";

$query = $db->prepare("
select top 10 d.baslik".$uzanti." as baslik,d.id,
replace(replace(replace(d.resim".$uzanti.",' ','-'),'ı','i'),N'ş','s') as resim,
d.url".$uzanti." as url,
d.title".$uzanti." as title from destinations d where d.favori=1 and  d.aktif=1  order by d.siralama asc
");
$query->execute();
$json["data"]=$query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($json);
