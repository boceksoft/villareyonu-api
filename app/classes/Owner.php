<?php

class Owner
{
    public static function Index($Routing)
    {
        global $db;
        $query = $db->prepare("select id,title,baslik,left(description,250) as description,footer from sayfalar where id=:id");
        $query->execute(["id" => $Routing["EntityId"]]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
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


}