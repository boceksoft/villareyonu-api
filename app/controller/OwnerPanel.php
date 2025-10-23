<?php

    $json = [];
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $Email = post("Email");
        $ReservationNumber = post("ReservationNumber");

        if($Email && $ReservationNumber){
            $Check = Reservations::CheckByEmail($Email,$ReservationNumber);
            if ($Check){


                $UserAgent = $_SERVER["HTTP_USER_AGENT"];
                $RemoteAddr = $_SERVER["REMOTE_ADDR"];


                $query = $db->prepare("select * from PhpUserTokens WHERE MemberId=:MemberId and Expire>=CURRENT_TIMESTAMP and UserAgent=:UserAgent and IpAdress=:IpAdress");
                $query->execute([
                    "MemberId"=>$ReservationNumber,
                    "UserAgent"=>$UserAgent,
                    "IpAdress"=>$RemoteAddr
                ]);
                $isLogin = $query->fetch(PDO::FETCH_ASSOC);

                if($isLogin){
                    $json["token"]=$isLogin["Token"];
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
       k.tutar,h.doviz,h.resim,h.title,format(islem_tarihi,'dd.MM.yyyy / H:mm:ss') as islem_tarihi,FromC.Symbol,
       case when convert(date,d.tarih2,104)<convert(date,GETDATE(),104) then 1 else 0 end as IsPass,d.Durum
       from kayitlar k 
                        inner join homes h on h.id = k.evid 
                        inner join dolu d on d.kayitid = k.id
                        inner join Finance.Currency FromC on FromC.CurrencyName=k.doviz
                        inner join Finance.Currency ToC on ToC.CurrencyId=:DefaultCurrencyId
                        inner join Finance.RateDetail RD on RD.ToCurrencyId=ToC.CurrencyId and RD.FromCurrencyId=FromC.CurrencyId and RD.RateId=:RateId
                       where email=:email and not d.Durum=5");
                $query->execute([
                    "email"=>$OriginalReservation["email"],
                    "DefaultCurrencyId"=>1, //Hangi para biriminde ödeme yapılacak ise onun id si yazılabilir.
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

