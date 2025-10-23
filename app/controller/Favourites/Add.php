<?php

    SetHeader(200);
    $json = [];

    try {
        $AddedUserGuid = Favourites::AddItem(post("ProductId"),post('UserGuid'));
        $json['success'] = true;
        $json['UserGuid'] = $AddedUserGuid;


    }catch (Exception $e) {
        $json['error'] = $e->getMessage();
    }

    echo json_encode($json);
