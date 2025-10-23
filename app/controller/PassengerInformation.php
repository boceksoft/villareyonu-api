<?php

SetHeader(200);
$json = [];
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $token = get("token");
    $id = get("_");
    if ($token) {
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {

            $query = $db->prepare("select k.id,k.musteri,h.baslik,FORMAT(d.tarih,'dd.MM.yyyy') as tarih,FORMAT(d.tarih2,'dd.MM.yyyy') as tarih2,
       k.tutar,h.doviz,h.resim,h.title,format(islem_tarihi,'dd.MM.yyyy / H:mm:ss') as islem_tarihi,FromC.Symbol,
       case when convert(date,d.tarih2,104)<convert(date,GETDATE(),104) then 1 else 0 end as IsPass,d.Durum,k.yetiskin,k.cocuk,(convert(int,k.yetiskin)+convert(int,k.cocuk)) as totalPassenger,
       k.email,k.tc,k.telefon,k.adres
       from kayitlar k 
                        inner join homes h on h.id = k.evid 
                        inner join dolu d on d.kayitid = k.id 
                        inner join Finance.Currency FromC on FromC.CurrencyName=k.doviz
                        inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                        inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                       where k.id=:id and not d.Durum=5");
            $query->execute([
                "DefaultCurrencyId"=>1, //Hangi para biriminde ödeme yapılacak ise onun id si yazılabilir.
                "RateId"=>Rate::GetLastRate(),
                "id"=>idHash($id,true)
            ]);
            $Reservation = $query->fetch(PDO::FETCH_ASSOC);
            if($Reservation){
                $json["success"]="200";
                $json["reservation"] = $Reservation;

            }else
                $json["error"]="Rezervasyon bulunamadı.";


        }else{
            $json["error"]="Lütfen giriş yapınız.";
        }
    }else{
        $json["error"]="Geçersiz token bilgisi";
    }
}else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = get("token");
    $id = get("_");
    if ($token) {
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {
            $ReservationId = idHash($id,true);




            if(count(post("passenger"))>0 && post("invoice")){
                foreach (post("passenger") as $customer){

                    $insertData["kayitlarId"] = $ReservationId;
                    $insertData["birthDate"] = $customer["birthDate"];
                    $insertData["gender"] = $customer["gender"];
                    $insertData["birthPlace"] = $customer["placeOfBirth"];
                    $insertData["isTurkish"] = $customer["isTurkish"]=="true" ? 0 : 1;
                    $insertData["name"] = $customer["name"];
                    $insertData["tc"] = $customer["tc"];

                    $query = $db->prepare("insert into Reservation.passengerInformation (kayitlarId, birthDate, gender, isTurkish, name, tc, birthPlace) values (:kayitlarId, :birthDate, :gender, :isTurkish, :name, :tc, :birthPlace)");
                    $query->execute($insertData);

                }

                $invoiceData["faturatipi"]=post("invoice")["faturatipi"];
                $invoiceData["isim"]=post("invoice")["isim"];
                $invoiceData["tcno"]=post("invoice")["tcno"];
                $invoiceData["email"]=post("invoice")["email"];
                $invoiceData["telefon"]=post("invoice")["telefon"];
                $invoiceData["adress"]=post("invoice")["adres"];
                $invoiceData["mesaj"]=post("invoice")["mesaj"];
                $invoiceData["vergidairesi"]=post("invoice")["vergidairesi"];
                $invoiceData["kayitlarId"]=$ReservationId;

                $query = $db->prepare("insert into Reservation.InvoiceData (faturatipi, isim, tcno, email, telefon, mesaj, vergidairesi, kayitlarId, adress) values(:faturatipi, :isim, :tcno, :email, :telefon, :mesaj, :vergidairesi, :kayitlarId, :adress) ");
                $query->execute($invoiceData);


                $mailicerik= MailTemplate::Index("default.txt",0);
                $mailicerik = str_replace("{-mail-icerik-}",		$ReservationId." numaralı rezervasyon için E-Fatura bilgileri sisteme kaydedilmiştir.Lütfen yönetim paneli üzerinden gelen kişi bilgilerini teyit edip onaylayınız.",			$mailicerik);
                $Mail = new SendMail();
                $Mail->setEmail($config['smtp_username']);
                $Mail->setContent($mailicerik);
                $Mail->setReceiverName($config["smtp_sendFrom"]);
                $Mail->setSubject($ReservationId." Rez. E-Fatura Bilgileri Sisteme Kaydedildi.");
                $Mail->Send();

                $json["success"]=$Language["success"];




            }else{
                $json["error"] = $Language["invoiceError"];
            }
        }else{
            $json["error"]=$Language["notAuth"];
        }
    }else{
        $json["error"]=$Language["invalidToken"];
    }
}

echo json_encode($json);