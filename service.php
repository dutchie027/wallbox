<?php

include_once 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

$authURL = 'https://api.wall-box.com/auth/token/user';

$curl = curl_init();
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . base64_encode($_ENV[''] . ':' . $_ENV['']),
    'Accept: application/json, text/plain, */*',
    'Content-Type: application/json;charset=utf-8',
]);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_URL, $authURL);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);

$token = json_decode($result)->jwt;

$list = 'https://api.wall-box.com/v3/chargers/groups';

$curl = curl_init();
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Accept: application/json, text/plain, */*',
    'Content-Type: application/json;charset=utf-8',
]);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_URL, $list);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($curl);
curl_close($curl);
print $result;
//print json_decode($result);
