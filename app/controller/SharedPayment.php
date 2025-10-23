<?php

if(route(1)){
    $f=implode("/",$route);
    require controller($f);
}else
    require controller("404");

