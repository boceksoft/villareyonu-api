<?php

$rez_no= post("rez_no");

$token = get("token");
$id = post("id");
if ($token){
    $PhpUserTokens = Login::IsLogin($token);
    if ($PhpUserTokens) {

        $id = idHash($id,true);
        $Reservation = Payment::GetReservation($id);
        if ($Reservation){

            $domain_= BASEAPI;
            $expiryDate=post("expiryDate");
            $expiryDate=explode("/",$expiryDate);
            $year = $expiryDate[1];
            $month = $expiryDate[0];

            $query = $db->prepare("select * from Finance.VirtualPosSettings where VirtualPosId=1");
            $query->execute([]);
            $VirtualPosSettings = $query->fetchAll(PDO::FETCH_ASSOC);
            $VirtualPosId=1;
            $MerchantId = ValletApi::getValueByKey($VirtualPosSettings,"MerchantId");
            $MerchantPassword = ValletApi::getValueByKey($VirtualPosSettings,"MerchantPassword");

            $mpiServiceUrl=	"https://3dsecure.vakifbank.com.tr/MPIAPI/MPI_Enrollment.aspx"; // Dok?mandaki Enrollment URLi
            $krediKartiNumarasi = str_replace([" ","-","."],"",post("cardNumber"));
            $sonKullanmaTarihi = $year.$month;
            $kartTipi = 100;
            $tutar = number_format($Reservation["Price"],2,".","");
            $paraKodu = $Reservation["PosCode"];
            $taksitSayisi = "";
            $islemNumarasi = $Reservation["id"]."-".date("YmdHis");
            $uyeIsyeriNumarasi = $MerchantId;
            $uyeIsYeriSifresi = $MerchantPassword;

            $SuccessURL = $domain_."/VakifBankResponse?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY;
            $FailureURL = $domain_."/VakifBankResponse?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY;
            $ekVeri = ""; // Optional

            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL,$mpiServiceUrl);
            curl_setopt($ch,CURLOPT_POST,TRUE);
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($ch,CURLOPT_HTTPHEADER,array("Content-Type"=>"application/x-www-form-urlencoded"));
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);

            $postFields = [
                "Pan" => $krediKartiNumarasi,
                "ExpiryDate" => $sonKullanmaTarihi,
                "PurchaseAmount" => $tutar,
                "Currency" => $paraKodu,
                "BrandName" => $kartTipi,
                "VerifyEnrollmentRequestId" => $islemNumarasi,
                "SessionInfo" => $ekVeri,
                "MerchantId" => $uyeIsyeriNumarasi,
                "MerchantPassword" => $uyeIsYeriSifresi,
                "SuccessUrl" => $SuccessURL,
                "FailureUrl" => $FailureURL,
                "InstallmentCount" => $taksitSayisi
            ];

            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));


            $resultXml = curl_exec($ch);
            curl_close($ch);

            $result = SonucuOku($resultXml);

            if($result["Status"] == "Y") {
                $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId,data) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId,:data)");
                $result = $query->execute([
                    "VirtualPosId"=>$VirtualPosId,
                    "Response"=>json_encode($result),
                    "kayitlarId"=>$Reservation["id"],
                    "conversationId"=>"enrollment".$islemNumarasi,
                    "data"=>idHash(json_encode($_POST))
                ]);
                if ($result)
                    $json["conversationId"]="enrollment".$islemNumarasi;
                else
                    $json["error"]="Sonuc eklenirken hata:";
            }else if ($result["Status"] == "N"){
                //$query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
                //$result = $query->execute([
                //    "VirtualPosId"=>$Reservation["VirtualPosId"],
                //    "Response"=>json_encode($result),
                //    "kayitlarId"=>$Reservation["id"],
                //    "conversationId"=>$islemNumarasi,
                //]);
                //if ($result)
                //    $json["conversationId"]=$islemNumarasi;
                //else
                    $json["error"]="3D Secure olmadan işleme devam edilemiyor.Lütfen başka bir kart deneyin.";
            }else{
                $json["error"]="Bir sorun oluştu. Kart Bilgilerinizi Kontrol edin. Hata Kodu:".$result["MessageErrorCode"];
            }
        }else
            $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];

    }else{
        $json["error"]=$Language["errors"]["payment"]["notAuth"];
    }
}else{
    $json["error"]=$Language["errors"]["payment"]["invalidToken"];
}
echo json_encode($json);
