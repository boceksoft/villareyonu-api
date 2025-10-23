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
            $query->execute(["VirtualPosId"=>6]);
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

            $Price = number_format($Reservation["Price"],2,"","");
            $TaksitSayisi="";
            if(post("installement")>1){
                //ilk 6 karakteri al



                $Installments = CustomInstallment::GetInstallments($Reservation,substr(str_replace([" ","-"],[""],post("cardNumber")),0,6));
                $Taksit = array_values(array_filter($Installments, function($Installment){
                    return $Installment["installmentNumber"] == post("installement");
                }));
                if($Taksit[0]){
                    $Price = str_replace(".","",$Taksit[0]["totalPrice"]);
                    $TaksitSayisi =  $Taksit[0]["installmentNumber"];
                }

            }

            $expiryDate=post("expiryDate");
            $expiryDate=explode("/",$expiryDate);
            $year = $expiryDate[1];
            $month = $expiryDate[0];

            $form_names = [
                "cardnumber"=> str_replace([" ","-"],[""],post("cardNumber")),
                "cardcvv2"=>post("cvv"),
                "cardexpiredateyear"=>$year,
                "cardexpiredatemonth"=>$month,
                "mode"=>"PROD",
                "apiversion"=>"v0.01",
                "terminalprovuserid"=>"PROVAUT",
                "terminaluserid"=>$VirtualPosSettingsTransformed["TerminalUserID"],
                "terminalmerchantid"=>$VirtualPosSettingsTransformed["TerminalMerchantID"],
                "txntype"=>"sales",
                "secure3dsecuritylevel"=>"3D_PAY",
                "txnamount"=>$Price,
                "txncurrencycode"=>$Reservation["PosCode"],
                "txninstallmentcount"=>$TaksitSayisi,
                "orderid"=>$orderId,
                "terminalid"=>$VirtualPosSettingsTransformed["TerminalID"],
                "successurl"=>$domain_."/Garanti/Response?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY,
                "errorurl"=>$domain_."/Garanti/Response?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY,
                "customeripaddress"=>$_SERVER['REMOTE_ADDR'],
                "customeremailaddress"=>$Reservation["email"],
                "isim"=>$Reservation["musteri"],
                "email"=>$Reservation["email"],
                "telefon"=>$Reservation["telefon"],
                "mesaj"=>$Reservation["mesaj"]
            ];
            $html = "<html><head><meta charset='utf-8'> </head><body>";
            $html.= "<form action='".$VirtualPosSettingsTransformed["Action"]."' method='POST'>";


            $data = [
                $VirtualPosSettingsTransformed["ProvisionPassword"],
                str_pad((int)$VirtualPosSettingsTransformed["TerminalUserID"], 9, 0, STR_PAD_LEFT)
            ];
            $shaData =  strtoupper(sha1(implode('', $data)));


            $hashedDataArr = [
                $form_names["terminaluserid"], $form_names["orderid"], $form_names["txnamount"],
                $form_names["successurl"], $form_names["errorurl"],
                $form_names["txntype"], $form_names["txninstallmentcount"],
                $VirtualPosSettingsTransformed["StoreKey"], $shaData
            ];

            $shaData = strtoupper(sha1(implode('', $hashedDataArr)));
            $postParams = array();
            foreach ($form_names as $key => $value){
                array_push($postParams, $key);
                $html.= "<input type=\"hidden\" name=\"" .$key ."\" value=\"" .$value."\" /><br />";
            }
            $html.= "<input type=\"hidden\" name=\"secure3dhash\" value=\"" .$shaData."\" /><br />";



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