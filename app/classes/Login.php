<?php

class Login
{
    public static function IsLogin($token){

        global $db;
        $query =  $db->prepare("select * from PhpUserTokens WHERE Token=:Token and Expire>=CURRENT_TIMESTAMP and UserAgent=:UserAgent");
        $query->execute([
            'Token'=>$token,
            "UserAgent"=>$_SERVER["HTTP_USER_AGENT"]
        ]);
        return $query->fetch(PDO::FETCH_ASSOC);


    }
}