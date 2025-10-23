<?php

SetHeader(200);
$json = [];
global $config;

$query = $db->prepare("select h.id,ToC.Symbol,
       h.ribbon".UZANTI." as ribbon,
       h.ribbon2".UZANTI." as ribbon2,
h.doviz as doviz,
dv.promotion_date,
dv.discount_type,
CAST(dv.discount_value as int) as discount_value,
FORMAT(dv.discount_start_date, 'dd MMM', 'tr-TR') as discount_start_date,
FORMAT(dv.discount_end_date, 'dd MMM', 'tr-TR') as discount_end_date,
h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title ,
h.url".UZANTI." as url,dbo.FnRandomSplit(h.resim,',') as image,
h.baslik".UZANTI." as name,d1.baslik+' / '+d2.baslik as destination
                from homes h  
                inner join destinations d3 on d3.id=h.emlak_bolgesi 
                inner join destinations d2 on d2.id=d3.cat 
                inner join destinations d1 on d1.id=d2.cat 
                inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz".UZANTI."
                inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                inner join tip t on t.id=h.emlak_tipi 
                inner join daily_villa_promotions dv on dv.villa_id=h.id and dv.promotion_date=:promotion_date
            where 
                h.aktif".UZANTI."=1 
                and t.aktif=1 
                and d3.aktif=1 
                and d2.aktif=1 
                and d1.aktif=1 ");


$query->execute([
    "promotion_date"=>date("Y-m-d"),
    "RateId"=>Rate::GetLastRate(),
    "DefaultCurrencyId"=>DefaultCurrencyId
]);
$json["data"] = $query->fetch(PDO::FETCH_ASSOC);


echo json_encode($json);