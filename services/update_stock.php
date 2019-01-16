<?php
/**
 * Executes service which updates stock amounts in DB.
 *
 * Author: Andrei Babkin <andrei@malachiteden.com>
 * Date: 16.01.2019
 * Time: 23:12
 */

session_start();

// include ERPLY API class
include("../utils/EAPI.class.php");

// Initialise class
$api = new EAPI();

// Configuration settings
$api->clientCode = 380579;
$api->username = 'andrei@malachiteden.com';
$api->password = 'x7bzWBgZqx5cLZSx';
$api->url = "https://".$api->clientCode.".erply.com/api/";

// Input parameters [{"requestName":"getProductStock","warehouseID":1,"getAmountReserved":1},{"requestName":"getProductStock","warehouseID":2}]
$requests = [
    ['requestName' => 'getProductStock', 'warehouseID' => 1],
    ['requestName' => 'getProductStock', 'warehouseID' => 2]
];
$params = [
    'requests' => json_encode($requests)
];

// Get stock amounts from ERPLY API
$result = 'Fail';
try{
    $result = $api->sendRequest(false, $params);
} catch(Exception $e) {
    echo $e->getMessage();
}

$output = json_decode($result, true);

print_r($output);
