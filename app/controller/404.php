<?php
SetHeader(404);
$json = [];


$json["error"]="Service not found";

echo json_encode($json);