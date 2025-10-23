<?php
use kornrunner\Blurhash\Blurhash;
class Detail
{
    public static function Index($Routing){


        global  $db;
        global $qsql;
        global $url;
        $sql = "select h.id,h.baslik".UZANTI." as baslik,concat('/',h.url".UZANTI.") as url,dbo.FnRandomSplit(h.resim,',') as resim,h.title".UZANTI." as title,left(h.description".UZANTI.",250) as description,kisi,yatak_odasi,banyo,concat(d3.baslik".UZANTI.",' / ',case when d2.baslik".UZANTI." = N'Kalkan' then N'Kaş' else d2.baslik".UZANTI." end) as destination,
       h.fiyata_dahil,h.icerik".UZANTI." as icerik,h.kisa_icerik".UZANTI." as kisa_icerik,h.giris_saat,h.cikis_saat,h.ribbon".UZANTI." as ribbon,h.ribbon2".UZANTI." as ribbon2,
       cast(isnull((select min(fiyat/7) from sezonlar where islem='emlak' and site=".PRICE_SITE." and islem_id=h.id and not fiyat=0 and convert(date,tarih2,104)>=convert(date,getdate(),104) ),0)*RD.Buy as int)  as minfiyat,
       ToC.Symbol,
       h.onecikan,
       h.hasar,
       h.depozito".UZANTI." as depozito,
       h.fiyat_notu".UZANTI." as fiyat_not,
       h.konum_not".UZANTI." as konum_not,
       h.video,
       h.takvimNotu,
       h.tam_korunakli_havuz,
       h.yuzme_havuzu,
       h.yuzme_havuzu_uzunluk,
       h.yuzme_havuzu_genislik,
       h.yuzme_havuzu_derinlik,
       h.cocuk_havuzu,
       h.cocuk_havuzu_uzunluk,
       h.cocuk_havuzu_genislik,
       h.cocuk_havuzu_derinlik,
       h.kapali_havuz, 
       h.kapali_havuz_uzunluk,
       h.kapali_havuz_genislik,
       h.kapali_havuz_derinlik,
       h.emlak_bolgesi,
       h.harita,
       h.enlem,
       h.boylam,
       h.aktif,
       ka.gavel as gavel,
       ka.gavelBelgeNo,
       ka.gavelBasvuruNo,       
       h.kurallar,
       Routing.RoutingId,
       yorumlar.*,
       h.ozellikler,
       last30dayrez=isnull((select count(dd2.id) from dolu dd2 where dd2.emlak=h.id and dd2.durum=3 and convert(date,dd2.createdOn,103) between dateadd(day,-30,convert(date,getdate(),103)) and convert(date,getdate(),103) ),0),
       d.baslik".UZANTI." as d3baslik,
       d2.baslik".UZANTI." as d2baslik,
       d3.baslik".UZANTI." as d1baslik,
       concat('destination_',d2.id) as SliderKey,
       case when len(h.canonical".UZANTI.") > 0 then  concat('{$qsql['domain']}/',h.canonical".UZANTI.") else '{$qsql['domain']}/".implode("/",$url)."' end  as canonical
       from homes h 
inner join destinations d on d.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d.cat
inner join destinations d3 on d3.id = d2.cat
left join kanun7464 ka on ka.homeId=h.id
inner join Finance.Currency FromC on FromC.CurrencyName=h.doviz
inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
left join Routing on Routing.EntityId=h.id and Routing.RoutingTypeId='ProductDetail' and Site=".SITE." 
outer apply (
    select cast(avg(cast(puan as decimal(10,5))) as decimal(10,1)) as rating,count(id) as reviews from dbo.defter where islm='emlak' and islm_id=h.id and onay=1
) as yorumlar    
where  h.url".UZANTI."=:Slug and h.aktif".UZANTI." = 1 order by h.aktif desc";

        $query = $db->prepare($sql);
        $query->execute([
            "Slug"=>$Routing["Slug"],
            "DefaultCurrencyId"=>1,
            "RateId"=>Rate::GetLastRate()
        ]);
        $json = $query->fetch(PDO::FETCH_ASSOC);

        if ($json["onecikan"]!=""){
            $query = $db->prepare("select id,baslik".DILUZANTI." as baslik from oneCikanOzellikler where id in (".$json["onecikan"].")");
            $query->execute();
            $json["onecikan"] = $query->fetchAll(PDO::FETCH_ASSOC);
        }
        else
            $json["onecikan"]=null;

        $fiyata_dahil = $json["fiyata_dahil"];

        $query = $db->prepare("select id,baslik".DILUZANTI." as baslik,icon
                from dahilOlanlar 
                where aktif=1 and ','+replace(replace(:fiyata_dahil,'#',','),' ','')+',' 
                      like '%,'+convert(varchar,id)+',%' order by siralama asc");
        $query->execute(["fiyata_dahil"=>$fiyata_dahil]);
        $json["fiyata_dahil"]=($query->fetchAll(PDO::FETCH_ASSOC));

        $ozellikler = $json["ozellikler"];
        $query = $db->prepare("select o.id,
       o.baslik".UZANTI." as baslik,
       o.icon".UZANTI."   as icon,
       o.resim".UZANTI."  as resim,
       o.iconmu".UZANTI." as iconmu,
       subTable.*
from ozellikler o

outer apply (
select (select o2.id, o2.baslik".UZANTI." as baslik, o2.icon".UZANTI." as icon, o2.resim".UZANTI." as resim, o2.iconmu".UZANTI." as iconmu,
CASE 
    WHEN CHARINDEX(','+convert(varchar,o2.id)+',', ','+replace(replace('.$ozellikler.','#',','),' ','')+',') > 0 THEN 1
    ELSE 0
  END AS IsAvailable
        from dbo.ozellikler o2
        where o2.cat = o.id
        for json path) as sub
) as subTable
where o.aktif = 1
  and o.cat = 0 and subTable.sub is not null ");
        $query->execute();
        $json["ozellikler"]=array_map(function ($item){
            $item["sub"]=json_decode($item["sub"]);
            return $item;
        },($query->fetchAll(PDO::FETCH_ASSOC)));

        $query = $db->prepare("select baslik".DILUZANTI." as baslik
                from dahilOlanlar 
                where aktif=1 and not ','+replace(replace(:fiyata_dahil,'#',','),' ','')+',' 
                      like '%,'+convert(varchar,id)+',%' order by siralama ");
        $query->execute(["fiyata_dahil"=>$fiyata_dahil]);
        $json["fiyata_dahil_olmayanlar"]=($query->fetchAll(PDO::FETCH_ASSOC));

        $query = $db->prepare("select id,baslik".UZANTI." as baslik,icon
                from kurallar 
                where  ','+replace(replace(:fiyata_dahil,'#',','),' ','')+',' 
                      like '%,'+convert(varchar,id)+',%' ");
        $query->execute(["fiyata_dahil"=>$json["kurallar"]]);
        $json["kurallar"]=($query->fetchAll(PDO::FETCH_ASSOC));

        $Ek = new Ekler($json["title"]);
        $json["title2"] = $Ek->belirtme();

        $json["faq"]["data"] = Faq::GetByPageId(37,5);
        $json["faq"]["page"] = Page::GetById(37,"/");
        $json["Reservation"] = Page::GetById(16,"/");


        //Structural - Yapısal Veriler
        $Structural = new Structural($Routing,$json);
        $json["Structural"] = $Structural->Result();

        //Structural - Yapısal Veriler

        //BreadCrumb
        $BreadCrumb = new BreadCrumb($Routing,$json);
        $json["BreadCrumb"] = $BreadCrumb->Result();
        //BreadCrumb

        $query=$db->prepare("select id,baslik,icerik from sayfalar".UZANTI." where id = 24");
        $query->execute();
        $json["cancellation"]=$query->fetch(PDO::FETCH_ASSOC);

        $query=$db->prepare("select id,baslik,icerik from sayfalar".UZANTI." where id = 25");
        $query->execute();
        $json["sozlesme"]=$query->fetch(PDO::FETCH_ASSOC);

        $query=$db->prepare("select icerik from sayfalar".UZANTI." where id = 188");
        $query->execute();
        $json["kvkk"]=$query->fetch(PDO::FETCH_ASSOC);

        $json["gavelDetailOption"]=$qsql["gavelDetailOption"];
        $json["stopSellMessage"]=$qsql["StopSellMessage"];
        $json["SuccessPage"]=Page::GetById(35);

        $json["hasar_dep_aciklama"]=$qsql["hasar_dep_aciklama"];

        $json["Banners"]=Page::GetByCat(1432,"/");
        $json["hit"] = rand(2,12,);


        $query = $db->prepare("Select uploadID, Filename,aciklama from Upload inner join homes h on h.id=islm_id Where islm='emlak' and h.id=:id and site=1 order by sira");
        $query->execute([
            "id"=>$json["id"]
        ]);
        $json["photos"]=array_map(function ($item){
            return [
                "Filename"=>html_entity_decode($item["Filename"]),
            ];
        },$query->fetchAll(PDO::FETCH_ASSOC));

        if(get("_")){
            $query = $db->prepare("select k.id,k.musteri,convert(varchar(10),d.tarih2,104) as tarih2,convert(varchar(10),d.tarih,104) as tarih from kayitlar k inner join dolu d on d.kayitid=k.id where k.id=:id");
            $query->execute(["id"=>idHash(get("_"),true)]);
            $r = $query->fetch(PDO::FETCH_ASSOC);
            $json["ReviewReservation"] =$r;
        }

        $sql = "SELECT o.baslik as room_name, o.id as room_id, yt.baslik as yatak_ismi, yt.yatakmi, ov.*,yt.icon
                    FROM odalarValues ov
                             LEFT JOIN yatak_tipleri yt on ov.yatak_tipleriId = yt.id
                             LEFT JOIN odalar o on ov.odalarId = o.id
                    WHERE homesId = :id
                    ORDER BY o.id asc";

        $stmt = $db->prepare($sql);
        $stmt->execute(['id' => $json['id'] ]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $rooms = [];

            foreach ($result as $row) {
                if (empty($row['deger'])) {
                    continue;
                }

                $roomName = $row['room_name'];
                $items = [];

                if ($row['yatakmi']) {
                    $items[] = [
                        'item_name' => $row['yatak_ismi'],
                        'icon' => $row['icon'],
                        'value' => $row['deger']
                    ];
                } else {
                    foreach (explode(', ', $row['deger']) as $deger) {
                        $split = explode("//", $deger);
                        $items[] = [
                            'item_name' => $split[0],
                            'icon' => $row['icon'],
                            'value' => 1
                        ];
                    }
                }

                $roomIndex = array_search($roomName, array_column($rooms, 'room_name'));
                if ($roomIndex !== false) {
                    $rooms[$roomIndex]['items'] = array_merge($rooms[$roomIndex]['items'], $items);
                } else {
                    $rooms[] = [
                        'room_name' => $roomName,
                        'items' => $items
                    ];
                }

            }

            $json["rooms"]= $rooms;
        }

        return $json;
    }

}