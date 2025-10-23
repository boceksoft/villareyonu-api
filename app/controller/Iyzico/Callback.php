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
    $id=idHash($ex[0],true);
    $r = Payment::GetReservation($id);

    if (post("status")=="success") {


        # create request class
        $request = new \Iyzipay\Request\CreateThreedsPaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        if($r["site"]=="2")
            $request->setLocale(\Iyzipay\Model\Locale::EN);
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


        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
        $query->execute([
            "VirtualPosId"=>3,
            "Response"=>$threedsPayment->getRawResult(),
            "kayitlarId"=>$r["id"],
            "conversationId"=>$conversationId,
        ]);


        if($threedsPayment->getStatus() == "success"){
            //Parayı Çekti
            Payment::Success($id,"IYZICO");
        }

        $calculatedSignature = calculateHmacSHA256Signature(array($paymentId, $currency, $basketId, $conversationId, $paidPrice, $price));
        $verified = $signature == $calculatedSignature;

    }else{
        //3d Doğrulama başarılı değil
        $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, kayitlarId,conversationId) VALUES (:VirtualPosId, :Response, :kayitlarId,:conversationId)");
        $query->execute([
            "VirtualPosId"=>3,
            "Response"=>json_encode($_POST),
            "kayitlarId"=>$r["id"],
            "conversationId"=>post("conversationId"),
        ]);
    }


    $PayByCreditCardPage = Page::GetById(22);
    header("Location: ".SiteUrl($PayByCreditCardPage["url"]."?_=".idHash($r["id"])."&conversationId=".post("conversationId")));

}