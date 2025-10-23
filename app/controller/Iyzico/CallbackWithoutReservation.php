<?php
SetHeader(200);
$json = [];
function calculateHmacSHA256Signature($params)
{
    $secretKey = IyzipayBootstrap::options()->getSecretKey();
    $dataToSign = implode(':', $params);
    $mac = hash_hmac('sha256', $dataToSign, $secretKey, true);
    return bin2hex($mac);
}

if(post("conversationId")){
    $ex = explode("-",post("conversationId"));
    array_pop($ex);

    $query= $db->prepare("select Payments.*,C.PosCode,C.Symbol from Finance.Payments inner join Finance.Currency C on C.CurrencyId=Payments.CurrencyId where Link=:Link");
    $query->execute(["Link" => implode("-",$ex)]);
    $Payment = $query->fetch(PDO::FETCH_ASSOC);


    $query = $db->prepare("select * from Finance.VirtualPosResponses where conversationId=:conversationId");
    $query->execute(["conversationId" => "enrollment".post("conversationId")]);
    $EnrollmentData = $query->fetch(PDO::FETCH_ASSOC);
    $EnrollmentData=json_decode(idHash($EnrollmentData["data"],true),2);

    if (post("status")=="success") {



        # create request class
        $request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(post("conversationId"));
        $request->setPaymentId(post("paymentId"));
        $request->setConversationData(post("conversationData"));

        # make request
        $threedsPayment = \Iyzipay\Model\ThreedsPayment::create($request, IyzipayBootstrap::options());


        #verify signature
        $paymentId = $threedsPayment->getPaymentId();
        $currency = $threedsPayment->getCurrency();
        $basketId = $threedsPayment->getBasketId();
        $conversationId = $threedsPayment->getConversationId();
        $paidPrice = $threedsPayment->getPaidPrice();
        $price = $threedsPayment->getPrice();
        $signature = $threedsPayment->getSignature();


        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, PaymentId,conversationId) VALUES (:VirtualPosId, :Response, :PaymentId,:conversationId)");
        $query->execute([
            "VirtualPosId"=>3,
            "Response"=>$threedsPayment->getRawResult(),
            "PaymentId"=>$Payment["PaymentId"],
            "conversationId"=>"3d".$conversationId,
        ]);


        if($threedsPayment->getStatus() == "success"){
            //Parayı Çekti
            $mailicerik= MailTemplate::Index("default.txt",0);
            $tablex = "<table style='width:100%;box-sizing:border-box;'>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>İsim</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$EnrollmentData["name"]}</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Email</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$EnrollmentData["email"]}</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Telefon</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$EnrollmentData["phone"]}</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Link</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$Payment["Link"]}</td>";
            $tablex .= "</tr>";
            $tablex .= "<tr>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Tutar</b></td>";
            $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$Payment["Symbol"]} {$Payment["Price"]}</td>";
            $tablex .= "</tr>";

            if($Payment["ReservationId"]>0){
                $tablex .= "<tr>";
                $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;'><b style='color:#797575;box-sizing:border-box;'>Rezervasyon No</b></td>";
                $tablex .= "<td style='border-bottom:1px dashed #f1f1f1;box-sizing:border-box;text-align:right'>{$Payment["ReservationId"]}</td>";
                $tablex .= "</tr>";
            }

            $tablex .= "</table>";

            $mailicerik = str_replace("{-mail-icerik-}",		$tablex,			$mailicerik);
            $Mail = new SendMail();
            $Mail->setEmail($config['smtp_username']);
            $Mail->setContent($mailicerik);
            $Mail->setReceiverName($config['smtp_sendFrom']);
            $Mail->setSubject("Ödemeniz alındı (Iyzico)");
            $Mail->Send();

            $Mail = new SendMail();
            $Mail->setEmail($EnrollmentData['email']);
            $Mail->setContent($mailicerik);
            $Mail->setReceiverName($config['smtp_sendFrom']);
            $Mail->setSubject("Ödemeniz alındı");
            $Mail->Send();

            $query = $db->prepare("update Finance.Payments set PaymentStatus=2,CustomerName=:CustomerName,CustomerEmail=:CustomerEmail,CustomerPhone=:CustomerPhone where PaymentId=:PaymentId");
            $query->execute([
                "PaymentId" => $Payment["PaymentId"],
                "CustomerName" => $EnrollmentData["name"],
                "CustomerEmail" => $EnrollmentData["email"],
                "CustomerPhone" => $EnrollmentData["phone"],
            ]);
        }

        $calculatedSignature = calculateHmacSHA256Signature(array($paymentId, $currency, $basketId, $conversationId, $paidPrice, $price));
        $verified = $signature == $calculatedSignature;



    }else{
        //3d Doğrulama başarılı değil
        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, PaymentId,conversationId) VALUES (:VirtualPosId, :Response, :PaymentId,:conversationId)");
        $query->execute([
            "VirtualPosId"=>3,
            "Response"=>json_encode($_POST),
            "PaymentId"=>$Payment["PaymentId"],
            "conversationId"=>"3d".post("conversationId"),
        ]);
    }




    $PayByCreditCardPage = Page::GetById(456);
    $qsql["domain"]=DOMAIN;
    header("Location: ".$qsql["domain"]."/".$Payment["Link"]."?conversationId=3d".post("conversationId"));

}