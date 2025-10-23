<?php
SetHeader(200);
$json = [];

$EntityId = get("EntityId");
$query = $db->prepare("select h.id,h.doviz as doviz,ToC.Symbol from homes h 
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId where h.id =  :id");
$query->execute(["id"=>$EntityId,"DefaultCurrencyId"=>DefaultCurrencyId]);
$home = $query->fetch(PDO::FETCH_ASSOC);

if($qsql["gavelExplodeOption"]=="0"){
    $explodeWhere=" and (isnull(ka.belgeSuresiTipi, 1) = 1 or (isnull(ka.belgeSuresiTipi, 1) = 2 and ('".$qsql["gavelExplodeDate"]."' >= convert(date, sezonlar.tarih1, 103) and  convert(date, getdate(), 103) <= '".$qsql["gavelExplodeDate"]."')))";
}

$verisql="
        select * from
            (select 
                    sondakikaGirisler=isnull(string_agg(convert(date,tarih1,104),','),''),
                    sondakikaCikislar=isnull(string_agg(convert(date,tarih2,104),','),''),
                    sondakikaGunler=isnull(string_agg(dbo.Fn_aratarihler2(convert(date,tarih1,104),convert(date,tarih2,104)) ,','),'') 
                    from sonDakika where convert(date,tarih2,104)>convert(date,getdate(),104) and site=".PRICE_SITE." and islem_id=".$EntityId.") as sonDakika,                     
            (select 
                    doluGirisler=isnull(string_agg(concat(year(tarih) ,'-' ,FORMAT(tarih,'MM'),'-', FORMAT(tarih,'dd')),','),''),
                    doluCikislar=isnull(string_agg(concat(year(tarih2) ,'-' ,FORMAT(tarih2,'MM'),'-', FORMAT(tarih2,'dd')),','),''),
                    doluGunler=isnull(string_agg(dbo.Fn_aratarihler2(tarih,tarih2) ,','),'') 
                    from dolu where convert(date,tarih2,104)>convert(date,getdate(),104) and durum=3 and emlak=".$EntityId.") as dolu,
            (select 
                    dolu_fakeGirisler=isnull(string_agg(concat(year(tarih) ,'-' ,FORMAT(tarih,'MM'),'-', FORMAT(tarih,'dd')),','),''),
                    dolu_fakeCikislar=isnull(string_agg(concat(year(tarih2) ,'-' ,FORMAT(tarih2,'MM'),'-', FORMAT(tarih2,'dd')),','),''),
                    dolu_fakeGunler=isnull(string_agg(dbo.Fn_aratarihler2(tarih,tarih2) ,','),'') 
                    from dolu_fake where convert(date,tarih2,104)>convert(date,getdate(),104) and durum=3 and emlak=".$EntityId.") as fake_dolu,
            (select 
                    odemeGirisler=isnull(string_agg(concat(year(tarih) ,'-' ,FORMAT(tarih,'MM'),'-', FORMAT(tarih,'dd')),','),''),
                    odemeCikislar=isnull(string_agg(concat(year(tarih2) ,'-' ,FORMAT(tarih2,'MM'),'-', FORMAT(tarih2,'dd')),','),''),
                    odemeGunler=isnull(string_agg(dbo.Fn_aratarihler2(tarih,tarih2) ,','),''),
                    odemeSaatler=isnull(string_agg(concat(REPLICATE(concat(datediff(HOUR,convert(datetime,getdate(),104),convert(datetime,saat,104)),','),datediff(day,convert(date,tarih,104),convert(date,tarih2,104))),datediff(HOUR,convert(datetime,getdate(),104),convert(datetime,saat,104))),','),'') 
                    from dolu left join kayitlar on kayitlar.id=dolu.kayitid where convert(date,tarih2,104)>convert(date,getdate(),104) and durum=1 and emlak=".$EntityId.") as odeme,
        (select isnull(( SELECT 
indirimler = isnull(STRING_AGG(
        CONCAT(
            REPLACE(dbo.Fn_aratarihler2(DATEADD(day, -1,i.tarih1), dateadd(day, 1, i.tarih2)), ',',  '|'+CAST(i.oran AS VARCHAR)+',' ), 
            '|', 
            i.oran
        ), ','
    ),'')
FROM indirimler i
WHERE i.tarih2 > GETDATE()
  AND GETDATE() BETWEEN i.showDate1 AND i.showDate2 
  AND i.emlak = ".$EntityId."
GROUP BY i.emlak
                    ),'') as indirimler)     as indirimler,                        
            (select 
                    isnull((SELECT r.baslik,
                    CONVERT(VARCHAR, r.date1, 104) AS date1,
                    CONVERT(VARCHAR, r.date2, 104) AS date2,
                    (SELECT CONVERT(VARCHAR, r.date1, 104) as date1,
                        CONVERT(VARCHAR, r.date2, 104) as date2,
                        CONVERT(VARCHAR, ruletypes.id) as id,
                        rulesruletypes.[value]  as [value] 
                        FROM rulesruletypes INNER JOIN ruletypes ON ruletypes.id = rulesruletypes.ruletypes WHERE  rulesId = r.id for json path) AS maddeler 
                        FROM   ruleshomes rh INNER JOIN rules r ON r.id = rh.rulesId WHERE  r.isactive = 1 AND rh.homesId = ".$EntityId." for json path),'') as kurallar ) as kurallar,
            (select  
                    isnull(string_agg( 
                        cast(concat(year(convert(date,tarih1,104)) ,'-' ,format(convert(date,tarih1,104),'MM'),'-', format(convert(date,tarih1,104),'dd'),',', 
                        dbo.Fn_aratarihler2(convert(date,tarih1,104),convert(date,tarih2,104)),
                        ',',year(convert(date,tarih2,104)) ,'-' ,format(convert(date,tarih2,104),'MM'),'-', format(convert(date,tarih2,104),'dd')) as nvarchar(max)),','),'') as fiyatlarTarihler,
                    isnull(string_agg(cast(REPLICATE(convert(nvarchar,(cast((isnull(convert(float,fiyat*RD.Buy),0)/7) as decimal(10,0))))+',',datediff(day,convert(date,tarih1,104),convert(date,tarih2,104)))+convert(nvarchar,(cast(isnull(convert(float,fiyat*RD.Buy),0)/7 as decimal(10,0)))) as nvarchar(max)),','),'') as fiyatlar 
                    from sezonlar                 
                    left join kanun7464 ka on ka.homeId=".$home["id"]." 
                    inner join Finance.Currency FromC on FromC.CurrencyName='".$home["doviz"]."'
                    inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                    inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId 
                        and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId 
                where site=".PRICE_SITE." ".$explodeWhere." and  islem_id=".$EntityId." and islem='emlak' and convert(date,tarih2,104)>=convert(date,getdate(),104)) as fiyatlar";

$query = $db->prepare($verisql);
$query->execute([
    "RateId"=>Rate::GetLastRate(),
    "DefaultCurrencyId"=>DefaultCurrencyId
]);

$json["Symbol"]=$home["Symbol"];
$json["data"]=array_map(function($item){
    return (explode(",",$item));
},$query->fetch(PDO::FETCH_ASSOC));

echo json_encode($json);