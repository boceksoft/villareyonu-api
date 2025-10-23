<?php
SetHeader(200);
$json = [];
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = get("token");
    $id = post("id");
    if ($token){
        $PhpUserTokens = Login::IsLogin($token);
        if ($PhpUserTokens) {
            $query = $db->prepare("insert into iyzico_response (conversationId, threeDSHtmlContent) values (:conversationId, :threeDSHtmlContent)");
            $i = $query->execute([
                "conversationId"=>post("conversationId"),
                "threeDSHtmlContent"=>post("threeDSHtmlContent")
            ]);
            if ($i){
                $json["success"]="200";
            }else{

                $query = $db->prepare("update  iyzico_response set threeDSHtmlContent=:threeDSHtmlContent where conversationId=:conversationId");
                $u = $query->execute([
                    "conversationId"=>post("conversationId"),
                    "threeDSHtmlContent"=>post("threeDSHtmlContent")
                ]);

                if ($u){
                    $json["success"]="200";
                }else {
                    $json["error"] = "Bir sorun oluştu.";
                }
            }



        }else{
            $json["error"]="Lütfen giriş yapınız.";
        }
    }else{
        $json["error"]="Geçersiz token bilgisi";
    }
}

echo json_encode($json);
