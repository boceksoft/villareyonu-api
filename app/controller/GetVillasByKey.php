<?php
SetHeader(200);
$json = [];

//breadCrumb_kategori



$sql = "select top 10 h.id,h.ribbon".UZANTI." as ribbon,h.ribbon2".UZANTI." as ribbon2,h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title,'/'+h.url".UZANTI." as url,h.siralama,
       dbo.FnRandomSplit(h.resim,',') as image,h.baslik".UZANTI." as name,d2.baslik".UZANTI." as destination,fiyatlar.*,ToC.Symbol,d2.baslik".UZANTI." as ilce,
       Routing.RoutingId,CH.BookableDirectly,
Routing.EntityId,yorumlar.*
from homes h
inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat 
inner join destinations d3 on d3.id = d2.cat 
inner join tip t on t.id=h.emlak_tipi
    inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
left join KiralamaTakvimi.CalendarHomes CH on CH.homesId=h.id
left join kanun7464 kanun on kanun.homeId=h.id
outer apply (
    select cast(avg(cast(puan as decimal(10,5))) as decimal(10,1)) as rating,count(id) as reviews from dbo.defter where islm='emlak' and islm_id=h.id and onay=1
) as yorumlar
left join Routing on Routing.EntityId=h.id and Routing.RoutingTypeId='ProductDetail' and Site=".SITE."
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
 cross apply (
     select 
         isnull((select cast(min(fiyat/7)*RD.Buy as decimal(10,0)) as minfiyat from sezonlar where site=".PRICE_SITE." and islem='emlak' and islem_id=h.id and not fiyat=0 and convert(date, tarih2,
                                                              104) >= convert(date, getdate(),
                                                                                    105)),0) as minfiyat,
         isnull((select cast(max(fiyat/7)*RD.Buy as decimal(10,0)) as maxfiyat from sezonlar where site=".PRICE_SITE." and islem='emlak' and islem_id=h.id and not fiyat=0 and convert(date, tarih2,
                                                              104) >= convert(date, getdate(),
                                                                                    105)),0) as maxfiyat
 ) as fiyatlar
 
where h.aktif".UZANTI."=1 and isnull(kanun.gavel,0)=0  and t.aktif=1 and fiyatlar.minfiyat>0 and d.aktif=1 and d2.aktif=1 and d3.aktif=1  ";

if (get("key")=="vitrin"){
    $query = $db->prepare($sql." and h.vitrin".UZANTI."=1 order by h.siralama asc ");
    $query->execute([
        "DefaultCurrencyId"=>DefaultCurrencyId,
        "RateId"=>Rate::GetLastRate()
    ]);
    $json = $query->fetchAll(PDO::FETCH_ASSOC);
}else if (get("key")=="favori"){
    $query = $db->prepare($sql." and h.favori".UZANTI."=1 order by h.siralama asc ");
    $query->execute([
        "DefaultCurrencyId"=>DefaultCurrencyId,
        "RateId"=>Rate::GetLastRate()
    ]);
    $json = $query->fetchAll(PDO::FETCH_ASSOC);
}else if (get("key")=="firsat"){
    $query = $db->prepare($sql." and h.firsat".UZANTI."=1 order by h.siralama asc ");
    $query->execute([
        "DefaultCurrencyId"=>DefaultCurrencyId,
        "RateId"=>Rate::GetLastRate()
    ]);
    $json = $query->fetchAll(PDO::FETCH_ASSOC);
}else if (substr(get("key"),0,11)=="CustomList_"){
    $explode = explode("_",get("key"));
    $query = new Query();
    $query->setQuery("CustomList");
    //$query->setTop(10);
    $query->addParam("and  CustomList.PageId=".$explode[1]);
    $query->addParam(" order by CustomListHomes.Sequence ");
    $json = $query->run();
}else if (substr(get("key"),0,4)=="tip_"){
    $explode = explode("_",get("key"));
    $query = new Query();
    $query->setTop(10);
    $query->setQuery("Product");
    $query->addParam("and h.vitrin=1 and  h.kategori2=".$explode[1]);
    $json = $query->run();
}else if (substr(get("key"),0,8)=="similar_"){
    $explode = explode("_",get("key"));
    $query = new Query();
    $query->setTop(1);
    $query->setQuery("Product");
    $query->addParam("and h.id=:id",["id"=>$explode[1]]);
    $Product = $query->run()[0];


    $explode = explode("_",get("key"));
    $query = new Query();
    $query->setTop(20);
    $query->setQuery("Product");
    $query->addParam("and h.emlak_bolgesi=:d3id and (h.kisi between :kisiMin and :kisiMax ) and not h.id=:id",[
        "d3id"=> $Product["d3id"],
        "kisiMin"=> $Product["kisi"],
        "kisiMax"=> $Product["kisi"]+2,
        "id"=> $Product["id"]
    ]);
    $json = $query->run();

}else if (get("key")=="BookableDirectly") {
    $query = $db->prepare($sql . " and CH.BookableDirectly=1 order by h.siralama asc ");
    $query->execute([
        "DefaultCurrencyId" => DefaultCurrencyId,
        "RateId" => Rate::GetLastRate()
    ]);
    $json = $query->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($json);

