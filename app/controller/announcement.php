<?php
    $query = $db->prepare("insert into announcement (rate, ip) values (:rate,:ip)");
    $query->execute([
        "rate"=>post("rate"),
        "ip"=>$_SERVER['REMOTE_ADDR']
    ]);