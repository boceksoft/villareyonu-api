<?php
$json = [];
if($_SERVER["REQUEST_METHOD"] == "POST"){
    $token = post("token");

    $UserAgent = $_SERVER["HTTP_USER_AGENT"];
    $RemoteAddr = $_SERVER["REMOTE_ADDR"];

    $query = $db->prepare("select * from PhpUserTokens WHERE Token=:Token and Expire>=CURRENT_TIMESTAMP and UserAgent=:UserAgent");
    $query->execute([
        "Token"=>$token,
        "UserAgent"=>$UserAgent,
    ]);
    $isLogin = $query->fetch(PDO::FETCH_ASSOC);
    if ($isLogin){

        $delete = $db->prepare("delete from PhpUserTokens where Token='".$token."'");

        if ($delete->execute()){
            $json["success"]="Çıkış Yapıldı";
        }else
            $json["error"]="Oturum sonlandırılamadı";


    }else
        $json["error"]="Oturum bulunamadı";
}

echo json_encode($json);