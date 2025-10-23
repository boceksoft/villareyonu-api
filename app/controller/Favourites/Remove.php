<?php

SetHeader(200);
$json = [];

try {
    Favourites::RemoveItem(post("ProductId"),post('UserGuid'));
    $json['success'] = true;
}catch (Exception $e) {
    $json['error'] = $e->getMessage();
}
echo json_encode($json);
