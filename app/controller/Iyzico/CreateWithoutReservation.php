<?php
function calculateHmacSHA256Signature($params)
{
    $secretKey = IyzipayBootstrap::options()->getSecretKey();
    $dataToSign = implode(':', $params);
    $mac = hash_hmac('sha256', $dataToSign, $secretKey, true);
    return bin2hex($mac);
}

$PaymentId= post("paymentId");

//Durumu Bekliyor olan ve süresi geçmemiş olan ödemeyi çekiyoruz.
$query= $db->prepare("select Payments.*,C.PosCode from Finance.Payments inner join Finance.Currency C on C.CurrencyId=Payments.CurrencyId where PaymentId=:PaymentId and ExpiredOn>=getdate() and PaymentStatus=0");
$query->execute(["PaymentId" => $PaymentId]);
$Payment = $query->fetch(PDO::FETCH_ASSOC);

if ($Payment){

    $domain_= BASEAPI;

    # create request class
    $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
    $request->setLocale(\Iyzipay\Model\Locale::TR);
    $binNumber=substr(str_replace("-","",post("cardNumber")),0,6);
    $request->setConversationId("123456789");
    $request->setBinNumber($binNumber);
    $request->setPrice($Payment["Price"]);

    # make request
    $installmentInfo = \Iyzipay\Model\InstallmentInfo::retrieve($request, IyzipayBootstrap::options());
    $installmentInfo = json_decode($installmentInfo->getRawResult(),2);
    $request=null;

    if ($installmentInfo["status"] == "success") {

        $installment=array_values(array_filter($installmentInfo["installmentDetails"][0]["installmentPrices"], function ($value) {
            return $value["installmentNumber"] == post("installement");
        }))[0];

        $request = new \Iyzipay\Request\CreatePaymentRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId($Payment["Link"]."-".date("YmdHis"));
        $request->setPrice($Payment["Price"]);
        $request->setPaidPrice(str_replace(",",".",$installment["totalPrice"]));
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setInstallment(post("installement"));
        $request->setBasketId($Payment["PaymentId"]);
        $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $request->setCallbackUrl(BASEAPI."/Iyzico/CallbackWithoutReservation");

        $paymentCard = new \Iyzipay\Model\PaymentCard();
        $paymentCard->setCardHolderName(post("name"));
        $paymentCard->setCardNumber(str_replace("-","",post("cardNumber")));
        $paymentCard->setExpireMonth(post("month"));
        $paymentCard->setExpireYear("20".post("year"));
        $paymentCard->setCvc(post("cvv"));
        $paymentCard->setRegisterCard(0);
        $request->setPaymentCard($paymentCard);

        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($Payment["PaymentId"]);
        $buyer->setName(post("name"));
        $buyer->setSurname("-");
        $buyer->setGsmNumber(post("phone"));
        $buyer->setEmail(post("email"));
        $buyer->setIdentityNumber("11111111111");
        $buyer->setLastLoginDate("");
        $buyer->setRegistrationDate("");
        $buyer->setRegistrationAddress("-");
        $buyer->setIp($_SERVER["REMOTE_ADDR"]);
        $buyer->setCity("-");
        $buyer->setCountry("-");
        $buyer->setZipCode("-");
        $request->setBuyer($buyer);

        $billingAddress = new \Iyzipay\Model\Address();
        $billingAddress->setContactName(post("name"));
        $billingAddress->setCity("-");
        $billingAddress->setCountry("-");
        $billingAddress->setAddress("-");
        $billingAddress->setZipCode("-");
        $request->setBillingAddress($billingAddress);

        $basketItems = array();
        $firstBasketItem = new \Iyzipay\Model\BasketItem();
        $firstBasketItem->setId($Payment["Link"]);
        $firstBasketItem->setName($Payment["Link"]);
        $firstBasketItem->setCategory1("Collectibles");
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
        $firstBasketItem->setPrice($Payment["Price"]);
        $basketItems[0] = $firstBasketItem;
        $request->setBasketItems($basketItems);


        $threedsInitialize = \Iyzipay\Model\ThreedsInitialize::create($request, IyzipayBootstrap::options());
        $paymentArr = json_decode($threedsInitialize->getRawResult(),2);

        if($paymentArr["status"] == "success"){

            #verify signature
            $paymentId = $threedsInitialize->getPaymentId();
            $conversationId = $threedsInitialize->getConversationId();
            $signature = $threedsInitialize->getSignature();

            $calculatedSignature = calculateHmacSHA256Signature(array($paymentId, $conversationId));
            $verified = $signature == $calculatedSignature;
            if ($verified){
                $query = $db->prepare("insert into iyzico_response (conversationId, threeDSHtmlContent) values (:conversationId, :threeDSHtmlContent)");
                $i = $query->execute([
                    "conversationId"=>$conversationId,
                    "threeDSHtmlContent"=>$paymentArr["threeDSHtmlContent"]
                ]);
                if ($i){

                    $query = $db->prepare("insert into Finance.VirtualPosResponses (VirtualPosId, Response, PaymentId,conversationId,data) VALUES (:VirtualPosId, :Response, :PaymentId,:conversationId,:data)");
                    $result = $query->execute([
                        "VirtualPosId"=>3,
                        "Response"=>json_encode($paymentArr),
                        "PaymentId"=>$Payment["PaymentId"],
                        "conversationId"=>"enrollment".$conversationId,
                        "data"=>idHash(json_encode($_POST))
                    ]);

                    $json["success"]=BASEAPI."/Iyzico/PaymentForm?_=".$conversationId;
                }else{

                    $query = $db->prepare("update  iyzico_response set threeDSHtmlContent=:threeDSHtmlContent where conversationId=:conversationId");
                    $u = $query->execute([
                        "conversationId"=>$conversationId,
                        "threeDSHtmlContent"=>$paymentArr["threeDSHtmlContent"]
                    ]);

                    if ($u){
                        $json["success"]=BASEAPI."/Iyzico/PaymentForm?_=".$conversationId;
                    }else {
                        $json["error"] = "Bir sorun oluştu.";
                    }
                }
            }else
                $json["error"]="3D doğrulanamadı";
        }else
            $json["error"]=$paymentArr["errorMessage"];

    }else
        $json["error"]=$installmentInfo["errorMessage"];


}else
    $json["error"]="Ödeme bulunamadı.";

echo json_encode($json);
