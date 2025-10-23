<?php

class Reservations
{
    public static function Index($Routing)
    {
        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,kisa_icerik from sayfalar".UZANTI." where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);

        $result = $query->fetch(PDO::FETCH_ASSOC);

        $result["PaymentPage"] = Page::GetById("22","/");
        $result["BankTransferPage"] = Page::GetById("21","/");
        $result["ContactPage"] = Page::GetById("14","/");

        return $result;
    }


    public static function CheckByEmail($email,$id){
        global $db;


        $query = $db->prepare("select id from kayitlar where email=:email and id=:id");
        $query->execute([
            "email"=>$email,
            "id"=>$id
        ]);

        return $query->fetch(PDO::FETCH_ASSOC);

    }

    public static function BankTransferForm($Routing)
    {
        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description from sayfalar".UZANTI." where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);

        $result = $query->fetch(PDO::FETCH_ASSOC);
        return $result;
    }


}