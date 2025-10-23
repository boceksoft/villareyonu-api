<?php
    SetHeader(200);
    $json = [];

    $MenuId = get("MenuId");

    $Data = Menu::GetItemsByMenuId($MenuId);
    $json["Data"] = $Data;

    echo json_encode($json);


