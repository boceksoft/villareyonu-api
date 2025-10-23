<?php


    //get "bocek" header key
    if ($_SERVER["REQUEST_METHOD"]=="POST"){
        if($_SERVER['HTTP_BOCEK']=="bocek*-123"){
            if(post("key")=="android"){
                $query = $db->prepare("Update genel set viiskyAndroidVersion=:viiskyAndroidVersion where id=1");
                $r = $query->execute([
                    'viiskyAndroidVersion' => post("value")
                ]);
                $json["status"]=$r;
            }elseif(post("key")=="ios"){
                $query = $db->prepare("Update genel set viiskyIosVersion=:viiskyIosVersion where id=1");
                $r = $query->execute([
                    'viiskyIosVersion' => post("value")
                ]);
                $json["status"]=$r;
            }
        }else{
            $json["status"]=false;
            $json["error"]="Invalid BOCEK header key.";
        }
    }else {
        $json["status"]=false;
        $json["error"] = "Invalid request method.";
    }

    echo json_encode($json);