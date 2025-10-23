<?php


$token = get("token");
$id = post("id");
if ($token){
    $PhpUserTokens = Login::IsLogin($token);
    if ($PhpUserTokens) {

        $id = idHash($id,true);
        $Reservation = Payment::GetReservation($id);
        if ($Reservation){
            $Vallet = new ValletApi(2);
            $nameParts = explode(" ", $Reservation["musteri"]);
            $firstName = $nameParts[0]; // İsim
            $lastName = isset($nameParts[1]) ? $nameParts[1] : ''; // Soyad (varsa)
            $order_data = array(
                'productName' => 'Rez. Kodu '.$Reservation["id"],
                'productData' => array(
                    array(
                        'productName'=>'Rez. Kodu '.$Reservation["id"],
                        'productPrice'=>$Reservation["Price"],
                        'productType'=>'FIZIKSEL_URUN',
                    ),
                ),
                'productType' => 'FIZIKSEL_URUN',
                'productsTotalPrice' => $Reservation["Price"],
                'orderPrice' => $Reservation["Price"],
                'currency' => $Reservation["CurrencyCode"],
                'orderId' => $Reservation["id"]."-".date("YmdHis"),
                'locale' => 'tr',
                'conversationId' => ValletApi::generateConversationId($Reservation["id"]),
                'buyerName' => $firstName,
                'buyerSurName' => $lastName,
                'buyerGsmNo' => $Reservation["telefon"],
                'buyerIp' => $_SERVER['REMOTE_ADDR'],
                'buyerMail' => $Reservation["email"],
                'buyerAdress' => '',
                'buyerCountry' => '',
                'buyerCity' => '',
                'buyerDistrict' => '',
            );
            $json["Response"]=$Vallet->create_payment_link($order_data);
            unset($json["Response"]["post_data"]);
            if ($json["Response"]["status"]=="error")
                $json["error"]=$json["Response"]["errorMessage"];
        }else
            $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];

    }else{
        $json["error"]=$Language["errors"]["payment"]["notAuth"];
    }
}else{
    $json["error"]=$Language["errors"]["payment"]["invalidToken"];
}
echo json_encode($json);