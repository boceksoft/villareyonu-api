<?php



if (post("Status")) {
    header("Content-Type: text/html; charset=utf-8");
    $explode = explode("-", post("VerifyEnrollmentRequestId"));
    //remove last element
    array_pop($explode);

    $query= $db->prepare("select Payments.*,C.PosCode,C.Symbol from Finance.Payments inner join Finance.Currency C on C.CurrencyId=Payments.CurrencyId where Link=:Link");
    $query->execute(["Link" => implode("-",$explode)]);
    $Payment = $query->fetch(PDO::FETCH_ASSOC);


    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, PaymentId,conversationId) VALUES (:VirtualPosId, :Response, :PaymentId,:conversationId)");
    $query->execute([
        "VirtualPosId" => 1,
        "Response" => json_encode($_POST),
        "PaymentId" => $Payment["PaymentId"],
        "conversationId" => post("VerifyEnrollmentRequestId"),
    ]);
    $PayByCreditCardPage = Page::GetById(456);
    if(post("Status") == "Y"){


        //3D Başarılı
        $query = $db->prepare("select * from Finance.VirtualPosSettings where VirtualPosId=1");
        $query->execute([]);
        $VirtualPosSettings = $query->fetchAll(PDO::FETCH_ASSOC);

        $MerchantId = ValletApi::getValueByKey($VirtualPosSettings,"MerchantId");
        $MerchantPassword = ValletApi::getValueByKey($VirtualPosSettings,"MerchantPassword");
        $TerminalNo = ValletApi::getValueByKey($VirtualPosSettings,"TerminalNo");

        $query = $db->prepare("select * from Finance.VirtualPosResponses where conversationId=:conversationId");
        $query->execute(["conversationId" => "enrollment".post("VerifyEnrollmentRequestId")]);
        $EnrollmentData = $query->fetch(PDO::FETCH_ASSOC);
        $EnrollmentData=json_decode(idHash($EnrollmentData["data"],true),2);


        $PostUrl = 'https://onlineodeme.vakifbank.com.tr:4443/VposService/v3/Vposreq.aspx'; //Dokümanda yer alan Prod VPOS URL i. Testlerinizi test ortamýnda gerçekleþtiriyorsanýz dokümandaki test URL ini kullanmalýsýnýz.
        $IsyeriNo = $_POST["MerchantId"];
        $IsyeriSifre = $MerchantPassword;
        $KartNo = $_POST["Pan"];
        $KartAy = substr($_POST["Expiry"],2,2);
        $KartYil = "20".substr($_POST["Expiry"],0,2);
        $KartCvv = $EnrollmentData["cvv"];
        //remove last 2 characters
        $Tutar = substr($_POST["PurchAmount"],0,-2);
        $CAVV = $_POST["Cavv"];
        $Eci = $_POST["Eci"];
        $SiparID = post("VerifyEnrollmentRequestId");
        $IslemTipi = "Sale";
        $TutarKodu = $_POST["PurchCurrency"];
        $ClientIp = $_SERVER['REMOTE_ADDR']; // ödemeyi gerçekleþtiren kullanýcýnýn IP bilgisi alýnarak bu alanda gönderilmelidir.
        //$Taksit     = $_POST["InstallmentCount"];

        $PosXML = 'prmstr=<VposRequest>
<MerchantId>' . $IsyeriNo . '</MerchantId>
<ECI>' . $Eci . '</ECI>
<CAVV>' . $CAVV . '</CAVV>
<MpiTransactionId>' . $SiparID . '</MpiTransactionId>
<Password>' . $IsyeriSifre . '</Password>
<TerminalNo>' . $TerminalNo . '</TerminalNo>
<TransactionType>' . $IslemTipi . '</TransactionType>
<TransactionId>' . $SiparID . '</TransactionId>';
        $PosXML = $PosXML . '<CurrencyAmount>' . $Tutar . '</CurrencyAmount>
<CurrencyCode>' . $TutarKodu . '</CurrencyCode>
<Pan>' . $KartNo . '</Pan>
<Expiry>' . $KartYil . $KartAy . '</Expiry>'; //3D Secure işyerlerimizin bu parametreyi kullanmasına lüzum yoktur.
        $PosXML = $PosXML . '<Cvv>' . $KartCvv . '</Cvv>
<TransactionDeviceSource>0</TransactionDeviceSource>
<ClientIp>' . $ClientIp . '</ClientIp></VposRequest>';



        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $PostUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $PosXML);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 59);
        curl_setopt($ch,CURLOPT_SSLVERSION,CURL_SSLVERSION_TLSv1_1);
        // curl_setopt ($ch, CURLOPT_CAINFO, "c:/php/ext/cacert.pem");

        $result = curl_exec($ch);

        // Check for errors and display the error message
        if ($errno = curl_errno($ch)) {
            $error_message = curl_strerror($errno);
            echo "cURL error ({$errno}):\n {$error_message}";
        }
        curl_close($ch);

        //convert xml result to array
        $result = SimpleXML_load_string($result);
        $rXml = json_decode(json_encode($result), true);



        if($rXml["ResultCode"]=="0000"){
            //Payment::Success($r["id"],"VAKIFBANK");

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
            $Mail->setSubject("Ödemeniz alındı (Vakıfbank)");
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

            //Parayı Çekti
        }else{

            echo "Ödeme başarısız oldu.Yönlendiriliyorsunuz... Kod: " . $rXml["ResultCode"] . " Mesaj: " . $rXml["ResultDetail"];
        }
        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, PaymentId,conversationId) VALUES (:VirtualPosId, :Response, :PaymentId,:conversationId)");
        $query->execute([
            "VirtualPosId" => 1,
            "Response" => json_encode($rXml),
            "PaymentId" => $Payment["PaymentId"],
            "conversationId" => "3d".post("VerifyEnrollmentRequestId"),
        ]);

        $qsql["domain"] = DOMAIN;
        header("Location: " . $qsql["domain"] . "/" . $Payment["Link"] . "?conversationId=3d" . post("VerifyEnrollmentRequestId"));

        exit;

    }else{
        $query = $db->prepare("update Finance.Payments set PaymentStatus=3 where PaymentId=:PaymentId");
        $query->execute(["PaymentId" => $Payment["PaymentId"]]);


        $qsql["domain"] = DOMAIN;
        header("Location: " . $qsql["domain"] . "/" . $Payment["Link"] . "?conversationId=" . post("VerifyEnrollmentRequestId"));
    }
}
