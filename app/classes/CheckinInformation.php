<?php

class CheckinInformation
{
    public static function Customer($Routing){
        global $db;
        global $qsql;
        $pathname=explode("/",get("pathname")?:get("path"));

        $query = $db->prepare("select id,title,baslik,description from sayfalar where id=:id");
        $query->execute(["id"=>$Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);

        //datepart(minute,islem_tarihi)) islemsaati

        $id = $pathname[2];
        $query = $db->prepare("select 
            I.faturatipi,I.isim,I.tcno,I.email,I.telefon as ftelefon,I.mesaj,I.vergidairesi,k.id,concat('/',h.url) as url,
            h.baslik,dbo.FnRandomSplit(h.resim,',') as resim,h.title,h.evkodu,k.musteri,k.telefon,k.telefon2,h.giris_saat,h.cikis_saat,d.tarih,
            k.tur,trim(replace(dbo.fnGetInt(k.on_odeme),'.','')) as on_odeme,
            convert(varchar(10),d.tarih,104) as tarihFormatted,
            convert(varchar(10),d.tarih2,104) as tarih2Formatted,
            d.tarih2,k.yetiskin,k.cocuk,k.hasar_dep,concat(e.ad,' ',e.soyad) as  EvsahibiAd,e.tel as EvsahibiTel,h.enlem,h.boylam,
            h.BakimciAd,h.BakimciTel,
            trim(replace(dbo.fnGetInt(k.kalan),'.','')) as kalan,
            isnull(trim(replace(dbo.fnGetInt(k.temizlik),'.','')),0) as temizlik,
            trim(replace(dbo.fnGetInt(k.toplam_tutar),'.','')) as toplam_tutar,
            trim(replace(dbo.fnGetInt(k.hasar_dep),'.','')) as hasar,
            concat(d2.baslik,' / ',d3.baslik) as bolge,
            ss.id as safeStatusId,
            Currency.Symbol,
            RD.Buy*(case when k.tur='1' then dbo.fnTemizle(k.on_odeme) else dbo.fnTemizle(k.toplam_tutar) end) as Price
                from kayitlar k
                    inner join homes h on h.id=k.evid 
                    inner join dolu d on d.kayitid = k.id
                    inner join destinations d3 on d3.id=h.emlak_bolgesi
                    inner join destinations d2 on d2.id=d3.cat
                    inner join Finance.Currency on Currency.CurrencyName=k.doviz
                    inner join Finance.RateDetail RD on RD.ToCurrencyId=Finance.Currency.CurrencyId and RD.FromCurrencyId=Finance.Currency.CurrencyId and RD.RateId=:RateId
                    left join safeStatus ss on ss.kayitId=k.id
                    left join kullanici e on e.id=IIF(h.evsahibi=0,1,h.evsahibi)
                    left join Reservation.InvoiceData I on I.kayitlarId=k.id
                where  concat(k.id,(case when datepart(hour,k.islem_tarihi)<10 then '0' else '' end),datepart(hour,k.islem_tarihi),(case when datepart(minute,k.islem_tarihi)<10 then '0' else '' end),datepart(minute,k.islem_tarihi))=:id order by I.id desc");
        $query->execute([
            "id"=>$id,
            "RateId"=>Rate::GetLastRate()
        ]);
        $Reservation= $query->fetch(PDO::FETCH_ASSOC);

        $query= $db->prepare("select * from Reservation.vw_ReservationExtraPayments where ReservationId=".$Reservation["id"]);
        $query->execute();
        $ReservationExtraPayments = $query->fetchAll(PDO::FETCH_ASSOC);
        $Reservation["ReservationExtraPayments"]=$ReservationExtraPayments;

        $result["details"] = $Reservation;

        if($Reservation["enlem"]!="" && $Reservation["boylam"]!=""){
            //Gezilecek Yerler

            $sql = " select *, CONVERT(FLOAT, Abs(Abs( CONVERT(FLOAT, mesafe_cetveli.enlem) - CONVERT(FLOAT, '".$Reservation["enlem"]."') ) + Abs( CONVERT(FLOAT, mesafe_cetveli.boylam) - CONVERT(FLOAT, '".$Reservation["boylam"]."') ) )) as mesafe,(select d.baslik from destinations d where d.id=mesafe_cetveli.bolge) as bolge from mesafe_cetveli ";
            $sql.="where tip=3 ";
            $sql.=" AND CONVERT(FLOAT, Abs(  ";
            $sql.="             Abs( CONVERT(FLOAT, mesafe_cetveli.enlem) -  ";
            $sql.="             CONVERT(FLOAT, '".$Reservation["enlem"]."') ) +  ";
            $sql.="             Abs( CONVERT(FLOAT, mesafe_cetveli.boylam) -  ";
            $sql.="             CONVERT(FLOAT, '".$Reservation["boylam"]."') )  ";
            $sql.="         )) <=  ";
            $sql.="     dbo.Fnmetremap(20000) ";

            $query = $db->query($sql);
            $GezilecekYerler = $query->fetchAll(PDO::FETCH_ASSOC);
            $result["GezilecekYerler"] = array_merge($GezilecekYerler,$GezilecekYerler,$GezilecekYerler);


            $sqlMesafeIsletme= "select *,(select d.baslik from destinations d where d.id=mesafe_cetveli.bolge) as bolge from mesafe_cetveli
                            where tip=1
                            AND CONVERT(FLOAT, Abs( 
                             Abs( CONVERT(FLOAT, mesafe_cetveli.enlem) -  
                                  CONVERT(FLOAT, '".$Reservation["enlem"]."') ) + 
                                   Abs( CONVERT(FLOAT, mesafe_cetveli.boylam) -  
                                   CONVERT(FLOAT, '".$Reservation["boylam"]."') )  )) <=  dbo.Fnmetremap(2000)";

            $sqlMesafeGezme= "select *,(select d.baslik from destinations d where d.id=mesafe_cetveli.bolge) as bolge from mesafe_cetveli
                            where tip=3
                            AND CONVERT(FLOAT, Abs( 
                             Abs( CONVERT(FLOAT, mesafe_cetveli.enlem) -  
                                   CONVERT(FLOAT, '".$Reservation["enlem"]."') ) + 
                                    Abs( CONVERT(FLOAT, mesafe_cetveli.boylam) -  
                                     CONVERT(FLOAT, '".$Reservation["boylam"]."') )  )) <=  dbo.Fnmetremap(2000)";

            $query = $db->query($sqlMesafeIsletme);
            $isletmeler= $query->fetchAll(PDO::FETCH_ASSOC);

            $yeradlari="";
            $enlemboylams="";
            $ek=-1;
            foreach ($isletmeler as $isletme){
                $ek++;
                if($enlemboylams!=""){
                    $enlemboylams.="|";
                }
                if($yeradlari!=""){
                    $yeradlari.=";/;";
                }
                $enlemboylams.=$isletme["enlem"].",".$isletme["boylam"];
                $yeradlari.=$isletme["yer"]."##".$isletme["resim"]."##".$isletme["bolge"]."##".$isletme["enlem"]."##".$isletme["boylam"]."##".$isletme["telefon"];
            }


            $kmverisicek="https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Reservation["enlem"].",".$Reservation["boylam"]."&destinations=".urlencode($enlemboylams)."&key=".$qsql["google_map_api"]."&mode=walking";
            $cacheDir = realpath(".") . "/app/cache/distance_matrix/";
            // Cache dosyasının adını belirle (URL'yi hash'leyerek benzersiz bir isim oluşturuyoruz)
            $cacheFileName =  $cacheDir . md5($kmverisicek) . '.json';

            // Cache dosyası varsa ve süresi dolmadıysa, dosyadan veriyi oku
            if (file_exists($cacheFileName)) {
                $response = file_get_contents($cacheFileName);
            } else {
                // API'den veri al ve cache'e kaydet
                $response = file_get_contents($kmverisicek);
                file_put_contents($cacheFileName, $response);
            }

            $data = json_decode($response, true);
            $yakindakiyerler="";
            $yerlerArr=explode(";/;",$yeradlari);
            for ($i=0; $i<count($yerlerArr); $i++){
                if($yakindakiyerler!=""){
                    $yakindakiyerler.=";/;";
                }
                $Yxkm= $data["rows"][0]["elements"][$i]["distance"]["text"];
                $Yxminute= $data["rows"][0]["elements"][$i]["duration"]["text"];
                $yakindakiyerler.=$yerlerArr[$i]."##".$Yxkm."##".$Yxminute;
            }
            $yerlerGosterARr=explode(";/;",$yakindakiyerler);
            $result["YakinYerler"] = $yerlerGosterARr;



        }






        return $result;
    }

    public static function metrebazi($metre)
    {

        $cevap = $metre;
        if(floatval($cevap)<10000){
            $cevap = "0".$cevap;
        }
        if(floatval($cevap)<1000){
            $cevap = "0".$cevap;
        }
        if(floatval($cevap)<100){
            $cevap = "0".$cevap;
        }
        if(floatval($cevap)<10){
            $cevap = "0".$cevap;
        }
        $cevap = "0,".$cevap;
        if(floatval($metre)>999){
            $msg = str_replace(substr($metre,-3),"", $metre)." km";
        }else{
            $msg = $metre." metre";
        }
        $cevap = $cevap.";/;/".$msg;

        return $cevap;

    }
}