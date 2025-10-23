<?php

 class Language
{
    public $lang=[];
    public function __construct()
    {
        $filename=DILUZANTI=='' ? "tr" : DILUZANTI;
        $r= require realpath(".")."/app/langs/" . $filename . ".php";
        $this->lang=$r;
    }
}