<?php
SetHeader(200);
$json = [];
$start = get("start");
$end = get("end");
$EntityId = get("EntityId");


//Kiralama Takviminde kontrol Et
$query = $db->prepare("select * from KiralamaTakvimi.CalendarHomes where homesId=:homesId");
$query->execute(["homesId"=>$EntityId]);
$CalendarHomes = $query->fetch(PDO::FETCH_ASSOC);
$DisabledRules=0; //Kendi sistemindeki kuralı devre dışı bırak.

if ($CalendarHomes){
    $result = KiralamaTakvimiReservation::Check($CalendarHomes["EstateId"],$start,$end);
    $json["apiResponse"]=$result;
    $EstateId = $CalendarHomes["EstateId"];
    if($result["Status"]!="Available") {
        if($result["Status"]=="AvailableHasWarningRule"){
            //Warning Kurala takıldıysa sadece kendi içerisinde rez oluştur ve takvime gönderme.
            $DisabledRules=1;
        }else{
            $json["error"] = $result["StatusDescription"];
        }


    }else if($result["Status"]=="Available"){

        $SendToApi = 1;

        if(post("directly")=="1"){ //Kullanıcı Hemen Öde seçeneği ile rezervasyon yapıyorsa
            $BookableDirectly=$CalendarHomes["BookableDirectly"]==true; //Villa hemen Ödenebilir mi?
            $DisabledRules=$BookableDirectly; //Villa hemen Ödenebilir ise kuralları devre dışı bırak.
        }

    }

}


if(!$json["error"]){
    $json = Product::Calculate($start,$end,$EntityId,1,get("PromotionCode"),$DisabledRules);
    if ($json["success"]){
    unset($json["success"]);

    if($json["Product"]["FirstPaymentTypeId"]=="1" && post("firstPayment")!="1"){
        $json["error"]=$Language["reservation"]["onlyFirstPaymentValidationError"];
    }else if ($json["Product"]["FirstPaymentTypeId"]=="2" && post("firstPayment")!="2"){
        $json["error"]=$Language["reservation"]["onlyFullPaymentValidationError"];
    }

    if(!$json["error"]){
        if (post("sozlesme")=="1"){
            if (post("phoneIsValid")=="1"){



                $ExecuteData["islem_tarihi"] = date("Y-m-d H:i:s");
                $ExecuteData["adi"] = $json["Product"]["baslik"];
                //$ExecuteData["rez_odeme_iste"] = $json["Product"]["rez_odeme_iste"];
                //$ExecuteData["odeme_evsahibi"] = $json["Product"]["odeme_evsahibi"];
                $ExecuteData["rez_tarihi"] = $start;
                $ExecuteData["gelecek_tarih"] = $end;
                $ExecuteData["toplam_gece"] = $json["result"]["night"];
                $ExecuteData["yetiskin"] = get("yetiskin");
                $ExecuteData["cocuk"] = get("cocuk");
                $ExecuteData["bebek"] = get("bebek");
                $ExecuteData["toplam_tutar"] = $json["result"]["symbol"].($json["result"]["accommodation_fee"]);
                $ExecuteData["on_odeme"] = $json["result"]["symbol"].($json["result"]["deposit_price"]);
                $ExecuteData["kalan"] = $json["result"]["symbol"].($json["result"]["remaining_price2"]);
                $ExecuteData["hasar_dep"] = $json["result"]["symbol"].(int)($json["Product"]["hasar"]);
                if((int)($json["result"]["old_price"])>0)
                    $ExecuteData["eskifiyat"] = $json["result"]["symbol"].(int)($json["result"]["old_price"]);
                $ExecuteData["temizlik"] = $json["result"]["cleaning_fee"]>0 ? $json["result"]["symbol"].($json["result"]["cleaning_fee"]) : "0";
                $ExecuteData["musteri"]=post("name") . " " . post("surname");
                $ExecuteData["email"]=post("email");
                $ExecuteData["telefon"]=post("phone");
                $ExecuteData["telefon2"]=post("phone2");
                $ExecuteData["odeme"] = post("paymentMethod") ;
                $ExecuteData["tur"]=post("firstPayment");
                $ExecuteData["taksit"]=$qsql["DefaultInstallment"];


                //$ExecuteData["not"]=post("not");
                $ExecuteData["resim"]=$json["Product"]["resim"];
                $ExecuteData["evid"]=$json["Product"]["id"];
                $ExecuteData["doviz"]=$json["Product"]["CurrencyName"];
                $ExecuteData["site"]=SITE;

                $ExecuteData["satis_kanallari_id"]=0;
                $ExecuteData["kur"]=1;
                $ExecuteData["adres"]=post("adres");
                $ExecuteData["sehir"]=post("sehir");
                $ExecuteData["postaKodu"]=post("postaKodu");
                $ExecuteData["tc"]=post("identityNumber");
                $ExecuteData["ulke"]=post("ulke");
                $ExecuteData["promotionCode"]=$json["result"]["code"]["code"];
                $ExecuteData["isNewSite"]=1;
                $ExecuteData["VirtualPosId"]=$qsql["DefaultVirtualPosId"];
                $ExecuteData["InstallmentVirtualPosId"]=$qsql["DefaultInstallmentVirtualPosId"];
                $ExecuteData["BookableDirectly"]=$BookableDirectly;

                $ExecuteData["siparis_kodu"] = time();
                $ExecuteData["tutar"]=post("firstPayment")=="1" ? ($json["result"]["deposit_price"]) : ($json["result"]["accommodation_fee"]);
                $ip = $_SERVER["REMOTE_ADDR"];

                if (!empty($_SERVER["HTTP_CF_CONNECTING_IP"])) {
                    $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
                }
                $ExecuteData["ipAddress"]=$ip;
                $a = 0;
                if($SendToApi){
                    $PostData = [];
                    $PostData["estateId"]=(int)$EstateId;
                    $PostData["checkInDate"]=$start;
                    $PostData["checkOutDate"]=$end;
                    $PostData["customerEmail"]=$ExecuteData["email"];
                    $PostData["customerFullName"]=$ExecuteData["musteri"];
                    $PostData["customerPhone"]=$ExecuteData["telefon"];
                    $PostData["adultGuestCount"]=(int)$ExecuteData["yetiskin"];
                    $PostData["childGuestCount"]=(int)$ExecuteData["cocuk"];
                    if($BookableDirectly)
                        $PostData["skipOptionEnding"]=1;
                    $ApiResponse = KiralamaTakvimiReservation::Create($PostData);
                    if ($ApiResponse["ReservationId"]>0){
                        $json["response"]=$ApiResponse;
                    }else{
                        $a=1;
                        $json["error"]=$ApiResponse["Messages"][0];
                        $json["x"]=$PostData;
                        $json["response"]=$ApiResponse;
                    }
                }

                try {

                    if($a==0){
                        $query = $db->prepare("insert into dbo.kayitlar (".implode(",",array_keys($ExecuteData)).") values (".implode(', ', array_map(function ($item) {return ":".$item;}, array_keys($ExecuteData))).") ");
                        $r = $query->execute($ExecuteData);
                    }
                    if ($r){
                        $kayitId = $db->lastInsertId();
                        if ($ApiResponse["ReservationId"]>0){
                            $query=$db->prepare("insert into KiralamaTakvimi.Response (kayitlarId, ReservationId, EstateId) values (:kayitlarId, :ReservationId, :EstateId)");
                            $query->execute([
                                "kayitlarId"=>$kayitId,
                                "ReservationId"=>$ApiResponse["ReservationId"],
                                "EstateId"=>$ApiResponse["EstateId"],
                            ]);
                        }

                        $query = $db->prepare("insert into dolu (tarih,tarih2,emlak,musteri,onay,wait,durum,kayitid,ReservationId) values (:tarih,:tarih2,:emlak,:musteri,:onay,:wait,:durum,:kayitid,:ReservationId)");
                        $insert = $query->execute([
                            "tarih"=>$ExecuteData["rez_tarihi"],
                            "tarih2"=>$ExecuteData["gelecek_tarih"],
                            "emlak"=>$ExecuteData["evid"],
                            "musteri"=>$ExecuteData["musteri"],
                            "onay"=>0,
                            "wait"=>0,
                            "durum"=>0,
                            "kayitid"=>$kayitId,
                            "ReservationId"=>$ApiResponse["ReservationId"]?:0,
                        ]);



                        foreach ($json["AvailableExtraServices"] as $entry) {
                            foreach ($entry['extraServices'] as $service) {
                                $key = $service['id'] . '-' . $service['amount'] . '-' . $service['start_date'] . '-' . $service['end_date'].'-'.$service['Type'];

                                // Daha önce eklenmişse gece sayısını artır
                                if (!isset($serviceSummary[$key])) {
                                    $serviceSummary[$key] = [
                                        'id' => $service['id'],
                                        'title' => $service['title'],
                                        'type' => $service['Type'],
                                        'price' => $service['amount'],
                                        'totalPrice' => $service['amount'],
                                        'nights' => 1,
                                    ];
                                } else {
                                    $serviceSummary[$key]['nights'] += 1;
                                    if($service['Type']=="0")
                                        $serviceSummary[$key]["totalPrice"] += $service['amount'];
                                }
                            }
                        }

                        foreach ($serviceSummary as $key => $value) {
                            $query=$db->prepare("insert into Reservation.ReservationExtraPayments (ReservationId, Title, Amount, CurrencyId, Type, Night, TotalAmount) values (:ReservationId, :Title, :Amount, :CurrencyId, :Type, :Night, :TotalAmount)");
                            $query->execute([
                                "ReservationId" => $kayitId,
                                "Title" => $value['title'],
                                "Amount" => $value['price'],
                                "CurrencyId" => $json["Product"]["CurrencyId"],
                                "Type" => $value['type'],
                                "Night" => $value['nights'],
                                "TotalAmount" => $value['totalPrice']
                            ]);
                        }


                        $mailkonu = str_replace("{villaName}",$json["Product"]["baslik"],$Language["sendReservationMail"]["title"]);
                        $mailkonuSite = str_replace("{villaName}",$json["Product"]["baslik"],$Language["sendReservationMail"]["titleSite"]);

                        $query = $db->prepare("select * from MailSablon where id=1");
                        $query->execute();
                        $mailsablon1 = $query->fetch(PDO::FETCH_ASSOC);

                        $mailicerik = MailTemplate::index("yeni_talep".UZANTI.".html",$kayitId);
                        $mailicerik = str_replace("{-mail-baslik-}",		$mailkonu,		$mailicerik);
                        $mailicerik = str_replace("{-mail-icerik-}",		$mailsablon1["icerik".UZANTI],												$mailicerik);

                        $Mail = new SendMail();
                        $Mail->setEmail($ExecuteData["email"]);
                        $Mail->setContent($mailicerik);
                        $Mail->setReceiverName($ExecuteData["musteri"]);
                        $Mail->setSubject($mailkonu);
                        if($BookableDirectly!=1)
                            $Mail->Send();


                        $mailicerik = MailTemplate::index("yeni_talep_site".UZANTI.".html",$kayitId);
                        $mailicerik = str_replace("{-mail-baslik-}",		"Yeni Rezervasyon Talebi (".$json["Product"]["baslik"].")",		 $mailicerik);
                        $mailicerik = str_replace("{-mail-icerik-}",		"",		$mailicerik);

                        $Mail = new SendMail();
                        $Mail->setEmail($config['smtp_username']);
                        $Mail->setContent($mailicerik);
                        $Mail->setReceiverName($config["smtp_sendFrom"]);
                        $Mail->setSubject($mailkonuSite);
                        if($BookableDirectly!=1)
                            $Mail->Send();

                        $json["success"]=$Language["reservation"]["success"];
                        $json["hash"]=idHash($kayitId);
                        $json["deposit_price"]=$json["result"]["deposit_price"];
                        $json["id"]=($kayitId);


                        if($BookableDirectly){


                            $opt = array(
                                CURLOPT_URL => str_replace("www.","web.",DOMAIN)."/".PanelUrl."/aktif_pasif.asp?site=".$ExecuteData["site"],
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_ENCODING => '',
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_TIMEOUT => 0,
                                CURLOPT_SSL_VERIFYHOST => 0,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                CURLOPT_CUSTOMREQUEST => "POST",
                                CURLOPT_POSTFIELDS =>  "id=".$kayitId."&islem=siparis_1&saat=15&bilgilendir=1&BookableDirectly=1&isMinute=1",
                                CURLOPT_POST => true,
                                CURLOPT_HTTPHEADER => array(
                                    'EstablishmentId: '.$config["EstablishmentId"],
                                    'X-API-Key: '.$config["apiKey"]
                                ),
                            );
                            $curl = curl_init();
                            curl_setopt_array($curl, $opt);
                            $response = curl_exec($curl);
                            $info = curl_getinfo($curl);
                            $json["AutoOptionResponse"]=$response;
                            $split = explode(";/",$response);

                            //"?_="&idhash(bocek("id"))&"&authKey="&idhash(bocek("id")&"_"&bocek("email")))
                            if ($split[0] == "0") {
                                $PayByCreditCardPage = Page::GetById(22);
                                $json["redirectTo"]=DOMAIN."/".$PayByCreditCardPage["url"]."?_=".idHash($kayitId)."&authKey=".idHash($kayitId."_".$ExecuteData["email"]);
                            }


                        }


                        $query = $db->prepare("select * from smsSablon".UZANTI." where id=3 and aktif=1");
                        $query->execute();
                        $sms = $query->fetch(PDO::FETCH_ASSOC);

                        if($sms && $BookableDirectly!=1){
                            $mesaj = str_replace(
                                ["{musteri}","{villaadi}","{tarih1}","{tarih2}","{rez_kodu}","{yetiskin}","{cocuk}"],[
                                $ExecuteData["musteri"],
                                $ExecuteData["adi"],
                                formattarih($ExecuteData["rez_tarihi"]),
                                formattarih($ExecuteData["gelecek_tarih"]),
                                $kayitId,
                                $ExecuteData["yetiskin"],
                                $ExecuteData["cocuk"],
                            ],$sms["icerik"]);

                            $a = new SmsSend();
                            $data=array(
                                'message'=>$mesaj,
                                'no'=>[$ExecuteData["telefon"]],
                                'header'=>$qsql["smsorg"],
                                'filter'=>0,
                                'encoding'=>'tr',
                                'startdate'=>'',
                                'stopdate'=>'',
                                'bayikodu'=>'',
                                'appkey'=>''
                            );
                            $sms= new SmsSend;
                            $sms->smsGonder($data);
                        }

                    }else
                        $json["error"]=$Language["reservation"]["reservationError"];

                }catch (PDOException $e) {
                    $json["error"]=$e->getMessage();
                }

            }else $json["error"]=$Language["reservation"]["phoneValidationError"];
        }else{
            $json["error"]=$Language["reservation"]["agreementValidationError"];
        }
    }

}
}

echo json_encode($json);