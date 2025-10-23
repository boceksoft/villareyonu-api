<?php
SetHeader(200);
$json = [];



$json["page"] = Page::GetById("11","/");
$json["data"]=Reviews::GetHomeReviews();



echo json_encode($json);