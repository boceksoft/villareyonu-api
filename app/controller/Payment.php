<?php
SetHeader(200);
$json = [];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token = get("token");
    $id = post("id");

    try {
        if ($token){
            $PhpUserTokens = Login::IsLogin($token);
            if ($PhpUserTokens) {


                $id = idHash($id,true);


                $Reservation = Payment::GetReservation($id);

                if ($Reservation){
                    $Reservation["ip"] = $_SERVER["REMOTE_ADDR"];
                    $json["data"]=$Reservation;
                }else
                    $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];
            }else{
                $json["error"]=$Language["errors"]["payment"]["notAuth"];
            }
        }else{
            $json["error"]=$Language["errors"]["payment"]["invalidToken"];
        }
    }catch (Exception $e) {
        $json["error"]=$e->getMessage();
    }

}


echo json_encode($json);