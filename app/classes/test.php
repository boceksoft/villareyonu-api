<?php

die('test');
$headers = array_change_key_case(getallheaders(), CASE_LOWER);
$language = isset($headers['language']) ? $headers['language'] : 'tr';

echo $language;