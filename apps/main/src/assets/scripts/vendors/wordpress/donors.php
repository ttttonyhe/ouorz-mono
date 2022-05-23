<?php
header("Access-Control-Allow-Origin: *");
$json = file_get_contents('donors.json');
$json = json_decode($json);
$json = json_encode($json);
echo $json;
