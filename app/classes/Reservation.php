<?php

class Reservation
{

    public static $Success = 3;

    public static function Index($Routing){
        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,url from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        $query = $db->prepare("select h.id,h.baslik".UZANTI." as baslik,h.title".UZANTI." as title,concat('/',h.url".UZANTI.") as url,dbo.FnRandomSplit(h.resim,',') as resim,kisi,yatak_odasi,banyo,h.depozito,
       concat(d2.baslik,'/',d.baslik) as destination,cast(h.hasar as decimal(10,0)) as hasar,ToC.Symbol,h.FirstPaymentTypeId,
       h.giris_saat,h.cikis_saat,h.fiyata_dahil
       from homes h 
           inner join destinations d on d.id = h.emlak_bolgesi
           inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
inner join destinations d2 on d2.id = d.cat where h.id=:id");
        $query->execute([
            "id"=>get("ProductId"),
            "DefaultCurrencyId"=>DefaultCurrencyId,
            "RateId"=>Rate::GetLastRate()
        ]);
        $result["Product"]=$query->fetch(PDO::FETCH_ASSOC);

        if($result["Product"]){
        $query = $db->prepare("select baslik".DILUZANTI." as baslik
                from dahilOlanlar 
                where aktif=1 and ','+replace(replace(:fiyata_dahil,'#',','),' ','')+',' 
                      like '%,'+convert(varchar,id)+',%' order by siralama asc");
        $query->execute(["fiyata_dahil"=>$result["Product"]["fiyata_dahil"]]);
            $result["Product"]["fiyata_dahil"]=array_values($query->fetchAll(PDO::FETCH_COLUMN));
            $result["sozlesme"]=Page::GetById(25,"/");
            $result["kvkk"]=Page::GetById(188,"/");
            $result["SuccessPage"]=Page::GetById(35,"/");


        }
        return $result;
    }

    public static function Success($Routing){
        global $db;

        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,url from sayfalar".UZANTI." where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        $ReservationId = idHash(get("q"),1);
        $query = $db->prepare("select id,musteri,email from kayitlar where  id = :id");
        $query->execute(["id"=>$ReservationId]);
        $r = $query->fetch(PDO::FETCH_ASSOC);

        $result["ReservationPage"]=Page::GetById(3,"/");

        if (!$r){
            $r["error"]=$ReservationId;
        }


        return array_merge($result,$r);
    }

    public static function ChangeStatus($ReservationNumber,$Status=0): bool
    {
        global $db;
        $query = $db->prepare("Update dbo.dolu set Durum=:Durum where kayitid=:kayitid");
        return $query->execute([
            "Durum"=>$Status,
            "kayitid"=>$ReservationNumber
        ]);
    }

    public static function ChangePaymentType($ReservationNumber,$Type){
        global $db;

        $query = $db->prepare("update kayitlar set odeme=:odeme where id=:id");
        return $query->execute([
            "odeme"=>$Type,
            "id"=>$ReservationNumber
        ]);


    }
}