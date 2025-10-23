<?php
SetHeader(200);
$json = [];
$perPage=get("limit")?:18;
$page = get("page") ?: 1;
$start = (($perPage * $page) - $perPage) + 1;
$ExecuteArray=[];

$orderEk=  " order by ".(get("id") ? "(case when h.id=".get("id")." then 0 else 1 end) asc," : "")."  h.siralama,h.id asc ";

$countSelect = "select count(h.id) as totalCount ";
$normalSelect="SET LANGUAGE Turkish;select * from (select  ROW_NUMBER() OVER (".$orderEk.") AS row_num, h.id,h.ribbon,h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title,h.url".UZANTI." as url,
h.resim as image,h.baslik".UZANTI." as name,d2.baslik".UZANTI."+' / '+d.baslik".UZANTI." as destination,fiyatlar.*,ToC.Symbol ";
$sql = "
from homes h
inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat
inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
cross apply (
select
isnull((select min(fiyat/7) from sezonlar where site=".PRICE_SITE." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as minfiyat,
isnull((select max(fiyat/7) from sezonlar where site=".PRICE_SITE." and islem='emlak' and islem_id=h.id and not fiyat=0),0) as maxfiyat
) as fiyatlar

where h.aktif".UZANTI."=1 ";

$ExecuteArray["DefaultCurrencyId"]=1;
$ExecuteArray["RateId"]=Rate::GetLastRate();

$totalRecord =$db->prepare($countSelect.$sql);
$totalRecord->execute($ExecuteArray);
$totalRecord=$totalRecord->fetch(PDO::FETCH_ASSOC)["totalCount"];

$arr = $db->prepare($normalSelect.$sql." ) subquery
WHERE row_num BETWEEN $start AND ".(($start -1)+$perPage));
if(get("sqlx")){
    echo $countSelect.$sql;
    echo $normalSelect.$sql." ) subquery
WHERE row_num BETWEEN $start AND ".(($start -1)+$perPage);
    print_r($ExecuteArray);
    exit;
}
$arr->execute($ExecuteArray);
$json["result"] = $arr->fetchAll(PDO::FETCH_ASSOC);
$json["CurrentPage"]=$page;
$json["TotalRecord"]=$totalRecord;
$json["TotalPage"]=(($totalRecord - ($totalRecord % $perPage)) / $perPage) + ($totalRecord % $perPage>0 ? 1 :0);
echo json_encode($json);
