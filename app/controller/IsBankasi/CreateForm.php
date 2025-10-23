<?php
SetHeader(200);
$json=[];

$JSON_POST = json_decode(file_get_contents('php://input'), true);

$rez_no= post("rez_no");

if(get("conversationId")){
    $id = get("rez_no");
    $id = idHash($id,true);
    $query = $db->prepare("select paymentHtml from kayitlar where id=:id");
    $query->execute(["id"=>$id]);
    $html = $query->fetch(PDO::FETCH_ASSOC)["paymentHtml"];
    header("Content-Type: text/html; charset=utf-8");
    echo $html;
    exit;
}
$token = get("token");
$id = post("id");
if ($token){
    $PhpUserTokens = Login::IsLogin($token);
    if ($PhpUserTokens) {

        $id = idHash($id,true);
        $Reservation = Payment::GetReservation($id);
        if ($Reservation){
            $domain_= BASEAPI;
            $query = $db->prepare("select * from Finance.VirtualPosSettings where VirtualPosId=:VirtualPosId");
            $query->execute(["VirtualPosId"=>5]);
            $virtualPosSettings = $query->fetchAll(PDO::FETCH_ASSOC);
            $VirtualPosSettingsTransformed = [];
            foreach($virtualPosSettings as $setting) {
                $VirtualPosSettingsTransformed[$setting["Name"]] = $setting["Value"];
            }
            $orderId=$Reservation["id"]."-".date("YmdHis");

            //if(post("tur")==FirstPaymentType::$OnOdeme){
            //    $Reservation["Price"] = $Reservation["on_odeme"];
            //    $Reservation["tur"] = FirstPaymentType::$OnOdeme;
            //}else if (post("tur")==FirstPaymentType::$Tamami){
            //    $Reservation["Price"] = $Reservation["toplam_tutar"];
            //    $Reservation["tur"] = FirstPaymentType::$Tamami;
            //}

            $Price = number_format($Reservation["Price"],2,".","");
            $TaksitSayisi="";
            if(post("installement")>1){
                //ilk 6 karakteri al



                $Installments = CustomInstallment::GetInstallments($Reservation,substr(str_replace([" ","-"],[""],post("cardNumber")),0,6));
                $Taksit = array_values(array_filter($Installments, function($Installment){
                    return $Installment["installmentNumber"] == post("installement");
                }));
                if($Taksit[0]){
                    $Price = $Taksit[0]["totalPrice"];
                    $TaksitSayisi =  $Taksit[0]["installmentNumber"];
                }

            }

            $expiryDate=post("expiryDate");
            $expiryDate=explode("/",$expiryDate);
            $year = $expiryDate[1];
            $month = $expiryDate[0];

            $form_names = [
                "pan"=> str_replace([" ","-"],[""],post("cardNumber")),
                "cv2"=>post("cvv"),
                "Ecom_Payment_Card_ExpDate_Year"=>$year,
                "Ecom_Payment_Card_ExpDate_Month"=>$month,
                "clientid"=>$VirtualPosSettingsTransformed["ClientId"],
                "amount"=>$Price,
                "okurl"=>$domain_."/IsBankasi/Response?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY,
                "failUrl"=>$domain_."/IsBankasi/Response?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY,
                "TranType"=>"Auth",
                "Instalment"=>$TaksitSayisi,
                "callbackUrl"=>$domain_."/IsBankasi/Response?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY,
                "currency"=>$Reservation["PosCode"],
                "rnd"=>rand("10000","99999"),
                "storetype"=>"3D_PAY",
                "hashAlgorithm"=>"ver3",
                "lang"=>"tr",
                "BillToName"=>post("PaymentName"),
                "BillToCompany"=>post("PaymentName"),
                "sipbil"=>date("YmdHis"),
                "SID"=>$orderId,
                "ip"=>$_SERVER['REMOTE_ADDR'],
                "odeme_tipi"=>"Kredi Kartı",
                "tampara"=>$Price,
                "isim"=>$Reservation["musteri"],
                "email"=>$Reservation["email"],
                "telefon"=>$Reservation["telefon"],
                "mesaj"=>"",
                "rez_no"=>$id,
                "VirtualPosId"=>5,
                "name"=>$Reservation["musteri"],
                "phone"=>$Reservation["telefon"],
                "message"=>"",
                "clientIp"=>$_SERVER['REMOTE_ADDR'],
                "tur"=>$Reservation["tur"]
            ];
            $html = "<html><head><meta charset='utf-8'> </head><body>";
            $html.= "<form action='".$VirtualPosSettingsTransformed["Action"]."' method='POST'>";

            $postParams = array();
            foreach ($form_names as $key => $value){
                array_push($postParams, $key);
                $html.= "<input type=\"hidden\" name=\"" .$key ."\" value=\"" .$value."\" /><br />";
            }

            natcasesort($postParams);

            $hashval = "";
            foreach ($postParams as $param){
                $paramValue = $form_names[$param];
                $escapedParamValue = str_replace("|", "\\|", str_replace("\\", "\\\\", $paramValue));

                $lowerParam = strtolower($param);
                if($lowerParam != "hash" && $lowerParam != "encoding" )	{
                    $hashval = $hashval . $escapedParamValue . "|";
                }
            }

            $storeKey = $VirtualPosSettingsTransformed["StoreKey"];
            $escapedStoreKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));
            $hashval = $hashval . $escapedStoreKey;

            $calculatedHashValue = hash('sha512', $hashval);
            $hash = base64_encode (pack('H*',$calculatedHashValue));

            $html.= "<input type=\"hidden\" name=\"HASH\" value=\"" .$hash."\" />";

            $html.= "</form></body><script>document.forms[0].submit();</script></html>";


            if(post("rez_no")){
                $query = $db->prepare("update kayitlar set paymentHtml=:paymentHtml where id=:id");
                $query->execute(["paymentHtml"=>$html,"id"=>post("rez_no")]);
            }
            $json["conversationId"]= $orderId;

        }else
            $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];

    }else{
        $json["error"]=$Language["errors"]["payment"]["notAuth"];
    }
}else{
    $json["error"]=$Language["errors"]["payment"]["invalidToken"];
}
echo json_encode($json);
?>