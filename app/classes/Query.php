<?php

class Query
{
    private $query;
    private $exArr;
    private $orderBySql;
    private $top=0;

    public function __construct()
    {

    }


    public function setQuery($type, $getQuery = false){
        global $config;
        $uzanti="";
        if ($type=="count"){
            $this->query="select count(h.id) as total
                from homes h  
                inner join destinations d3 on d3.id=h.emlak_bolgesi 
                inner join destinations d2 on d2.id=d3.cat 
                inner join destinations d1 on d1.id=d2.cat 
left join kanun7464 kanun on kanun.homeId=h.id
                inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
                inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                inner join tip t on t.id=h.emlak_tipi 
                CROSS APPLY (
        SELECT 
            ISNULL(MIN(fiyat), 0) AS minfiyat,
            ISNULL(MAX(fiyat), 0) AS maxfiyat 
        FROM 
            sezonlar 
        WHERE 
            islem = 'emlak' 
            AND site = ".PRICE_SITE."
            AND islem_id = h.id 
            AND fiyat <> 0
    ) AS fiyatlar 
            where 
                h.aktif".UZANTI."=1 
                and t.aktif=1 
                and d3.aktif=1 
                and d2.aktif=1 
                and d1.aktif=1 and not fiyatlar.minfiyat=0 and isnull(kanun.gavel,0)=0 ";
            $this->exArr=[
                "RateId"=>Rate::GetLastRate(),
                "DefaultCurrencyId"=>DefaultCurrencyId
            ];
        }else if($type=="lastMinute"){
            $this->query="SET LANGUAGE Turkish;select sonDakika.*,h.id,ToC.Symbol,
       h.ribbon".UZANTI." as ribbon,
h.doviz as doviz,
h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title,h.kisa_icerik".UZANTI." as kisa_icerik ,
concat('/',h.url".UZANTI.",'?start=',convert(date,sonDakika.tarih1,104),'&end=',convert(date,sonDakika.tarih2,104)) as url,h.resim as image,
h.baslik".UZANTI." as name,d2.baslik as destination,
       Routing.RoutingId,yorumlar.*,
       cast(fiyatlar.sqfiyat*RD.Buy as decimal(12,0)) as sqfiyat, cast(fiyatlar.IndirimTutari * RD.Buy as decimal(12, 0))                                     as IndirimTutari,
Routing.EntityId
                from homes h  
                inner join destinations d3 on d3.id=h.emlak_bolgesi 
                inner join destinations d2 on d2.id=d3.cat 
                inner join destinations d1 on d1.id=d2.cat 
                inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
                inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                inner join tip t on t.id=h.emlak_tipi
                left join kanun7464 kanun on kanun.homeId=h.id
                outer apply (
    select cast(avg(cast(puan as decimal(10,5))) as decimal(10,1)) as rating,count(id) as reviews from dbo.defter where islm='emlak' and islm_id=h.id and onay=1
) as yorumlar  
                left join Routing on Routing.EntityId=h.id and Routing.RoutingTypeId='ProductDetail' and Site=".SITE." 
                cross apply(select top 1 id,replace(tarih1,'/','.') as tarih1,
                            replace(tarih2,'/','.') as tarih2,
                            cast(fiyat*RD.Buy as decimal(12,0)) as fiyat,
                            datediff(day,convert(date,tarih1,104),convert(date,tarih2,104)) as gece,
                            FORMAT (convert(date,tarih1,104), 'dd MMM') as gun1,
                            FORMAT (convert(date,tarih2,104), 'dd MMM') as gun2
                            from sonDakika where islem_id=h.id 
                            and convert(date,getdate(),104)<=convert(date,tarih1,104) and site=".PRICE_SITE."
                            order by convert(date,tarih1,104) ) as sonDakika 
                            
                cross apply(select ToplamTutar as sqfiyat,IndirimTutari from dbo.Fn_yenifiyathesapla_tablo(sondakika.tarih1,sondakika.tarih2,h.id,".PRICE_SITE.")) as fiyatlar
                            
            where 
                h.aktif".UZANTI."=1 and isnull(kanun.gavel,0)=0 ";
            $this->exArr=[
                "RateId"=>Rate::GetLastRate(),
                "DefaultCurrencyId"=>DefaultCurrencyId
            ];
        }else if ($type=="Product"){
            if ($this->top>0)
                $topStr = "top ".$this->top;

        $this->query="select $topStr h.id,cast(fiyatlar.minfiyat*RD.Buy as decimal(10,0)) as minfiyat,cast(fiyatlar.maxfiyat*RD.Buy as decimal(10,0))*RD.Buy as maxfiyat,ToC.Symbol,
       h.ribbon".UZANTI." as ribbon,
h.doviz as doviz,
h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title ,h.description".UZANTI." as description ,
concat('/',h.url".UZANTI.") as url,dbo.FnRandomSplit(h.resim,',') as image,
h.baslik".UZANTI." as name,d2.baslik as destination,yorumlar.*,d3.id as d3id
                from homes h 
                inner join destinations d3 on d3.id=h.emlak_bolgesi 
                inner join destinations d2 on d2.id=d3.cat 
                inner join destinations d1 on d1.id=d2.cat 
                inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
                inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                left join kanun7464 kanun on kanun.homeId=h.id
                inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                inner join tip t on t.id=h.emlak_tipi 
                outer apply (
    select cast(avg(cast(puan as decimal(10,5))) as decimal(10,1)) as rating,count(id) as reviews from dbo.defter where islm='emlak' and islm_id=h.id and onay=1
) as yorumlar  
                CROSS APPLY (
        SELECT 
            ISNULL(MIN(fiyat/7), 0) AS minfiyat, 
            ISNULL(MAX(fiyat/7), 0) AS maxfiyat 
        FROM 
            sezonlar 
        WHERE 
            islem = 'emlak' 
            AND site = ".PRICE_SITE."
            AND islem_id = h.id 
            AND fiyat <> 0
            AND convert(date, tarih2,104) >= convert(date, getdate(),105)
    ) AS fiyatlar  
            where 
                h.aktif".UZANTI."=1 
                and t.aktif=1 
                and d3.aktif=1 
                and d2.aktif=1 
                and d1.aktif=1 and not fiyatlar.minfiyat=0 and isnull(kanun.gavel,0)=0";
            $this->exArr=[
                "RateId"=>Rate::GetLastRate(),
                "DefaultCurrencyId"=>DefaultCurrencyId
            ];
            if ($getQuery){
                die($this->query);
            }
        }else if ($type=="CustomList"){
            if ($this->top>0)
                $topStr = "top ".$this->top;

            $this->query="select $topStr h.id,fiyatlar.*,ToC.Symbol,
       h.ribbon".UZANTI." as ribbon,
h.doviz as doviz,
h.kisi,h.yatak_odasi,h.banyo,h.title".UZANTI." as title ,
concat('/',h.url".UZANTI.") as url,dbo.FnRandomSplit(h.resim,',') as image,
h.baslik".UZANTI." as name,d2.baslik as destination
                from homes h  
                inner join destinations d3 on d3.id=h.emlak_bolgesi 
                inner join destinations d2 on d2.id=d3.cat 
                inner join destinations d1 on d1.id=d2.cat 
                inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
                inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                inner join tip t on t.id=h.emlak_tipi 
                left join kanun7464 kanun on kanun.homeId=h.id
                inner join CustomListHomes on CustomListHomes.HomeId=h.id
                inner join CustomList on CustomList.Id=CustomListHomes.ListId
                cross apply(select isnull((select min(fiyat/7) from sezonlar where islem='emlak' and site=1 and islem_id=h.id and convert(date, tarih2, 104) >= getdate() and not fiyat=0),0) as minfiyat,isnull((select max(fiyat/7) from sezonlar where islem='emlak' and site=1 and islem_id=h.id and convert(date, tarih2, 104) >= getdate() and not fiyat=0),0) as maxfiyat ) as fiyatlar 
            where 
                h.aktif".UZANTI."=1 
                and t.aktif=1 
                and d3.aktif=1 
                and d2.aktif=1 
                and d1.aktif=1 and not fiyatlar.minfiyat=0 and isnull(kanun.gavel,0)=0 ";
            $this->exArr=[
                "RateId"=>Rate::GetLastRate(),
                "DefaultCurrencyId"=>"1"
            ];
        }
    }

    public function addParam($str,$ex=false){
        $this->query.=" ".$str;
        if ($ex)
            $this->exArr=array_merge($this->exArr,$ex);
    }

    public function addOrderBy($param)
    {
        $this->query.=" order by ".$param;
    }

    public function Run(){
        global $db;
        $query = $db->prepare($this->query);
        $query->execute($this->exArr);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    public function setTop($top){
        $this->top=$top;
    }
    public function GetQuery(){
        return $this->query;
    }


}