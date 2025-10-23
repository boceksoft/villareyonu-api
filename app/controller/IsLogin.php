<?php
    $json = [
        "IsLogin"=>false
    ];

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if (get("Action")=="Reservations"){
            $json["IsLogin"] = Login::IsLogin(post("token"));
        }
    }


    echo json_encode($json);