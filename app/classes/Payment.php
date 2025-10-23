<?php

class Payment
{
    public static function Reservation($Routing)
    {

        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,kisa_icerik from sayfalar where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $result["sozlesme"]=Page::GetById(25,"/");



        //$result["Product"]=self::GetReservation();


        return $result;
    }

    public static function GetReservation($ReservationNumber){
        global $db;

        $query = $db->prepare("select k.id,k.musteri,h.baslik".UZANTI." as name,h.baslik".UZANTI." as baslik,FORMAT(d.tarih,'dd.MM.yyyy') as tarih,FORMAT(d.tarih2,'dd.MM.yyyy') as tarih2,
       k.tutar,h.doviz,h.resim,h.title".UZANTI." as title,h.url".UZANTI." as url,format(islem_tarihi,'dd.MM.yyyy / H:mm:ss') as islem_tarihi,FromC.Symbol,(k.temizlik),
       RD.Buy*(case when k.tur='1' then dbo.fnGetInt(k.on_odeme) else dbo.fnGetInt(k.toplam_tutar) end) as Price,
       (case when k.tur='1' then dbo.fnGetInt(k.on_odeme) else dbo.fnGetInt(k.toplam_tutar) end) as PriceOrg,
       h.ribbon,h.ribbon2,h.kisi,h.yatak_odasi,h.banyo,k.taksit,
       RD.Buy,
       GETDATE() as CurrentDate,case WHEN TRY_CONVERT(datetime,saat,104)>=GETDATE() THEN 0 ELSE 1 END as IsExpired,TRY_CONVERT(datetime,saat,104) as saat,
       dbo.FnRandomSplit(h.resim,',') as image,
       ToC.Symbol as TSymbol,
       d2.baslik".UZANTI."+' / '+d1.baslik".UZANTI." as destination,
       ToC.CurrencyCode,
       ToC.PosCode,
       h.depozito,
       k.on_odeme,
       k.site,
       k.tur,
       k.LastSendedPdfByConversationId,
       dbo.fnGetInt(k.hasar_dep) as hasar_dep,
             d.tarih as CheckInDate,
       d.tarih2 as CheckOutDate,
       dbo.fnGetInt(k.kalan) as kalan,
       dbo.fnGetInt(k.temizlik) as temizlik,
       dbo.fnGetInt(k.on_odeme) as on_odeme,
       case when convert(date,d.tarih2,104)<convert(date,GETDATE(),104) then 1 else 0 end as IsPass,d.Durum,dbo.fnGetInt(k.toplam_tutar) as toplam_tutar,
       datediff(day ,d.tarih,d.tarih2) as gece,k.email,k.telefon,k.tc,k.adres,h.evkodu,k.yetiskin,k.cocuk,k.bebek,k.evid,k.VirtualPosId,k.InstallmentVirtualPosId
       from kayitlar k 

                        inner join homes h on h.id = k.evid 
                               inner join destinations d1 on d1.id = h.emlak_bolgesi
inner join destinations d2 on d2.id = d1.cat  
                        inner join dolu d on d.kayitid = k.id
                        inner join Finance.Currency FromC on FromC.CurrencyName=k.doviz
                        inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                        inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                       where k.id=:id and d.durum in (1,3)");
        $query->execute([
            "id"=>$ReservationNumber,
            "DefaultCurrencyId"=>1, //Hangi para biriminde ödeme yapılacak ise onun id si yazılabilir.
            "RateId"=>Rate::GetLastRate()
        ]);
        $reservation = $query->fetch(PDO::FETCH_ASSOC);

        $query= $db->prepare("select * from Reservation.vw_ReservationExtraPayments where ReservationId=".$reservation["id"]);
        $query->execute();
        $ReservationExtraPayments = $query->fetchAll(PDO::FETCH_ASSOC);
        $reservation["ReservationExtraPayments"]=$ReservationExtraPayments;

        return $reservation;


    }

    public static function Success($ReservationNumber,$BankName="",$VirtualPosId=0){
        global $Language;
        global $db;
        global $config;
        global $qsql;
        $json=[];
        //Rezervasyon Sorgusunu değişkene aktar.
        $sql="
                select kayitlar.id as kayitid,
                DATEDIFF(DAY,rez_tarihi,gelecek_tarih) as gece,
                destinations.baslik".UZANTI." as bolge_baslik,
                d2.baslik".UZANTI." as bolge_ust_baslik,
                kayitlar.*, 
                dbo.FnRandomSplit(homes.resim,',') as resim, 
                homes.baslik".UZANTI." as baslik, 
                convert(varchar,convert(date,kayitlar.rez_tarihi,104),103) as t1, 
                convert(varchar,convert(date,kayitlar.gelecek_tarih,104),103) as t2 
                from homes 
                inner join kayitlar on kayitlar.evid=homes.id 
                inner join destinations on destinations.id=homes.emlak_bolgesi 
                inner join destinations as d2 on d2.id=destinations.cat 
                where kayitlar.id=:ReservationNumber";


        $query= $db->prepare($sql);
        $query->execute([
            "ReservationNumber"=>$ReservationNumber
        ]);
        $Reservation = $query->fetch(PDO::FETCH_ASSOC);


        if ($Reservation){
            //Rezervasyon var ise.

            //rqbot("sablon1"&uzanti&".asp?islem=odeme_tamamlandi&islm_id=".bocek["id"]."&isim=".bocek["musteri"]."&email=".bocek["email"]."&rezervasyonkodu=".bocek["id"]."&odeme=".bocek["odeme"]."&resim=".bocek["resim"]."&bolge=".bocek["bolge_ust_baslik"]."-".bocek["bolge_baslik"]."&tarih=".bocek["rez_tarihi"]."&tarih2=".bocek["gelecek_tarih"]."&gece=".bocek["gece"]."&kisi=".bocek["yetiskin"]."&cocuk=".bocek["cocuk"]."&konaklama_bedeli=".bocek["toplam_tutar"]."&depozito=".bocek["on_odeme"]."&villaya_giriste_odenecek=".bocek["kalan"]."&villakodu="&evUzanti.bocek["baslik"]."&hasar_dep=".bocek["hasar_dep"]."&temizlik=".bocek["temizlik"]."&elektrik=".bocek["elektrik"]&parcaliEk)

            $query=$db->prepare("Select * from KiralamaTakvimi.Response where kayitlarId=:kayitlarId");
            $query->execute(["kayitlarId"=>$ReservationNumber]);
            $response = $query->fetch(PDO::FETCH_ASSOC);

            //Kiralama takvimi ile bağlantılı bir rez ise
            if ($response){
                KiralamaTakvimiReservation::Approve($response["ReservationId"]);
            }

            //Encode for url
            $ReservationEncoded = array_map(function ($item){
                return urlencode($item);
            },$Reservation);

            //Durum Değiştir
            $Cs = Reservation::ChangeStatus($ReservationNumber,Reservation::$Success);
            if ($Cs){
                //Ödeme Şeklini kredi kartı olarak güncelle.
                //Reservation::ChangePaymentType($ReservationNumber,PaymentType::$CreditCard);

                //Todo:Buraya var ise tarihler ve sonDakika tablosundaki verileri silme işlemi yapılacak.

                //for i=cdate(bocek("t1")) to cdate(bocek("t2"))
                //    a=split(i,".")
                //    baglan.execute("delete from tarihler where islem='emlak' and islem_id="&bocek("evid")&" and convert(date,'"&a(2)&"-"&a(1)&"-"&a(0)&"') between convert(date,tarih1,104) and convert(date,tarih2,104)")
                //    baglan.execute("delete from sonDakika where islem_id="&bocek("evid")&" and convert(date,'"&a(2)&"-"&a(1)&"-"&a(0)&"') between convert(date,tarih1,104) and convert(date,tarih2,104)")
                //next

                $start_date = new DateTime($Reservation["rez_tarihi"]);
                $end_date = new DateTime($Reservation["gelecek_tarih"]);

                $current_date = $start_date;

                while ($current_date <= $end_date) {
                    $query2 = $db->prepare("delete from sonDakika where islem_id=".$Reservation["evid"]." and convert(date,'".$current_date->format('Y-m-d')."') between convert(date,tarih1,104) and convert(date,tarih2,104)");
                    $query2->execute();
                    $current_date->modify('+1 day');
                }

                $query = $db->prepare("select * from MailSablon where id=3");
                $query->execute();
                $mailsablon1 = $query->fetch(PDO::FETCH_ASSOC);

                $ReservationsPage=Page::GetById(3);

                $MailBaslik=str_replace("{ReservationId}",$Reservation["id"],$Language["paymentSuccessMail"]["title"]);

                $mailicerik = MailTemplate::index("tamamlandi".UZANTI.".html",$ReservationNumber);

                $mailicerik = str_replace("{-mail-baslik-}",				$MailBaslik,								$mailicerik);
                $mailicerik = str_replace("{-mail-icerik-}",				$mailsablon1["icerik".UZANTI],													$mailicerik);
                $mailicerik = str_replace("{musteri}",					$Reservation["musteri"],													$mailicerik);
                $mailicerik = str_replace("{villaadi}",					$Reservation["adi"],													$mailicerik);
                $mailicerik = str_replace("{-kisi-bilgileri-btn-}",			SiteUrl($ReservationsPage["url"]),		$mailicerik);
                $mailicerik = str_replace("{-rezervasyonlarim-btn-}",		SiteUrl($ReservationsPage["url"]),			$mailicerik);
                $mailicerik = str_replace("{-rez-tamamla-url-}",		SiteUrl($ReservationsPage["url"]),			$mailicerik);
                $Mail = new SendMail();
                $Mail->setEmail($Reservation["email"]);
                $Mail->setContent($mailicerik);
                $Mail->setReceiverName($Reservation["musteri"]);
                $Mail->setSubject($MailBaslik);
                if($Mail->Send()){


                    $Mail = new SendMail();
                    $Mail->setEmail($config["smtp_username"]);
                    $Mail->setContent($mailicerik);
                    $Mail->setReceiverName($config["smtp_sendFrom"]);
                    $Mail->setSubject("Rezervasyon ".$Reservation["id"]." ".$Reservation["baslik"]." ".$BankName." ödeme alındı.");
                    $Mail->Send();

                    $json["success"]="200";
                }else{
                    $json["error"]=$Language["errors"]["mainNotSended"];
                }
            }else{
                $json["error"]=$Language["errors"]["reservationPaymentTypeError"];
            }
        }else{
            //Rezervasyon bulunamadı ise.
            $json["error"]=$Language["errors"]["reservationNotFound"];
        }
        return $json;
    }


}