<?php
SetHeader(200);
$json = [];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token = get("token");
    $id = post("id");

    if ($token){
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {


            $id = idHash($id,true);


            $Reservation = Payment::GetReservation($id);

            if ($Reservation){
                $Installments = CustomInstallment::GetInstallments($Reservation,post("binNumber"));
                $json["installments"]=$Installments;

                $json["data"]=$Reservation;


            }else
                $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];
        }else{
            $json["error"]=$Language["errors"]["payment"]["notAuth"];
        }
    }else{
        $json["error"]=$Language["errors"]["payment"]["invalidToken"];
    }
}


echo json_encode($json);