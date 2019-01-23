<?php
/**
 * Executes service which updates stock amounts in DB.
 */

session_start();

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialise class
$api = new \App\Utils\EAPI();

// Configuration settings
$api->clientCode = \App\Services\Config::CLIENT_CODE;
$api->username = \App\Services\Config::USERNAME;
$api->password = \App\Services\Config::PASSWORD;
$api->url = \App\Services\Config::getApiUrl();

// Execute service
$service = new \App\Services\UpdateStockService(
    $api,
    new \App\DB\ParametersDAO(),
    new \App\DB\StockDAO()
);
try{
    $service->run();
} catch(\Exception $e) {
    // Log error
    echo $e->getMessage().PHP_EOL;
}
