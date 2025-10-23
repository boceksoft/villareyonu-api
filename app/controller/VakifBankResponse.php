<?php
if (post("Status")) {
    header("Content-Type: text/html; charset=utf-8");
    $explode = explode("-", post("VerifyEnrollmentRequestId"));
    $r = Payment::GetReservation($explode[0]);
    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
    $query->execute([
        "VirtualPosId" => 1,
        "Response" => json_encode($_POST),
        "kayitlarId" => $r["id"],
        "conversationId" => post("VerifyEnrollmentRequestId"),
    ]);
    $PayByCreditCardPage = Page::GetById(22);
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

        print_r(htmlspecialchars($PosXML));

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
            Payment::Success($r["id"],"VAKIFBANK");
            //Parayı Çekti
        }else{
            echo "Ödeme başarısız oldu.Yönlendiriliyorsunuz... Kod: " . $rXml["ResultCode"] . " Mesaj: " . $rXml["ResultDetail"];
        }
        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
        $query->execute([
            "VirtualPosId" => 1,
            "Response" => json_encode($rXml),
            "kayitlarId" => $r["id"],
            "conversationId" => "3d".post("VerifyEnrollmentRequestId"),
        ]);

        $qsql["domain"] = DOMAIN;
        header("Location: " . SiteUrl($PayByCreditCardPage["url"] . "?_=" . idHash($r["id"]) . "&conversationId=3d" . post("VerifyEnrollmentRequestId")));

        exit;

    }else{
        $qsql["domain"] = DOMAIN;
        header("Location: " . SiteUrl($PayByCreditCardPage["url"] . "?_=" . idHash($r["id"]) . "&conversationId=" . post("VerifyEnrollmentRequestId")));
    }
}
