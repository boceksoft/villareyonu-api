<?php

SetHeader(200);
$json = [];

//cross apply(select top 1 id,replace(tarih1,'/','.') as tarih1,replace(tarih2,'/','.') as tarih2,fiyat,datediff(day,convert(date,tarih1,104),convert(date,tarih2,104)) as gece,convert(varchar,day(convert(date,tarih1,104))) as gun,convert(varchar,datename(month,convert(date,tarih1,104))) as ay from sonDakika where islem_id=h.id and convert(date,getdate(),104)<=convert(date,tarih1,104) and site="&site&" order by convert(date,tarih1,104) ) as sonDakika

$json = Page::GetById(5,"/");
$query = new Query();
$query->setQuery("lastMinute");
if(get("EntityId"))
    $query->addParam("and h.id=".get("EntityId"));
$json["data"] = $query->Run();
echo json_encode($json);
