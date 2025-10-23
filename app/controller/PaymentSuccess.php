<?php

    SetHeader(200);
    $json=[];

    $conversationId = get("_");
    if($conversationId){
        $query = $db->prepare("select * from iyzico_response where conversationId=:conversationId");
        $query->execute(["conversationId"=>$conversationId]);
        $iyzico_response= $query->fetch(PDO::FETCH_ASSOC);
        if ($iyzico_response){
            $callback=json_decode($iyzico_response["callback"],2);



            //Ödeme Başarılı olmuş.
            if($callback["status"]=="success"){

                //Şifrelenmiş rez numarasını decode et;
                $ReservationNumber = idHash($conversationId,true);

                //Rezervasyon Sorgusunu değişkene aktar.
                $sql="
                select kayitlar.id as kayitid,
                DATEDIFF(DAY,rez_tarihi,gelecek_tarih) as gece,
                destinations.baslik as bolge_baslik,
                d2.baslik as bolge_ust_baslik,
                kayitlar.*, 
                dbo.FnRandomSplit(homes.resim,',') as resim, 
                homes.baslik as baslik, 
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
                    if (Reservation::ChangeStatus($ReservationNumber,Reservation::$Success)){
                        //Ödeme Şeklini kredi kartı olarak güncelle.
                        Reservation::ChangePaymentType($ReservationNumber,PaymentType::$CreditCard);

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
						
						$mailicerik = MailTemplate::index("tamamlandi.txt",$ReservationNumber);
						$mailicerik = str_replace("{-mail-baslik-}",				"Ödeme Yapıldı! (".$Reservation["id"].")",								$mailicerik);
						$mailicerik = str_replace("{-mail-icerik-}",				$mailsablon1["icerik"],													$mailicerik);
						$mailicerik = str_replace("{-kisi-bilgileri-btn-}",			str_replace("web.","www.",$qsql["domain"])."/rezervasyon-tamamla",		$mailicerik);
						$mailicerik = str_replace("{-rezervasyonlarim-btn-}",		str_replace("web.","www.",$qsql["domain"])."/rezervasyonlarim",			$mailicerik);
						
                        $mailkonu = "Ödeme Yapıldı! (".$Reservation["id"].")";
                        $Mail = new SendMail();
                        $Mail->setEmail($Reservation["email"]);
                        $Mail->setContent($mailicerik);
                        $Mail->setReceiverName($Reservation["musteri"]);
                        $Mail->setSubject($mailkonu);
                        $Mail->setBcc($config["smtp_username"]);
                        if($Mail->Send()){
                            $json["success"]="200";
                        }else{
                            $json["error"]="Mail Gönderilirken bir sorun oluştu.(Ödeme işlemleri ve rezervasyon gerçekleşti.Girdiğiniz mail adresi hatalı olabilir.Rezervasyon kontrolünü firmamızı arayarak doğrulayınız.)";
                        }
                    }else{
                        $json["error"]="Rezervasyon durumu değiştirilirken bir sorun oluştu.";
                    }
                }else{
                    //Rezervasyon bulunamadı ise.
                    $json["error"]="Reservasyon Bulunamadı.";
                }
            }
        }else{
            $json["error"]="Bu siparişe ait herhangi bir ödeme bulunamadı.";
        }

    }else{
        $json["error"]="Eksik veya hatalı parametre.";
    }
    echo json_encode($json);

