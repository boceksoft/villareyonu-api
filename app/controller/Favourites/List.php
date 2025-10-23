<?php

SetHeader(200);
$json = [];

echo json_encode(Favourites::ListItems(get("UserGuid")));
