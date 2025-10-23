<?php
SetHeader(200);
$json = [];
$DefaultCurrencyId=1;

//breadCrumb_kategori


$sql = "select top 10 h.id,h.ribbon,h.kisi,h.yatak_odasi,h.banyo,h.title,h.url,
       h.resim as image,h.baslik as name,d2.baslik+' / '+d.baslik as destination,fiyatlar.*,ToC.Symbol
from homes h
inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat 
    inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz".$uzanti."
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
 cross apply (
     select 
         isnull((select min(fiyat/7) from sezonlar where site=".$site." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as minfiyat,
         isnull((select max(fiyat/7) from sezonlar where site=".$site." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as maxfiyat
 ) as fiyatlar
 
where h.aktif=1 and h.vitrin=1  order by h.siralama ";

$query = $db->prepare($sql);
$query->execute([
    "DefaultCurrencyId"=>$DefaultCurrencyId,
    "RateId"=>Rate::GetLastRate()
]);

$json = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($json);

