<?php

SetHeader(200);
$json=[];

$conversationId = get("_");
if($conversationId){
    $query = $db->prepare("select * from Finance.VirtualPosResponses where conversationId=:conversationId");
    $query->execute(["conversationId"=>$conversationId]);
    $iyzico_response= $query->fetch(PDO::FETCH_ASSOC);
    if ($iyzico_response){
        $callback=json_decode($iyzico_response["Response"],2);

        //Ödeme Başarılı olmuş.
        $json["response"]=$callback;
        if($iyzico_response["VirtualPosId"]=="1"){
            //Vakıfbank
            $json["status"]=$callback["ResultCode"]=="0000";
        }else if($iyzico_response["VirtualPosId"]=="2"){
            //Vallet42310
            $json["status"]=$callback["paymentStatus"]=="paymentOk";
        }else if ($iyzico_response["VirtualPosId"]=="3"){
            //Iyzico
            $json["status"]=$callback["status"]=="success";
        }else if ($iyzico_response["VirtualPosId"]=="5") {
            //Iyzico
            $json["status"] = $callback["ProcReturnCode"] == "00";
        }else if ($iyzico_response["VirtualPosId"]=="6") {
            //Iyzico
            $json["status"] = $callback["procreturncode"] == "00";
        }
        if(!$json["status"]){
            $json["response"]["FinallyError"]=$Language["paymentError"].". ".$json["response"]["bankMessage"].$json["response"]["ErrorMessage"].$json["response"]["errorMessage"].$json["response"]["ResultDetail"].$json["response"]["ErrMsg"].$json["response"]["mdErrorMsg"].$json["response"]["errmsg"];

        }

    }else{
        $json["error"]="Bu siparişe ait herhangi bir ödeme bulunamadı.";
    }

}else{
    $json["error"]="Eksik veya hatalı parametre.";
}
echo json_encode($json);