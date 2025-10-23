<?php
SetHeader(200);
$json = [];

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token = get("token");
    $id = get("_");

    if ($token){
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {


            $id = idHash($id,true);


            $Reservation = Payment::GetReservation($id);

            if ($Reservation){
                if($Reservation["LastSendedPdfByConversationId"]==post("conversationId")){
                    $json["error"] = "Daha önce gönderilmiş.";
                }else{
                    $opt = array(
                        CURLOPT_URL => str_replace("www.","web.",DOMAIN)."/pdfgonder.asp?rezid=".$Reservation["id"],
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_SSL_VERIFYHOST => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_POST => true
                    );
                    $curl = curl_init();
                    curl_setopt_array($curl, $opt);
                    $response = curl_exec($curl);
                    $info = curl_getinfo($curl);
                    $query = $db->prepare("update dbo.kayitlar set LastSendedPdfByConversationId=:LastSendedPdfByConversationId where id=:id");
                    $query->execute([
                        "LastSendedPdfByConversationId"=>post("conversationId"),
                        "id"=>$Reservation["id"]
                    ]);
                }
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