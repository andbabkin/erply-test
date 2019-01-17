<?php
/**
 * Executes service which updates stock amounts in DB.
 */

session_start();

require_once "dependencies.php";
require_once "UpdateStockService.php";

// Initialise class
$api = new \Utils\EAPI();

// Configuration settings
$api->clientCode = \Services\Config::CLIENT_CODE;
$api->username = \Services\Config::USERNAME;
$api->password = \Services\Config::PASSWORD;
$api->url = \Services\Config::getApiUrl();

// Execute service
$service = new \Services\UpdateStockService(
    $api,
    new \DB\ParametersDAO()
);
try{
    $service->run();
} catch(\Exception $e) {
    // Log error
    echo $e->getMessage();
}
