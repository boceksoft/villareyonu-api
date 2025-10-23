<?php
    $query = $db->prepare("select baslik as title,'".CDN."/uploads/'+homes.resim as image from homes");
    $query->execute();
    $data = $query->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);