<?php

    $json = [];
    if($_SERVER["REQUEST_METHOD"] == "POST"){



        $Email = post("Email");
        $ReservationNumber = post("ReservationNumber");

        if(post("AuthKey")){
            $s = explode("_",idHash(post("AuthKey"),1));
            //remove array first
            $ReservationNumber= $s[0];
            array_shift($s);
            $Email = implode("_",$s);
        }

        if($Email && $ReservationNumber){

            $Check = Reservations::CheckByEmail($Email,$ReservationNumber);
            if ($Check){


                $UserAgent = $_SERVER["HTTP_USER_AGENT"];
                $RemoteAddr = $_SERVER["REMOTE_ADDR"];


                $query = $db->prepare("select * from PhpUserTokens WHERE MemberId=:MemberId and Expire>=CURRENT_TIMESTAMP and UserAgent=:UserAgent ");
                $query->execute([
                    "MemberId"=>$ReservationNumber,
                    "UserAgent"=>$UserAgent,
                ]);
                $isLogin = $query->fetch(PDO::FETCH_ASSOC);

                if($isLogin){
                    $json["token"]=$isLogin["Token"];
                    $json["id"]=idHash($ReservationNumber);
                }else{
                    $exData = [
                        "Token"=>md5(uniqid().$UserAgent.$RemoteAddr.$ReservationNumber),
                        "UserAgent"=>$UserAgent,
                        "MemberId"=>$ReservationNumber,
                        "IpAdress"=>$RemoteAddr,
                        "Expire"=>date('Y-m-d H:i:s',time()+USER_EXPIRE)
                    ];

                    $query = $db->prepare("insert into PhpUserTokens (Token, UserAgent, Expire, MemberId, IpAdress) values (:Token, :UserAgent, :Expire, :MemberId, :IpAdress)");
                    $query->execute($exData);

                    $json["token"]=$exData["Token"];
                }







            }else
                $json["error"]="Girdiğiniz bilgilere göre rezervasyon bulunamamıştır.";

        }else
            $json["error"]="Geçersiz kullanıcı bilgisi.";


    }else if($_SERVER["REQUEST_METHOD"] == "GET"){

        $token = get("token");
        if ($token){
            $PhpUserTokens = Login::IsLogin($token);
            if ($PhpUserTokens) {

                $ReservationNumber = $PhpUserTokens["MemberId"];

                $query = $db->prepare("select k.email from kayitlar k where k.id=:ReservationNumber ");
                $query->execute([
                    "ReservationNumber"=>$ReservationNumber
                ]);
                $OriginalReservation = $query->fetch(PDO::FETCH_ASSOC);


                $query = $db->prepare("select k.id,k.musteri,h.baslik,FORMAT(d.tarih,'dd.MM.yyyy') as tarih,FORMAT(d.tarih2,'dd.MM.yyyy') as tarih2,
       k.tutar,h.doviz,h.resim,dbo.FnRandomSplit(h.resim,',') as image,h.title,format(islem_tarihi,'dd.MM.yyyy / H:mm:ss') as islem_tarihi,FromC.Symbol,
       case when convert(date,d.tarih2,104)<convert(date,GETDATE(),104) then 1 else 0 end as IsPass,d.Durum,h.oda_sayisi,h.banyo,h.yatak_odasi,
       (select count(passengerInformation.id) from Reservation.passengerInformation WHERE kayitlarId=k.id) as ToplamKisiBilgisi, k.yetiskin, k.cocuk, k.bebek ,k.odeme,(select count(havale.id) from havale WHERE kayitlarId=k.id) as ToplamHavale
       ,cast(RD.Buy*(case when k.tur='1' then dbo.fnTemizle(k.on_odeme) else dbo.fnTemizle(k.toplam_tutar) end) as decimal(12,0)) as Price,
       concat('/',h.url) as url,k.evid
       from kayitlar k 
                        inner join homes h on h.id = k.evid  
                        inner join dolu d on d.kayitid = k.id
                        inner join Finance.Currency FromC on FromC.CurrencyName=k.doviz
                        inner join Finance.Currency ToC on ToC.CurrencyId=FromC.CurrencyId
                        inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                       where email=:email and not d.Durum=5 order by k.id desc");
                $query->execute([
                    "email"=>$OriginalReservation["email"],
                    "RateId"=>Rate::GetLastRate()
                ]);

                $Reservations = array_map(function ($item){
                    $item["hashed"] = idHash($item["id"]);
                    return $item;
                },$query->fetchAll(PDO::FETCH_ASSOC));

                $json["data"]=$Reservations;





            }else{
                $json["error"]="Lütfen giriş yapınız.";

            }

        }else{
            $json["error"]="Geçersiz token bilgisi";
        }


    }


    echo json_encode($json);

