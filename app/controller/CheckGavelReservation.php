<?php

SetHeader(200);
$json = [];


if($_SERVER["REQUEST_METHOD"] == "POST"){


    $ReservationNumber = post("ReservationNumber");

    if($ReservationNumber) {
        $query = $db->prepare("select k.id,d.tarih from kayitlar k inner join dolu d on d.kayitId = k.id and d.durum=3 and convert(date,GETDATE(),104) <= d.tarih 
                                                             and d.emlak=:emlak
              and k.id=:ReservationNumber");
        $query->execute([
            "emlak" => post("emlak"),
            "ReservationNumber" => $ReservationNumber
        ]);
        $gh = $query->fetch(PDO::FETCH_ASSOC);
        if ($gh) {
            $json["success"] = $gh;
        }else
            $json["error"] = $Language["errors"]["reservationNotFound"];
    }else{
        $json["error"]=$Language["errors"]["invalidUser"];
    }
}

echo json_encode($json);

//select k.id,d.tarih from kayitlar k inner join dolu d on d.kayitId = k.id and d.durum=3 and convert(date,GETDATE(),104) <= d.tarih and d.emlak="&page("id")&" and k.email=N'"&rq("email")&"' and k.id="&rq("reservation-number")&"
