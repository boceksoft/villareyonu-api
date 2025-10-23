<?php
function calculateHmacSHA256Signature($params)
{
    $secretKey = IyzipayBootstrap::options()->getSecretKey();
    $dataToSign = implode(':', $params);
    $mac = hash_hmac('sha256', $dataToSign, $secretKey, true);
    return bin2hex($mac);
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

            # create request class
            $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();

            $request->setLocale(\Iyzipay\Model\Locale::TR);
            if ($Reservation["site"]=="2")
                $request->setLocale(\Iyzipay\Model\Locale::EN);

            $binNumber=substr(str_replace(["-", " "],"",post("cardNumber")),0,6);
            $request->setConversationId("123456789");
            $request->setBinNumber($binNumber);
            $request->setPrice($Reservation["Price"]);

            $expiryDate=explode("/",post("expiryDate"));

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
                if ($Reservation["site"]=="2")
                    $request->setLocale(\Iyzipay\Model\Locale::EN);
                $request->setConversationId(post("id")."-".date("YmdHis"));
                $request->setPrice($Reservation["Price"]);
                $request->setPaidPrice(str_replace(",",".",$installment["totalPrice"]));
                $request->setCurrency(\Iyzipay\Model\Currency::TL);
                $request->setInstallment(post("installement"));
                $request->setBasketId($id);
                $request->setPaymentChannel(\Iyzipay\Model\PaymentChannel::WEB);
                $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
                $request->setCallbackUrl(BASEAPI."/Iyzico/Callback?language=".CURRENT_LANGUAGE."&currency=".CURRENT_CURRENCY);

                $paymentCard = new \Iyzipay\Model\PaymentCard();
                $paymentCard->setCardHolderName(post("PaymentName"));
                $paymentCard->setCardNumber(str_replace(["-", " "],"",post("cardNumber")));
                $paymentCard->setExpireMonth($expiryDate[0]);
                $paymentCard->setExpireYear("20".$expiryDate[1]);
                $paymentCard->setCvc(post("cvv"));
                $paymentCard->setRegisterCard(0);
                $request->setPaymentCard($paymentCard);

                $buyer = new \Iyzipay\Model\Buyer();
                $buyer->setId($Reservation["id"]);
                $buyer->setName($Reservation["musteri"]);
                $buyer->setSurname("-");
                $buyer->setGsmNumber($Reservation["telefon"]);
                $buyer->setEmail($Reservation["email"]);
                $buyer->setIdentityNumber($Reservation["tc"]=="" ?  "11111111111" : $Reservation["tc"]);
                $buyer->setLastLoginDate("");
                $buyer->setRegistrationDate("");
                $buyer->setRegistrationAddress($Reservation["adres"]=="" ?  "-" : $Reservation["adres"]);
                $buyer->setIp($_SERVER["REMOTE_ADDR"]);
                $buyer->setCity("-");
                $buyer->setCountry("-");
                $buyer->setZipCode("-");
                $request->setBuyer($buyer);

                $billingAddress = new \Iyzipay\Model\Address();
                $billingAddress->setContactName($Reservation["musteri"]);
                $billingAddress->setCity("-");
                $billingAddress->setCountry("-");
                $billingAddress->setAddress($Reservation["adres"]=="" ?  "-" : $Reservation["adres"]);
                $billingAddress->setZipCode("-");
                $request->setBillingAddress($billingAddress);

                $basketItems = array();
                $firstBasketItem = new \Iyzipay\Model\BasketItem();
                $firstBasketItem->setId($Reservation["name"]);
                $firstBasketItem->setName($Reservation["baslik"]);
                $firstBasketItem->setCategory1("Collectibles");
                $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL);
                $firstBasketItem->setPrice($Reservation["Price"]);
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
            $json["error"]=$Language["errors"]["payment"]["reservationNotFound"];

    }else{
        $json["error"]=$Language["errors"]["payment"]["notAuth"];
    }
}else{
    $json["error"]=$Language["errors"]["payment"]["invalidToken"];
}
echo json_encode($json);